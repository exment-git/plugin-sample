<?php

namespace App\Plugins\WordPressPost;

use Exceedone\Exment\Services\Plugin\PluginCrudBase;
use Encore\Admin\Widgets\Form as WidgetForm;
use Encore\Admin\Widgets\Box;
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
     * Get auth type.
     * Please set null or "key" pr "id_password" or "oauth".
     *
     * @return string|null
     */
    public function getAuthType() : ?string
    {
        return "id_password";
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
     * Get fields definitions
     *
     * @return array|Collection
     */
    public function getFieldDefinitions()
    {
        return [
            ['key' => 'id', 'label' => 'ID', 'primary' => true, 'grid' => 1, 'show' => 1],
            ['key' => 'title', 'label' => 'タイトル', 'grid' => 2, 'show' => 2, 'create' => 1, 'edit' => 1],
            ['key' => 'date', 'label' => '作成日時', 'grid' => 3, 'show' => 3],
            ['key' => 'content', 'label' => '本文', 'show' => 4, 'create' => 2, 'edit' => 2],
        ];
    }

    /**
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
     * post create value
     *
     * @return mixed
     */
    public function postCreate(array $posts, array $options = [])
    {
        $client = new Client([
            'base_uri' => $this->getSiteUrl(),
        ]);

        // create Authorization header 
        $id_password = $this->getAuthIdPassword();
        $Authorization = "Basic " . base64_encode("{$id_password['id']}:{$id_password['password']}");

        $response = $client->request('POST', "wp-json/wp/v2/posts", [
            'headers' => [
                'Authorization' => $Authorization,
                //'Content-Type' => 'application/json',
            ],
            'form_params' => [
                'title' => array_get($posts, 'title'),
                'content' => array_get($posts, 'content'),
                'status' => 'publish',
            ],
        ]);
        $json = json_decode((string)$response->getBody());
        $j = json_decode(json_encode($json), true);
        return array_get($j, 'id');
    }

    /**
     * edit posted value
     *
     * @return mixed
     */
    public function putEdit($id, array $posts, array $options = [])
    {
        $client = new Client([
            'base_uri' => $this->getSiteUrl(),
        ]);

        // create Authorization header 
        $id_password = $this->getAuthIdPassword();
        $Authorization = "Basic " . base64_encode("{$id_password['id']}:{$id_password['password']}");

        $response = $client->request('POST', "wp-json/wp/v2/posts/{$id}", [
            'headers' => [
                'Authorization' => $Authorization,
                //'Content-Type' => 'application/json',
            ],
            'form_params' => [
                'title' => array_get($posts, 'title'),
                'content' => array_get($posts, 'content'),
                'status' => 'publish',
            ],
        ]);
        $json = json_decode((string)$response->getBody());
        $j = json_decode(json_encode($json), true);
        return array_get($j, 'id');
    }

    /**
     * delete value
     *
     * @param $id string
     * @return mixed
     */
    public function delete($id, array $options = [])
    {
        $client = new Client([
            'base_uri' => $this->getSiteUrl(),
        ]);

        // create Authorization header 
        $id_password = $this->getAuthIdPassword();
        $Authorization = "Basic " . base64_encode("{$id_password['id']}:{$id_password['password']}");

        $response = $client->request('DELETE', "wp-json/wp/v2/posts/{$id}", [
            'headers' => [
                'Authorization' => $Authorization,
                //'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Whether create data. If false, disable create button.
     * Default: true
     *
     * @return bool
     */
    public function enableCreate(array $options = []) : bool
    {
        return true;
    }

    /**
     * Whether edit target data. If false, disable edit button and link.
     * Default: true
     *
     * @return bool
     */
    public function enableEdit($value, array $options = []) : bool
    {
        return true;
    }

    /**
     * Whether delete target data. If false, disable delete button and link.
     * Default: true
     *
     * @return bool
     */
    public function enableDelete($value, array $options = []) : bool
    {
        return true;
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
     * Set column difinition for create. If add event, definition.
     *
     * @param WidgetForm $form
     * @return void
     */
    public function setCreateColumnDifinition(WidgetForm $form, string $key, string $label)
    {
        if($key == 'content'){
            $form->tinymce('content', '本文');
            return;
        }
        parent::setCreateColumnDifinition($form, $key, $label);
    }

    /**
     * Set column difinition for edit. If add event, definition.
     *
     * @param WidgetForm $form
     * @return void
     */
    public function setEditColumnDifinition(WidgetForm $form, string $key, string $label)
    {
        if($key == 'content'){
            $form->tinymce('content', '本文');
            return;
        }
        parent::setCreateColumnDifinition($form, $key, $label);
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
