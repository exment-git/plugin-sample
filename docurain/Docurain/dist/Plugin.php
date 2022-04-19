<?php
namespace App\Plugins\Docurain;

use Exceedone\Exment\Enums\FileType;
use Exceedone\Exment\Enums\ColumnType;
use Exceedone\Exment\Model\System;
use Exceedone\Exment\Services\Plugin\PluginTriggerBase;
use Exceedone\Exment\Model\File as ExmentFile;

class Plugin extends PluginTriggerBase
{
    protected $useCustomOption = true;
    protected const DOCURAIN_ERROR_MESSAGE = 'エラーが発生しました';
    protected const DOCURAIN_API_URI = 'https://api.docurain.jp/api';
    protected const DOCURAIN_ERROR_CODES = [
        'AUTHENTICATION_ERROR' => '認証(認可)エラー。指定したトークンで許可されるIPアドレスや有効期限が適合するかご確認下さい。', 
        'PAYMENT_REQUIRED' => '割り当てられた無料枠を使い切りました。', 
        'NO_FREE_USAGE_REMAINED' => '割り当てられた無料枠を使い切りました。', 
        'FORBIDDEN' => '許可のない機能をリクエストしました。', 
        'EXTRA_CHARGE_OPTION_NEEDED' => '有償APIを実行する権利がありません。', 
        'NOT_FOUND' => 'URLが間違っています。', 
        'METHOD_NOT_ALLOWED' => '要求されたメソッドが間違っています。', 
        'INVALID_CONTENT_TYPE' => 'Content-Typeヘッダが要求されたものと異なるか、もしくは設定されていません。', 
        'MISSING_REQUEST_PART' => 'multipart/form-dataを要求するAPIにおいて、要求されたパートが不足しているか、名前が間違っています。', 
        'INVALID_REQUEST_BODY' => 'Request Bodyが空であるか、フォーマットが間違っているために読み込めません。', 
        'JSON_PARSE_ERROR' => 'JSONのパースエラーです。', 
        'IMAGE_UNSUPPORTED_FILE_FORMAT_ERROR' => 'サポートされていない画像形式です。', 
        'IMAGE_BROKEN_ERROR' => '画像データが壊れています。', 
        'BARCODE_ERROR' => 'バーコードの描画に失敗しました。', 
        'TEMPLATE_UNSUPPORTED_FILE_FORMAT_ERROR' => 'サポートされていないファイル形式のテンプレートです。', 
        'TEMPLATE_BROKEN_ERROR' => 'テンプレートが壊れています。', 
        'TEMPLATE_HYPERLINK_BROKEN_ERROR' => '内部的に壊れているハイパーリンクが存在します。', 
        'TEMPLATE_RUN_STYLE_ERROR' => 'テンプレート内のセルまたはシェイプに指定されているスタイルに問題があります。', 
        'TEMPLATE_PARSE_ERROR' => 'テンプレートのパースエラー。テンプレート中の制御構文に誤りがあります。', 
        'TEMPLATE_REFERENCE_ERROR' => 'JSONの値を参照する式に誤りがあります。', 
        'TEMPLATE_FORMULA_PARSE_ERROR' => 'テンプレート中の数式に誤りがあります。', 
        'TEMPLATE_FORMULA_UNSUPPORTED_FUNCTION_ERROR' => 'Docurainでサポートされていない関数を使用しています。', 
        'TEMPLATE_PRINT_AREA_REFERENCE_ERROR' => '印刷範囲の指定が不正です。', 
        'TEMPLATE_METHOD_ARGUMENT_ERROR' => 'テンプレート内のメソッド呼び出しの引数が誤っています。', 
        'TEMPLATE_TOO_LARGE_ERROR' => '処理前のテンプレートの内容が大きすぎます（例：行または列が多すぎるなど）。', 
        'RUNTIME_EVAL_ERROR' => 'テンプレートにデータを適用して評価するときにエラーが発生しました。', 
        'RUNTIME_FORMULA_TOO_LONG_STRING_LITERAL_ERROR' => '数式中の文字列リテラルが長すぎます。', 
        'RUNTIME_RESOURCE_EXHAUSTED_ERROR' => 'テンプレートの処理を行っている際に処理負荷が規定の上限値に達しました。', 
        'RUNTIME_EVAL_TIMEOUT_ERROR' => 'テンプレートの処理がタイムアウトしました。', 
        'RUNTIME_TOO_LARGE_RESULT_ERROR' => 'テンプレートを処理した結果が大きすぎます（例：foreachで大量の行を生成しているなど）。', 
        'RUNTIME_TOO_LARGE_CELL_VALUE_ERROR' => 'セルに設定する値が大きすぎます。', 
        'RUNTIME_TOO_MANY_ROWS_ERROR' => 'テンプレートを処理した結果の行数が多すぎます。', 
        'RUNTIME_TOO_MANY_CELLS_ERROR' => 'テンプレートを処理した結果のセル数が多すぎます。', 
        'RUNTIME_TOO_MANY_CELL_STYLES_ERROR' => 'テンプレートを処理した結果のセルスタイル(セルの外見のバリエーション)数が多すぎます。', 
        'RUNTIME_TOO_MANY_PRINT_AREAS_ERROR' => 'テンプレートを処理した結果の印刷範囲が多すぎます。', 
        'TOO_MANY_REQUESTS' => 'APIへのアクセス頻度が高すぎます。アクセスの間隔を開けてもう一度実行してください。', 
        'ASSERTION_ERROR' => 'アサーションに失敗しました。', 
        'INTERNAL_ERROR' => 'その他のエラー。数度試しても改善しない場合はサポートにご連絡ください。', 
    ];
    

