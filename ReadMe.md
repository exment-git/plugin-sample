# Exment Plugin Samples

> Sorry, Now this page is Japanese only.

Exmentのサンプルプラグインになります。  
一部のプラグインでは、プラグインの利用方法も記載しています。

## サンプルプラグイン一覧

| 名前 | 表示名 | 種類 | パス | 概要 |
| ---- | ---- | ---- | ---- | ---- |
| HarddeleteBatch | データの物理削除 | バッチ | batch/HarddeleteBatch | 論理削除しているすべてのデータを、完全に削除します。 |
| PluginSyncBatch | 外部データベースのバッチによる同期処理 | バッチ | batch/PluginSyncBatch | 外部データベースの都市データをExmentのテーブルと一括同期します。 |
| TestPluginAddFileButton | ファイル列追加ボタン | ボタン | button/TestPluginAddFileButton | 指定のデータの列に、ファイル列が存在する場合、サンプルファイルを追加します。 |
| TestPluginDownload | ファイルダウンロードテスト | ボタン | button/TestPluginDownload | プラグインによって、ファイルをダウンロードするテストです。 |
| Docurain | Docurain | Docurain | docurain/Docurain | Docurainにより、PDFを作成するプラグインです。 |
| PluginSyncCity | 外部データ連携 | イベント | event/PluginSyncCity | 都市データの情報を外部データベースと連携します。 |
| ExportTestCsv | エクスポート(CSV) | エクスポート | export/ExportTestCsv | CSVファイルをエクスポートするプラグインです。 |
| ExportTestExcel | エクスポート(Excel) | エクスポート | export/ExportTestExcel | Excelファイルをエクスポートするプラグインです。 |
| PluginImportContract | 独自ロジックによるインポート | インポート | import/PluginImportContract | 契約データを独自ロジックでインポートします。 |
| SystemTableColumnList | システム向け テーブル・列一覧表示 | ページ | page/SystemTableColumnList | 内部パラメータも含めた、Exmentのテーブル・列の一覧を表示します。 |
| YasumiPage | 休みページ | ページ | page/YasumiPage | 表示年の祝日をページに表示します。 |
| YouTubeSearch | YouTube検索 | ページ | page/YouTubeSearch | YouTubeでデータ検索を行うプラグインです。 |
| ReplaceZenHan | 全角半角変換 | スクリプト | script/ReplaceZenHan | 全角の英数字を半角に置き換えます。 |
| SetAddress | 入力フォーム 住所セット | スクリプト | script/SetAddress | 入力フォームの郵便番号を使用し、住所をセットします。 |
| TestScript | スクリプトテスト | スクリプト | script/TestScript | 一通りのスクリプトをテストします。 |
| SetStyle | スタイルテスト | スタイル | style/SetStyle | スタイルテストです。すべての文字色を赤色にします。 |
| TableScroll | 表 横スクロールスタイル | スタイル | style/TableScroll | 表の横スクロールのスタイルを設定します。 |
| PluginValidatorTest | バリデーションテスト | バリデーション | validation/PluginValidatorTest | カスタムテーブルのバリデーションのテストです。 |
| KanbanView | かんばんビュー | ビュー | view/KanbanView | シンプルなかんばんビューを表示するプラグインです。 |


## 使用方法
- このリポジトリをcloneするか、zipでダウンロードします。  

- 使用したいプラグインのフォルダまで遷移します。  
例："HarddeleteBatch"を使用したい場合：batch/HarddeleteBatch

- そのフォルダの"dist"フォルダを開きます。  
※ReadMe.mdなど、説明用のファイルがある場合、distフォルダと同階層に配置しています。プラグイン実体は、distフォルダ内に配置しています。  

- distフォルダの中身をzip化します。

- zipファイルを、Exmentのプラグインページからインストールしてください。  
インストール方法は[こちら](https://exment.net/docs/#/ja/plugin)です。  

