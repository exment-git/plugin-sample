<?php
namespace App\Plugins\TestPluginAddFileButton;

use Exceedone\Exment\Services\Plugin\PluginButtonBase;
use Exceedone\Exment\Enums\ColumnType;
use Exceedone\Exment\Enums\FileType;
use Exceedone\Exment\Model\File as ExmentFile;

class Plugin extends PluginButtonBase
{
    /**
     * Plugin Button
     */
    public function execute()
    {
        \Log::debug('Plugin calling');

        // 対象テーブルのファイル列を取得します。
        $file_column = $this->custom_table->custom_columns->first(function($custom_column) {
            return $custom_column->column_type == ColumnType::FILE;
        });

        // プラグインフォルダを取得します。
        $dir_path = $this->plugin->getFullPath();

        if (empty($this->custom_value->getValue($file_column->column_name))) {
            // 対象データのファイル列に値が設定されていない場合はsample.txtを設定します
            $file_path = path_join($dir_path, 'files', 'sample.txt');
            $content = \File::get($file_path);

            $file = ExmentFile::storeAs(FileType::CUSTOM_VALUE_COLUMN, $content, $this->custom_table->table_name, 'sample.txt')
                ->saveCustomValue($this->custom_value->id, $file_column, $this->custom_table);
        
            $this->custom_value->setValue($file_column->column_name, $file->path);
            $this->custom_value->save();
        } else {
            // 対象データのファイル列にすでに値が設定されている場合はtest.xlsxを添付ファイルとして追加します
            $file_path = path_join($dir_path, 'files', 'test.xlsx');
            $content = \File::get($file_path);

            $file = ExmentFile::storeAs(FileType::CUSTOM_VALUE_DOCUMENT, $content, $this->custom_table->table_name, 'test.xlsx')
                ->saveCustomValue($this->custom_value->id, null, $this->custom_table)
                ->saveDocumentModel($this->custom_value, 'test.xlsx');
        }

    }

    /**
    * (v3.4.3対応)画面にボタンを表示するかどうかの判定。デフォルトはtrue
    *
    * @return bool true: 描写する false 描写しない
    */
    public function enableRender()
    {
        if (is_null($this->custom_table)) {
            return false;
        }
        // 例1：選択しているデータにファイル列が存在する場合ボタンを表示する
        return $this->custom_table->custom_columns->contains(function($custom_column) {
            return $custom_column->column_type == ColumnType::FILE;
        });
    }
}
