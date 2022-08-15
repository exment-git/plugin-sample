<?php

namespace App\Plugins\BatchButton;

use Exceedone\Exment\Services\Plugin\PluginSettingBase;

class PluginSetting extends PluginSettingBase
{
    protected $useCustomOption = true;

    /**
     * @param [type] $form
     * @return void
     */
    public function setCustomOptionForm(&$form)
    {
        $form->text('text', 'ログ出力文字列')
            ->help('ログに出力する文字列を設定します。')
            ->default('Executed!');
    }
}