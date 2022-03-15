<?php

namespace App\Plugins\SystemTableColumnList;

use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Table;
use Exceedone\Exment\Services\Plugin\PluginPageBase;
use Exceedone\Exment\Model\CustomTable;
use Encore\Admin\Widgets\Form as WidgetForm;
use Exceedone\Exment\Enums\ColumnType;

class Plugin extends PluginPageBase
{
    /**
     * Display a listing of the resource.
     *
     * @return Content|\Illuminate\Http\Response
     */

    public function index()
    {
        // テーブルの一覧を取得
        $tables = CustomTable::filterList();

        // テーブルを指定していた場合、そのテーブルを絞り込み
        $table = CustomTable::getEloquent(request()->get('table'));
        
        $html = '';

        $form = new WidgetForm();
        $form->disableReset();
        $form->disableSubmit();

        $form->description('内部のシステム名も含めた、Exmentのテーブル・列の一覧を表示します。テーブルを選択してください。');

        $form->select('table', '該当テーブル')->options($tables->pluck('table_view_name', 'table_name'))
            ->setElementClass('system_table_column_list_select_table')
            ->default($table ? $table->table_name : null);

        ///// テーブルが存在する場合のみ、以下を実施
        if ($table) {
            $form->display(exmtrans('custom_table.table_name'))->default($table->table_name);
            $form->display(exmtrans('custom_table.table_view_name'))->default($table->table_view_name);
            $form->display('データベース テーブル名')->default(getDBTableName($table));
            $form->display(exmtrans('common.suuid'))->default($table->suuid);

            // 列一覧を表示
            $form->exmheader('列一覧')->hr();
        }

        // リロード用のURL
        $url = $this->plugin->getFullUrl();
        $form->hidden('system_table_column_list_root_url')->default($url);

        $html .= $form->render();
        
        if ($table) {
            $headers = [
                exmtrans('custom_column.column_name'),
                exmtrans('custom_column.column_view_name'),
                exmtrans('custom_column.column_type'),
                exmtrans('custom_column.options.index_enabled'),
                'データベース 列名',
                exmtrans('common.suuid'),
            ];
            $bodies = [];
            foreach($table->custom_columns_cache as $custom_column){
                $body = [];
                $body[] = esc_html($custom_column->column_name);
                $body[] = esc_html($custom_column->column_view_name);
                $body[] = array_get(ColumnType::transArray("custom_column.column_type_options"), $custom_column->column_type);
                $body[] = \Exment::getTrueMark($custom_column->index_enabled);
                $body[] = esc_html($custom_column->getQueryKey());
                $body[] = esc_html($custom_column->suuid);

                $bodies[] = $body;
            }

            $html .= (new Table($headers, $bodies))->render();
        }

        $box = new Box('システム用 テーブル・列一覧', $html);

        return $box;
    }
}
