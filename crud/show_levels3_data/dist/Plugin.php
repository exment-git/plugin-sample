<?php

namespace App\Plugins\ShowLevels3Data;

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

        // 参照するカスタム列の情報を取得
        $columns = $this->getTargetCustomColumns();
        collect($columns)->each(function($column, $idx) use(&$result){
            $custom_column = CustomColumn::getEloquent($column['column'], $column['table']);

            $result->push([
                'key' => array_get($custom_column, 'column_name'),
                'label' => array_get($custom_column, 'column_view_name'),
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
        $query = $this->getBasicQuery();
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
        $query = $this->getBasicQuery();
        return $query->where('this_table.id', $id)->first();
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


    protected function getBasicQuery()
    {
        // テーブル・列情報
        $info = $this->getJoinTableColumns();

        // 自テーブル
        $thisTable = $info[0];
        // 親テーブル
        $parentTable = $info[1];
        // 祖母テーブル
        $grandParentTable = $info[2];

        // DBテーブル名
        $thisTableDB = getDBTableName($thisTable['table']);
        $parentTableDB = getDBTableName($parentTable['table']);
        $grandParentTableDB = getDBTableName($grandParentTable['table']);

        // 連結フィールドのDB列名を取得します
        $key1 = $thisTable['foreign_column'] == 'parent_id' ? 'parent_id' : CustomColumn::getEloquent($thisTable['foreign_column'], $thisTable['table'])->getQueryKey();
        $key2 = $parentTable['foreign_column'] == 'parent_id' ? 'parent_id' :  CustomColumn::getEloquent($parentTable['foreign_column'], $parentTable['table'])->getQueryKey();

        // 参照するフィールドのDB列名を取得します
        $selects = collect(["this_table.id"]);
        $selects = $selects->merge(collect($this->getTargetCustomColumns())
            ->map(function($tableColumn) use($thisTable, $parentTable, $grandParentTable){
                // キー名を判定
                $asName = '';
                $column = CustomColumn::getEloquent($tableColumn['column'], $tableColumn['table'])->getQueryKey();

                if($thisTable['table'] == $tableColumn['table']){
                    $asName = 'this_table';
                }
                elseif($parentTable['table'] == $tableColumn['table']){
                    $asName = 'parent_table';
                }
                elseif($grandParentTable['table'] == $tableColumn['table']){
                    $asName = 'grand_parent_table';
                }

                //  クエリビルダのselectに該当する文字列を返却
                return "$asName.{$column} as {$tableColumn['column']}";
            }))->toArray();

        $query = \DB::table("$thisTableDB as this_table")
            ->join("$parentTableDB as parent_table", "this_table.$key1", '=', 'parent_table.id')
            ->join("$grandParentTableDB as grand_parent_table", "parent_table.$key2", '=', 'grand_parent_table.id')
            ->select($selects);

        return $query;
    }

    // 以下、独自実装部分 --------------------------------------------------------

    /**
     * 取得対象のテーブル・列一覧を返却
     *
     * @return array
     */
    protected function getTargetCustomColumns() : array
    {
        // 'table': テーブル名
        // 'column': 列名
        return [
            ['table' => 'product', 'column' => 'price'],
            ['table' => 'order', 'column' => 'number'],
            ['table' => 'order', 'column' => 'amount'],
            ['table' => 'purchasing', 'column' => 'email'],
        ];
    }
    
    /**
     * テーブル結合の情報を取得
     *
     * @return array
     */
    protected function getJoinTableColumns() : array
    {
        return [
            // 第一引数：自テーブルのテーブル名、外部キーに該当する列名
            // ※リレーション設定で、1:nリレーションの場合は、foreign_columnを"parent_id"と記入してください
            ['table' => 'order', 'foreign_column' => 'product'],
            // 第二引数：1つ上のテーブルのテーブル名、外部キーに該当する列名
            // ※リレーション設定で、1:nリレーションの場合は、foreign_columnを"parent_id"と記入してください
            ['table' => 'product', 'foreign_column' => 'purchasing'],
            // 第三引数：2つ上のテーブルのテーブル名
            ['table' => 'purchasing'],
        ];
    }
}
