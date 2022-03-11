<?php
namespace App\Plugins\TestPluginDownload;

use Exceedone\Exment\Services\Plugin\PluginButtonBase;

class Plugin extends PluginButtonBase
{
    /**
     * Plugin Button
     */
    public function execute()
    {
        // base64文字列、Content-Type、ファイル名を配列で返却する
        $base_path = base_path('public/vendor/exment/images/user.png');
        $fileName = 'user.png';
        return [
            'fileBase64' => base64_encode(\File::get($base_path)),
            'fileContentType' => \File::mimeType($base_path),
            'fileName' => $fileName,
            
            // 任意：「ダウンロードが完了しました」メッセージを表示する
            'swaltext' => 'ダウンロードが完了しました',
        ];
    }
}
