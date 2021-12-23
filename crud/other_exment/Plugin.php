<?php

namespace App\Plugins\OAuthExment;

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
    protected $title = '記事一覧';

    protected $useCustomOption = true;

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
        return [
            ['key' => 'id', 'label' => 'ID', 'primary' => true, 'grid' => 1, 'show' => 1],
            ['key' => 'title', 'label' => 'タイトル', 'grid' => 2, 'show' => 2],
            ['key' => 'date', 'label' => '作成日時', 'grid' => 3, 'show' => 3],
            ['key' => 'content', 'label' => '本文', 'show' => 4],
        ];
    }

    /**
     * (3) データ一覧(Paginate)を取得
     * Get data paginate
     *
     * @return LengthAwarePaginator
     */
    public function getPaginate(array $options = []) : ?LengthAwarePaginator
    {
        $client = new Client([
            'base_uri' => $this->getSiteUrl(),
        ]);

        $query = [
            'per_page' => $options['per_page'] ?? 20,
            'page' => $options['page'] ?? 20
        ];

        $uri = 'api/data/' . $this->getEndpoint();

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

        $result = collect($json)->map(function($j){
            $j = json_decode(json_encode($j), true);
            return (object)[
                'id' => array_get($j, 'id'),
                'title' => array_get($j, 'title.rendered'),
                'content' => array_get($j, 'content.rendered'),
                'date' => array_get($j, 'date'),
            ];
        });
        
        return new LengthAwarePaginator(
            $result, 
            array_get($response->getHeaders(), 'X-WP-Total')[0], 
            $query['per_page'],
            $query['page'],
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
        $client = new Client([
            'base_uri' => $this->getSiteUrl(),
        ]);
        $response = $client->request('GET', "wp-json/wp/v2/posts/{$id}");
        $json = json_decode((string)$response->getBody());
        
        $j = json_decode(json_encode($json), true);
        return (object)[
            'id' => array_get($j, 'id'),
            'title' => array_get($j, 'title.rendered'),
            'content' => array_get($j, 'content.rendered'),
            'date' => array_get($j, 'date'),
        ];
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
     * Whether freeword search. If true, show search box in grid.
     * Default: false
     *
     * @return bool
     */
    public function enableFreewordSearch(array $options = []) : bool
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

    /**
     * Wordpressサイト設定一覧を取得
     *
     * @return array
     */
    protected function getSiteDefinitions() : Collection
    {
        $url = $this->plugin->getCustomOption('site');
        $siteStrings = explodeBreak($siteString);
        
        $configs = [];
        foreach($siteStrings as $s){
            $ses = explode(',', $s);
            if(count($ses) < 3){
                continue;
            }

            $configs[] = [
                'endpoint' => $ses[0],
                'label' => $ses[1],
                'url' => $ses[2],
            ];
        }

        return collect($configs);
    }


    // 以下、独自実装部分 --------------------------------------------------------

    /**
     * Exmentのテーブル一覧を取得
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getExmentTables() : \Illuminate\Support\Collection
    {
        $client = new Client([
            'base_uri' => $this->getSiteUrl(),
        ]);

        $query = [
            'count' => $this->getChunkCount(),
        ];

        $response = $client->get('api/table', ['query' => $query]);

        $json = json_decode((string)$response->getBody());

        return $json;
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