    /**
     * Plugin Trigger
     */
    public function execute()
    {
        $token = $this->plugin->getCustomOption('docurain_token');

        if(!isset($token)){
            return [
                'result' => false,
                'swal' => static::DOCURAIN_ERROR_MESSAGE,
                'swaltext' => 'トークンがありません。プラグインの設定を行ってください。',
            ];
        }

        list($filePath, $fileName, $outFileName) = $this->getTargetFile();
        if(!isset($filePath)){
            return [
                'result' => false,
                'swal' => static::DOCURAIN_ERROR_MESSAGE,
                'swaltext' => 'ファイル設定が行われていません。プラグインの設定を行ってください。',
            ];
        }

        if(!\File::exists($filePath)){
            return [
                'result' => false,
                'swal' => static::DOCURAIN_ERROR_MESSAGE,
                'swaltext' => "ファイル「{$fileName}」がありませんでした。",
            ];
        }

        $body = fopen($filePath, 'r');

        $postValue = $this->postValue();

        $client = new \GuzzleHttp\Client();

        $response = $client->request('POST', url_join(static::DOCURAIN_API_URI, 'instant', 'pdf'), [
            'http_errors' => false,
            'headers' => [
                'Authorization' => "token $token",
            ],
            'multipart' => [
                [
                    'name' => 'entity',
                    'contents' => json_encode($postValue),
                    'headers'  => [ 'Content-Type' => 'application/json'],
                ],
                [
                    'name' => 'template',
                    'contents' => $body, 
                    'headers'  => [ 'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],  
                ]
            ]
        ]);

        if(in_array('application/pdf', (array)$response->getHeaders()['Content-Type'])){
            $pdfFile = $response->getBody()->getContents();

            $file = ExmentFile::storeAs(FileType::CUSTOM_VALUE_DOCUMENT, $pdfFile, $this->custom_table->table_name, $outFileName)
                ->saveCustomValue($this->custom_value->id, null, $this->custom_table);
            // save document model
            $document_model = $file->saveDocumentModel($this->custom_value, $outFileName);
            return true;
        }
        
        // エラー時、結果を表示
        $json = json_decode($response->getBody()->getContents(), true);
        return [
            'result' => false,
            'swal' => static::DOCURAIN_ERROR_MESSAGE,
            'swaltext' => $this->getErrorMessage($json, (string)$response->getStatusCode()),
        ];
    }
    
    public function setCustomOptionForm(&$form){
        $form->text('docurain_token', 'Docurainトークン')
            ->help('Docurainのトークンを入力してください。')
            ->required();

        $form->textarea('docurain_files', 'テーブル名と帳票ファイル名一覧')
            ->help('帳票出力を行うテーブル名とテンプレートファイル名、出力する帳票ファイル名、ボタンのラベルをカンマ区切りで入力してください。複数の場合は改行で区切ってください。<br />※テンプレートファイル名に日本語は使用できません。<br />※帳票ファイル名とボタンのラベルは省略可能です。<br />例：<br />customer,CustomerInfo.xlsx<br />contract,ContractInfo.xlsx,契約書.pdf,契約書発行')
            ;
    }

    public function getButtonLabel()
    {
        $targetTableFiles = $this->plugin->getCustomOption('docurain_files');
        if(!is_nullorempty($targetTableFiles)){
            $lines = explodeBreak($targetTableFiles);

            foreach($lines as $line){
                $l = explode(',', $line);
                if($l[0] == $this->custom_table->table_name && count($l) > 3) {
                    return $l[3];
                }
            }
        }
        return parent::getButtonLabel();
    }

    /**
     * 対象のファイルパスを取得
     *
     * @return void
     */
    protected function getTargetFile(){
        $path = $this->plugin->getFullPath();

        // 対象ファイル一覧取得
        $targetTableFiles = $this->plugin->getCustomOption('docurain_files');
        if(is_nullorempty($targetTableFiles)){
            return [null, null, null];
        }
        $lines = explodeBreak($targetTableFiles);

        foreach($lines as $line){
            $l = explode(',', $line);
            if($l[0] == $this->custom_table->table_name && count($l) > 1){
                    // 出力ファイル名取得
                    if(count($l) >= 3 && !is_nullorempty($l[2])){
                        $outFileName = replaceTextFromFormat($l[2], $this->custom_value);
                        $ext = \pathinfo($outFileName, PATHINFO_EXTENSION);
                        if (is_nullorempty($ext)) {
                            $outFileName .= '.pdf';
                        }
                    }else{
                        $outFileName = \pathinfo($l[1], PATHINFO_FILENAME) . '.pdf';
                    }
                    return [path_join($path, 'documents', $l[1]), $l[1], $outFileName];
            }
        }

        return [null, null, null];
    }

    /**
     * Docrainに渡すデータを編集する
     */
    protected function postValue(){
        $custom_table = $this->custom_value->custom_table;

        // 選択データを取得
        $result = $this->custom_value->toArray();
        
        // 選択データのURLを設定する
        $result['value_url'] = $this->custom_value->getUrl();

        // 全てのカスタム列の値を取得する（コード等は表示用の値に変換する）
        $result['value'] = $this->custom_value->getValues(true);
        
        // 親テーブルの情報を取得する
        $parent_value = $this->custom_value->getParentValue();
        if (isset($parent_value)) {
            $result['parent'] = $parent_value->getValues(true);
        }

        // カスタム列で参照している他テーブルの情報を取得する
        $select_columns = $custom_table->custom_columns->where('column_type', ColumnType::SELECT_TABLE);
        foreach($select_columns as $select_column) {
            $select_value = $this->custom_value->getValue($select_column);
            if (isset($select_value)) {
                $result['select_table'][$select_value->custom_table->table_name] = $select_value->getValues(true);
            }
        }

        // 子テーブルの情報を取得する
        foreach($custom_table->custom_relations as $custom_relation) {
            $children = $this->custom_value->getChildrenValues($custom_relation->child_custom_table_id);
            if ($children instanceof \Illuminate\Database\Eloquent\Collection) {
                foreach($children as $child) {
                    $result['children'][$child->custom_table->table_name][] = $child->getValues(true);
                }
            }
        }

        // システムパラメータを設定する
        $result['system'] = [
            'site_name' => System::site_name(), 
            'site_name_short' => System::site_name_short(),
            'system_mail_from' => System::system_mail_from(),
            'system_url' => admin_url(),
            'login_url' => admin_url('auth/login'),
        ];

        return $this->replaceBlank($result);
    }

    /**
     * nullは空文字に変換する
     * ※Docrainでテンプレート設定値がそのまま表示されるのを防ぐため
     */
    protected function replaceBlank($value) {
        if (is_array($value)) {
            return collect($value)->map(function($item) {
                return $this->replaceBlank($item);
            })->toArray();
        } else {
            return empty($value)? '': $value;
        }
    }

    /**
     * DocurainからのエラーレスポンスをMessageに変換する
     *
     * @return void
     */
    protected function getErrorMessage($json, $statusCode){
        $codes = static::DOCURAIN_ERROR_CODES;
        $type = array_get($json, 'type');

        $result = array_get($codes, $type);

        if(is_nullorempty($result)){
            return '予期せぬエラーが発生しました。';
        }

        return $result . "<br />(エラーコード ： {$type}、HTTPステータスコード ： {$statusCode})";
    }
}