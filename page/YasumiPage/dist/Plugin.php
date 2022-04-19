<?php

namespace App\Plugins\YasumiPage;

use Exceedone\Exment\Services\Plugin\PluginPageBase;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Table;

class Plugin extends PluginPageBase
{
    /**
     * Index
     *
     * @return void
     */
    public function index()
    {
        return $this->getIndexBox();
    }

    public function list()
    {
        $result = [];
        $weeks = ["日", "月", "火", "水", "木", "金", "土"];

        $years = range(request()->get('year_from'), request()->get('year_to'));

        foreach($years as $year){
            $holidays = \Yasumi\Yasumi::create('Japan', $year, 'ja_JP');
            foreach($holidays->getHolidays() as $holiday){
                $result[] = [
                    $holiday->format('Y/m/d') . '(' . $weeks[$holiday->format('w')] . ')',
                    $holiday->getName(),
                ];
            }
        }
        
        $table = new Table([
            '日付',
            '祝日名',
        ], $result);

        $html = $this->getIndexBox()->render();
        $html .= (new Box("祝日検索結果", $table))->render();

        return $html;
    }

    
    protected function getIndexBox(){
        // Yasumiのチェック
        $hasLibrary = class_exists(\Yasumi\Yasumi::class);
        $currentYear = \Carbon\Carbon::now()->year;

        return new Box("祝日一覧取得", view('exment_yasumi_page::index', [
            'action' => $this->plugin->getRouteUri('list'),
            'hasLibrary' => $hasLibrary,
            'years' => range($currentYear - 10, $currentYear + 2),
            'selectYearFrom' => request()->get('year_from', $currentYear - 1),
            'selectYearTo' => request()->get('year_to', $currentYear + 1),
        ]));
    }
    
}