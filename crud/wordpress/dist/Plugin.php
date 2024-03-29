<?php

namespace App\Plugins\WordPress;

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
    protected $icon = 'fa-wordpress';

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

        if(array_has($options, 'query')){
            $query['search'] = array_get($options, 'query');
        }

        $response = $client->get('wp-json/wp/v2/posts', ['query' => $query]);

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
     * Whether edit all. If false, disable edit button and link.
     * Default: true
     *
     * @return bool
     */
    public function enableEditAll(array $options = []) : bool
    {
        return false;
    }

    /**
     * Whether delete all. If false, disable delete button and link.
     * Default: true
     *
     * @return bool
     */
    public function enableDeleteAll(array $options = []) : bool
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
     * WordpressサイトURLを取得
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
        $form->url('site', 'サイト')
            ->help('取得対象のサイトのURLを記入してください。例：Exment公式サイト  https://exment.net');
    }
}
