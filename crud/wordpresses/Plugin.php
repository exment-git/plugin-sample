<?php

// (1)
namespace App\Plugins\WordPresses;

use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Form as WidgetForm;
use Exceedone\Exment\Services\Plugin\PluginCrudBase;
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

    // (13)
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
     * Get class name. Toggle using endpoint name.
     *
     * @param string|null $endpoint
     * @return string|null class name
     */
    public function getPluginClassName(?string $endpoint)
    {
        $sites = $this->getSiteDefinitions();
        if(!$sites->contains(function($site) use($endpoint){
            return array_get($site, 'endpoint') == $endpoint;
        })){
            return null;
        }
        
        return get_class($this);
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
        $siteString = $this->plugin->getCustomOption('sites');
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


    public function getAllEndpoints() : ?Collection
    {
        return $this->getSiteDefinitions()->pluck('endpoint');
    }

    /**
     * Whether access all CRUD page. If false, cannot access all page.
     * Default: true
     *
     * @return bool
     */
    public function enableAccessCrud(array $options = []) : bool
    {
        return !is_nullorempty($this->getAllEndpoints());
    }

    /**
     * Wordpressサイト見出しを取得
     *
     * @return string
     */
    public function getTitle() : ?string
    {
        return array_get($this->getSiteDefinitions()->first(function($config){
            return array_get($config, 'endpoint') == $this->endpoint;
        }), 'label');
    }

    /**
     * WordpressサイトURLを取得
     *
     * @return string
     */
    protected function getSiteUrl() : ?string
    {
        return array_get($this->getSiteDefinitions()->first(function($config){
            return array_get($config, 'endpoint') == $this->endpoint;
        }), 'url');
    }

    /**
     * プラグインの編集画面で設定するオプション。
     *
     * @param mixed $form
     * @return void
     */
    public function setCustomOptionForm(&$form)
    {
        $form->textarea('sites', 'サイト一覧')
            ->help('カンマ区切りで、以下のように記入してください。１つ目：エンドポイント名(英数字),2つ目：見出し,3つ目:URL。例：exment,Exment公式サイト,https://exment.net');
    }

    /**
     * Callback tools. If add event, definition.
     *
     * @param $tools
     * @return void
     */
    public function callbackGridTool($tools)
    {
        $menulist = [];

        foreach ($this->getSiteDefinitions() as $site) {
            $menulist[] = [
                'href' => admin_urls('plugins', $this->plugin->getOption('uri'), array_get($site, 'endpoint')),
                'label' => array_get($site, 'label'),
                'icon' => 'fa-wordpress',
            ];
        }
        $tools->prepend(view('exment::tools.menu-button', [
            'button_label' => 'サイト変更',
            'menulist' => $menulist,
        ])->render(), 'right');
    }

    /**
     * Callback show page tools. If add event, definition.
     *
     * @param Box $box
     * @return void
     */
    public function callbackShowTool($id, Box $box)
    {
        $box->tools(view('exment::tools.button', [
            'href' => $this->getSiteUrl() . "?p={$id}",
            'label' => '該当記事を表示',
            'icon' => 'fa-wordpress',
            'btn_class' => 'btn-primary',
            'target' => '_blank',
        ])->render());
    }
}
