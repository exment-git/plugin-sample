<?php

namespace App\Plugins\OtherExment;

use Exceedone\Exment\Services\Plugin\PluginCrudBase;
use Encore\Admin\Widgets\Form as WidgetForm;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use GuzzleHttp\Client;

class Plugin extends PluginCrudBase
{
    /**
     * content icon
     *
     * @var string
     */
    protected $icon = 'fa-database';

    /**
     * content title
     *
     * @var string
     */
    protected $title = 'Exment 他のExmentよりデータ取得';

    protected $useCustomOption = true;

    /**
     * 一度列定義を取得していた場合はここにセットしておく
     *
     * @var \Illuminate\Support\Collection
     */
    protected $fieldDefinitions;

    /**
     * Get auth type.
     * Please set null or "key" pr "id_password" or "oauth".
     *
     * @return string|null
     */
    public function getAuthType() : ?string
    {
        return 'oauth';
    }

    /**
     * Get max chunk count.
     *
     * @return int
     */
    public function getChunkCount() : int
    {
        return 100;
    }

    /**
     * (2) 列定義を取得
     * Get fields definitions
     *
     * @return array|Collection
     */
    public function getFieldDefinitions()
    {
        if(isset($this->fieldDefinitions)){
            return $this->fieldDefinitions;
        }

        // ExmentのAPIから列定義取得
        $client = new Client();
        $endpoint = $this->getEndpoint();
        if(is_nullorempty($endpoint)){
            $endpoint = $this->getExmentTables()->first();
        }
        $uri = url_join($this->getSiteUrl(), 'api/table/' . $endpoint . '/columns');

        $authorization = "Bearer " . $this->getOauthAccessToken();
        $response = $client->get($uri, [
            'headers' => [
                'Authorization' => $authorization,
            ],
            'query' => ['count' => 100],
        ]);

        $json = json_decode((string)$response->getBody());
        $json = json_decode(json_encode($json), true);
        
        // IDの列を追加
        $result = collect();
        $result->push([
            'key' => 'id',
            'label' => 'ID',
            'primary' => true,
            'grid' => 1,
            'show' => 1,
        ]);
        collect($json)->each(function($j) use(&$result){
            $result->push([
                'key' => array_get($j, 'column_name'),
                'label' => array_get($j, 'column_view_name'),
                'grid' => 1,
                'show' => 1,
                'order' => array_get($j, 'order', 9999),
            ]);
        });

        $this->fieldDefinitions = $result;
        return $result;
    }

    /**
     * (3) データ一覧(Paginate)を取得
     * Get data paginate
     *
     * @return LengthAwarePaginator
     */
    public function getPaginate(array $options = []) : ?LengthAwarePaginator
    {
        $client = new Client();

        $query = [
            'per_page' => $options['per_page'] ?? 20,
            'page' => $options['page'] ?? 20,
            'valuetype' => 'text',
        ];

        $endpoint = $this->getEndpoint();
        if(is_nullorempty($endpoint)){
            $endpoint = $this->getExmentTables()->first();
        }
        $uri = url_join($this->getSiteUrl(), 'api/data/' . $endpoint);

        if(array_has($options, 'query')){
            $query['q'] = array_get($options, 'query');
            $uri .= '/query';
        }

        $authorization = "Bearer " . $this->getOauthAccessToken();
        $response = $client->get($uri, [
            'headers' => [
                'Authorization' => $authorization,
            ],
            'query' => $query,
        ]);

        $json = json_decode((string)$response->getBody());
        $json = json_decode(json_encode($json), true);

        $result = collect(array_get($json, 'data'))->map(function($j){
            $result = [];
            foreach($this->getFieldDefinitions() as $definition){
                // IDの場合はそのままセット
                $key = array_get($definition, 'key');
                if($key == 'id'){
                    $result[$key] = array_get($j, 'id');
                }
                // 値の場合はvalue.{キー名}からセット
                else{
                    $result[$key] = array_get($j, "value.{$key}");
                }
            }
            return (object)$result;
        });
        
        return new LengthAwarePaginator(
            $result, 
            array_get($json, 'total'), 
            array_get($json, 'per_page'), 
            array_get($json, 'current_page'), 
            [
                'path' => $this->getFullUrl(),
            ]
        );
    }

