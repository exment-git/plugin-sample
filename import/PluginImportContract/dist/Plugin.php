<?php
namespace App\Plugins\PluginImportContract;

use Exceedone\Exment\Services\Plugin\PluginImportBase;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Plugin extends PluginImportBase{
    /**
     * execute
     */
    public function execute() {
        $path = $this->file->getRealPath();

        $reader = $this->createReader();
        $spreadsheet = $reader->load($path);

        // Sheet1のB4セルの内容で顧客マスタを読み込みます
        $sheet = $spreadsheet->getSheetByName('Sheet1');
        $client_name = getCellValue('B4', $sheet, true);
        $client = getModelName('client')::where('value->client_name', $client_name)->first();

        // Sheet1のヘッダ部分に記載された情報で契約データを編集します
        // statusには固定値:1を設定します
        $contract = [
            'value->contract_code' => getCellValue('B3', $sheet, true),
            'value->client' => $client->id,
            'value->status' => '1',
            'value->contract_date' => getCellValue('D4', $sheet, true),
        ];
        // 契約テーブルにレコードを追加します
        $record = getModelName('contract')::create($contract);

        // Sheet1の7行目～15行目に記載された明細情報を元に契約明細データを出力します
        for ($i = 7; $i <= 15; $i++) {
            // A列から製品バージョンコードを取得します
            $product_version_code = getCellValue("A$i", $sheet, true);
            // 製品バージョンコードが設定されていない時は次の行にスキップします
            if (!isset($product_version_code)) break;
            // 製品バージョンコードで、製品バージョンテーブルを読み込みます
            $product_version = getModelName('product_version')
                ::where('value->product_version_code', $product_version_code)->first();
            // 製品バージョンテーブルが取得できなかった場合は次の行にスキップします
            if (!isset($product_version)) continue;
            // 明細行と製品バージョンテーブルから契約明細データを編集します
            $contract_detail = [
                'parent_id' => $record->id,
                'parent_type' => 'contract',
                'value->product_version_id' => $product_version->id,
                'value->fixed_price' => getCellValue("B$i", $sheet, true),
                'value->num' => getCellValue("C$i", $sheet, true),
                'value->zeinuki_price' => getCellValue("D$i", $sheet, true),
            ];
            // 契約明細テーブルにレコードを追加します
            getModelName('contract_detail')::create($contract_detail);
        }

        return true;
    }
    
    protected function createReader()
    {
        return IOFactory::createReader('Xlsx');
    }
    
}
