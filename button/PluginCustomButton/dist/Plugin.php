<?php
namespace App\Plugins\PluginCustomButton;

use Exceedone\Exment\Services\Plugin\PluginButtonBase;
class Plugin extends PluginButtonBase
{
    /**
     * Plugin Trigger
     */
    public function execute()
    {
    }
    
    /**
     * デフォルトのボタン表示に代わり、独自のボタンを追加する処理
     *
     * @return void
     */
    public function render(){
        $buttons = [];

        // keys. true: 「次」、false：「前」
        $keys = [true, false];
        foreach($keys as $key){
            // 前後の値を取得
            $value = $this->getNextOrPrevId($key);
            
            // 値が存在すれば、ボタンを追加する
            if(!is_null($value)){
                $buttons[] = [
                    'href' => $value->getUrl(),
                    'target' => '_top',
                    ($key ? 'icon_right' : 'icon') => $key ? 'fa-arrow-right' : 'fa-arrow-left',
                    'label' => $value->getLabel(),
                ];
            }
        }

        // button.blade.phpでボタンを描写する
        return $this->pluginView('buttons', ['buttons' => $buttons]);
    }

    /**
     * 次もしくは前のデータを取得する
     *
     * @param boolean $isNext true: 次のデータ、false: 前のデータ
     * @return CustomValue
     */
    protected function getNextOrPrevId(bool $isNext){
        $query = $this->custom_table->getValueModel()->query();   
        $query->where('id', ($isNext ? '>' : '<'), $this->custom_value->id);

        $query->orderBy('id', ($isNext ? 'asc' : 'desc'));

        return $query->first();
    }
}