    /**
     * (3) データ詳細(1件のデータ)を取得
     * read single data
     *
     * @return array|Collection
     */
    public function getData($id, array $options = [])
    {
        $client = new Client();

        $query = [
            'valuetype' => 'text',
        ];

        $endpoint = $this->getEndpoint();
        if (is_nullorempty($endpoint)) {
            $endpoint = $this->getExmentTables()->first();
        }
        $uri = url_join($this->getSiteUrl(), 'api/data/' . $endpoint . '/' . $id);

        $authorization = "Bearer " . $this->getOauthAccessToken();
        $response = $client->get($uri, [
            'headers' => [
                'Authorization' => $authorization,
            ],
            'query' => $query,
        ]);

        $json = json_decode((string)$response->getBody());
        $json = json_decode(json_encode($json), true);

        $result = [];
        foreach ($this->getFieldDefinitions() as $definition) {
            // IDの場合はそのままセット
            $key = array_get($definition, 'key');
            if ($key == 'id') {
                $result[$key] = array_get($json, 'id');
            }
            // 値の場合はvalue.{キー名}からセット
            else {
                $result[$key] = array_get($json, "value.{$key}");
            }
        }
        return (object)$result;
    }

    /**
     * (8) (任意：)新規作成を実行可能とするかどうか
     * Whether create data. If false, disable create button.
     * Default: true
     *
     * @return bool
     */
    public function enableCreate(array $options = []) : bool
    {
        return false;
    }

    /**
     * Whether edit target data. If false, disable edit button and link.
     * Default: true
     *
     * @return bool
     */
    public function enableEdit($value, array $options = []) : bool
    {
        return false;
    }

    /**
     * Whether delete target data. If false, disable delete button and link.
     * Default: true
     *
     * @return bool
     */
    public function enableDelete($value, array $options = []) : bool
    {
        return false;
    }
    /**
     * Whether export data. If false, disable export button and link.
     * Default: false
     *
     * @return bool
     */
    public function enableExport(array $options = []) : bool
    {
        return true;
    }

    /**
     * Whether access all CRUD page. If false, cannot access all page.
     * Default: true
     *
     * @return bool
     */
    public function enableAccessCrud(array $options = []) : bool
    {
        return !is_nullorempty($this->getSiteUrl());
    }

    /**
     * Set column difinition for show. If add event, definition.
     *
     * @param WidgetForm $form
     * @return void
     */
    public function setShowColumnDifinition(WidgetForm $form, string $key, string $label)
    {
        if($key == 'content'){
            $form->display($key, $label)
                ->escape(false);
            return;
        }
        parent::setShowColumnDifinition($form, $key, $label);
    }

    // 以下、独自実装部分 --------------------------------------------------------

    /**
     * Exmentのテーブル一覧を取得
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getExmentTables() : \Illuminate\Support\Collection
    {
        return collect(explodeBreak($this->plugin->getCustomOption('tables')));
    }

    /**
     * Get the value of endpoint
     */ 
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * ExmentサイトURLを取得
     *
     * @return string
     */
    protected function getSiteUrl() : ?string
    {
        return $this->plugin->getCustomOption('site');
    }

    /**
     * Get target all endpoints
     * If support multiple endpoints, override function, end return. 
     *
     * @return array|null
     */
    public function getAllEndpoints() : ?Collection
    {
        $tables = $this->getExmentTables();
        if(\is_nullorempty($tables) || count($tables) <= 1){
            return null;
        }
        return $tables;
    }

    /**
     * プラグインの編集画面で設定するオプション。
     *
     * @param mixed $form
     * @return void
     */
    public function setCustomOptionForm(&$form)
    {
        $form->url('site', 'Exmentサイト')
            ->help('取得対象のExmentのURLを記入してください。')
            ->required();

        $form->textarea('tables', '取得対象テーブル一覧')
            ->help('データを取得する対象のテーブル名を、英数字で記入してください。複数取得する場合は、改行区切りで記入してください。※初期表示は、1行目のテーブルのデータを取得します。')
            ->required();
    }
}
