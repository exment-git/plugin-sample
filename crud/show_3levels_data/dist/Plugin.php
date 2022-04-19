<?php

namespace App\Plugins\PluginCrudSample;

use Exceedone\Exment\Services\Plugin\PluginCrudBase;
use Encore\Admin\Widgets\Form as WidgetForm;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Exceedone\Exment\Model\CustomColumn;

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
    protected $title = '独自のSQLでExmentデータ表示';

    protected $useCustomOption = true;

    /**
     * 一度列定義を取得していた場合はここにセットしておく
     *
     * @var \Illuminate\Support\Collection
     */
    protected $fieldDefinitions;

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
        
        // IDの列を追加
        $result = collect();
        $result->push([
            'key' => 'id',
            'label' => 'ID',
            'primary' => true,
            'grid' => 1,
            'show' => 1,
        ]);

        // 参照するカスタム列の情報を取得します
        $columns[] = CustomColumn::getEloquent('price', 'product');
        $columns[] = CustomColumn::getEloquent('number', 'order');
        $columns[] = CustomColumn::getEloquent('amount', 'order');
        $columns[] = CustomColumn::getEloquent('email', 'purchasing');
        
        collect($columns)->each(function($j, $idx) use(&$result){
            $result->push([
                'key' => array_get($j, 'column_name'),
                'label' => array_get($j, 'column_view_name'),
                'grid' => 1,
                'show' => 1,
                'order' => $idx?? 9999,
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
        // 参照するカスタムテーブルのDBテーブル名を取得します
        $order_table = getDBTableName('order');
        $product_table = getDBTableName('product');
        $purchasing_table = getDBTableName('purchasing');

        // 連結フィールドのDB列名を取得します
        $key1 = CustomColumn::getEloquent('product', 'order')->getQueryKey();
        $key2 = CustomColumn::getEloquent('purchasing', 'product')->getQueryKey();

        // 参照するフィールドのDB列名を取得します
        $column1 = CustomColumn::getEloquent('number', 'order')->getQueryKey();
        $column2 = CustomColumn::getEloquent('price', 'product')->getQueryKey();
        $column3 = CustomColumn::getEloquent('email', 'purchasing')->getQueryKey();
        $column4 = CustomColumn::getEloquent('amount', 'order')->getQueryKey();

        $query = \DB::table("$order_table as order")
            ->join("$product_table as product", "order.$key1", '=', 'product.id')
            ->join("$purchasing_table as purchasing", "product.$key2", '=', 'purchasing.id')
            ->select(
                'order.id',
                "order.$column1 as number",
                "product.$column2 as price",
                "purchasing.$column3 as email",
                "order.$column4 as amount");

        return $query->paginate(
            array_get($options, 'per_page') ?? 20, ['*'], 'page', array_get($options, 'page'));
    }

    /**
     * (3) データ詳細(1件のデータ)を取得
     * read single data
     *
     * @return array|Collection
     */
    public function getData($id, array $options = [])
    {
        // 参照するカスタムテーブルのDBテーブル名を取得します
        $order_table = getDBTableName('order');
        $product_table = getDBTableName('product');
        $purchasing_table = getDBTableName('purchasing');

        // 連結フィールドのDB列名を取得します
        $key1 = CustomColumn::getEloquent('product', 'order')->getQueryKey();
        $key2 = CustomColumn::getEloquent('purchasing', 'product')->getQueryKey();

        // 参照するフィールドのDB列名を取得します
        $column1 = CustomColumn::getEloquent('number', 'order')->getQueryKey();
        $column2 = CustomColumn::getEloquent('price', 'product')->getQueryKey();
        $column3 = CustomColumn::getEloquent('email', 'purchasing')->getQueryKey();
        $column4 = CustomColumn::getEloquent('amount', 'order')->getQueryKey();

        return \DB::table("$order_table as order")
            ->join("$product_table as product", "order.$key1", '=', 'product.id')
            ->join("$purchasing_table as purchasing", "product.$key2", '=', 'purchasing.id')
            ->select(
                'order.id',
                "order.$column1 as number",
                "product.$column2 as price",
                "purchasing.$column3 as email",
                "order.$column4 as amount")
            ->where('order.id', $id)->first();
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
}
