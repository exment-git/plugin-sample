<?php
namespace App\Plugins\OperationLogDelete;

use Exceedone\Exment\Services\Plugin\PluginBatchBase;

class Plugin extends PluginBatchBase
{
    // (1)
    protected $useCustomOption = true;

    /**
     * execute
     */
    public function execute() 
    {
        $target_day = $this->plugin->getCustomOption('rotate_day') ?? 14;
        $target_date = \Carbon\Carbon::now()->addDays(-1 * $target_day);

        // クエリ作成
        $query = \DB::table('admin_operation_log');

        // 日付で絞り込み
        $query->where('created_at', '<', $target_date);

        // 削除実施
        $deleted = $query->delete();
    }
    

    /**
     * (2) プラグインの編集画面で設定するオプション
     *
     * @param $form
     * @return void
     */
    public function setCustomOptionForm(&$form)
    {
        $form->number('rotate_day', '基準日付')
            ->min(0)
            ->default($this->plugin->getOption('rotate_day') ?? 14)
            ->help('何日前のログを削除するかを設定します。※既定は14日です。');
    }
}
