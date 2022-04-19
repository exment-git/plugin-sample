<?php

namespace App\Plugins\MySQLWorld;

use Encore\Admin\Widgets\Grid\Grid;
use Encore\Admin\Widgets\Form;
use Exceedone\Exment\Services\Plugin\PluginCrudBase;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class Plugin extends PluginCrudBase
{
    /**
     * content title
     *
     * @var string
     */
    protected $title = '世界の都市';

    /**
     * content description
     *
     * @var string
     */
    protected $description = '世界の都市を一覧で表示します。';

    /**
     * content icon
     *
     * @var string
     */
    protected $icon = 'fa-globe';

    /**
     * Get fields definitions
     *
     * @return array|Collection
     */
    public function getFieldDefinitions()
    {
        return [
            ['key' => 'ID', 'label' => 'ID', 'primary' => true, 'grid' => 1, 'show' => 1, 'edit' => 1],
            ['key' => 'Name', 'label' => '都市名', 'grid' => 2,'show' => 2, 'create' => 1, 'edit' => 2],
            ['key' => 'CountryCode', 'label' => '国コード', 'grid' => 3, 'show' => 3, 'create' => 2,'edit' => 3],
            ['key' => 'Population', 'label' => '人工', 'show' => 5, 'create' => 4,'edit' => 5],
        ];
    }

    /**
     * Get data paginate
     *
     * @return LengthAwarePaginator
     */
    public function getPaginate(array $options = []) : ?LengthAwarePaginator
    {
        $query = \DB::connection('world')
            ->table('city');

        // フリーワード検索がある場合
        $q = array_get($options, 'query');
        if(isset($q)){
            $query->where(function($query) use($q){
                $query
                    ->where('Name', 'LIKE', "%{$q}%")
                    ->orWhere('CountryCode', 'LIKE', "%{$q}%")
                ;
            });
        }

        return $query->paginate(array_get($options, 'per_page') ?? 20, ['*'], 'page', array_get($options, 'page'));
    }

    /**
     * read single data
     *
     * @return array|Collection
     */
    public function getData($id, array $options = [])
    {
        return \DB::connection('world')
            ->table('city')
            ->where('ID', $id)->first();
    }

    /**
     * set form info
     *
     * @return Form|null
     */
    public function setForm(Form $form, bool $isCreate, array $options = []) : ?Form
    {
        if(!$isCreate){
            $form->display('ID');    
        }
        $form->text('Name');

        // 国一覧取得
        $countries = \DB::connection('world')->table('country')->pluck('Name', 'Code');
        $form->select('CountryCode')->options($countries);
        
        $form->number('Population');

        return $form;
    }

    /**
     * post create value
     *
     * @return mixed
     */
    public function postCreate(array $posts, array $options = [])
    {
        // 独自のデータベースに保存する。
        $value = \DB::connection('world')
            ->table('city')
            ->insertGetId($posts);

        return $value;
    }

    /**
     * edit posted value
     *
     * @return mixed
     */
    public function putEdit($id, array $posts, array $options = [])
    {
        // 独自のデータベースに保存する。
        \DB::connection('world')
            ->table('city')
            ->whereOrIn('ID', $id)
            ->update($posts);

        return $id;
    }

    /**
     * delete value
     *
     * @param $id string
     * @return mixed
     */
    public function delete($id, array $options = [])
    {
        $value = \DB::connection('world')
            ->table('city')
            ->where('ID', $id)
            ->delete();
    }
}
