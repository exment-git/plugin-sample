<?php
namespace App\Plugins\PluginValidatorTest;

use Exceedone\Exment\Services\Plugin\PluginValidatorBase;
class Plugin extends PluginValidatorBase
{
    /**
     * Plugin Validator
     */
    public function validate()
    {
        // 入力値を取得する
        $priority = array_get($this->input_value, 'priority');

        if ($priority < 100) {
            // エラーメッセージを設定する（キー：列名、値：メッセージ）
            $this->messages['priority'] = '100以上の値を重要度には入れてください！';
            // 戻り値にfalse（エラー発生）を返す
            return false;
        }
        // 戻り値にtrue（正常）を返す
        return true;
    }
}
