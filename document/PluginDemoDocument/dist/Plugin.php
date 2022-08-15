<?php
namespace App\Plugins\PluginDemoDocument; // (PluginDemoDocument:プラグイン名)

use Exceedone\Exment\Model\Define;
use Exceedone\Exment\Model\System;
use Exceedone\Exment\Services\Plugin\PluginDocumentBase;

class Plugin extends PluginDocumentBase
{
    /**
     * ドキュメントの変数置き換え実行前に呼び出される関数
     *
     * @param SpreadSheet $spreadsheet
     * @return void
     */
    protected function called($spreadsheet)
    {
        // ドキュメントの変数置き換え実行前に独自処理を実行したい場合はこちら
    }

    /**
     * ドキュメントの変数置き換え実行後に呼び出される関数
     *
     * @param SpreadSheet $spreadsheet
     * @return void
     */
    protected function saving($spreadsheet)
    {
        // １枚目のシートを選択
        $sheet = $spreadsheet->getSheet(0);
        // B1セルにサイト名を設定
        $sheet->setCellValue('B1', System::site_name());

        // Exmentの新着情報を取得する
        $items = $this->getItems();

        foreach ($items as $idx => $item) {
            // 4行目～出力
            $row = $idx + 4;
            // 日付をA?セルに出力
            $date = \Carbon\Carbon::parse(array_get($item, 'date'))->format(config('admin.date_format'));
            $sheet->setCellValue("A$row", $date);
            // リンクをB?セルに出力
            $link = array_get($item, 'link');
            $sheet->setCellValue("B$row", $link);
            // タイトルをC?セルに出力
            $title = array_get($item, 'title.rendered');
            $sheet->setCellValue("C$row", $title);
        }
    }

    /**
     * Exmentの新着情報をAPIから取得する
     *
     * @return array exment news items array
     */
    protected function getItems()
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', Define::EXMENT_NEWS_API_URL, [
            'http_errors' => false,
            'query' => $this->getQuery(),
            'timeout' => 3, // Response timeout
            'connect_timeout' => 3, // Connection timeout
        ]);

        $contents = $response->getBody()->getContents();
        if ($response->getStatusCode() != 200) {
            return [];
        }
        return json_decode_ex($contents, true);
    }

    /**
     * APIに渡すクエリ条件
     *
     * @return array query string array
     */
    protected function getQuery()
    {
        $request = request();

        // get querystring
        $query = [
            'categories' => 6,
            'per_page' => System::datalist_pager_count() ?? 5,
            'page' => $request->get('page') ?? 1,
        ];

        return $query;
    }

    /**
    * (v3.4.3対応)画面にボタンを表示するかどうかの判定。デフォルトはtrue
    * 
    * @return bool true: 描写する false 描写しない
    */
    public function enableRender(){
        // カスタム列の値「view_flg」が「1」の場合にボタンを表示する
        return $this->custom_value->getValue('view_flg')  == 1;
    }
}