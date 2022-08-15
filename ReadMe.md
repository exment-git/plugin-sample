# Exment Plugin Samples

> Sorry, Now this page is Japanese only.

Exmentのサンプルプラグインになります。  
一部のプラグインでは、プラグインの利用方法も記載しています。

## サンプルプラグイン一覧

| 名前 | 表示名 | 種類 | パス | 概要 |
| ---- | ---- | ---- | ---- | ---- |
| PluginDemoAPI | カスタム列情報取得API | API | batch/PluginDemoAPI | 列名からカスタム列情報を取得するサンプルAPIです。 |
| HarddeleteBatch | データの物理削除 | バッチ | batch/HarddeleteBatch | 論理削除しているすべてのデータを、完全に削除します。 |
| PluginSyncBatch | 外部データベースのバッチによる同期処理 | バッチ | batch/PluginSyncBatch | 外部データベースの都市データをExmentのテーブルと一括同期します。 |
| OperationLogDelete | 操作ログローテーション | バッチ | batch/OperationLogDelete | Exmentの操作ログを、一定日付経過したものをデータベースから物理削除します。 |
| TestPluginAddFileButton | ファイル列追加ボタン | ボタン | button/TestPluginAddFileButton | 指定のデータの列に、ファイル列が存在する場合、サンプルファイルを追加します。 |
| TestPluginDownload | ファイルダウンロードテスト | ボタン | button/TestPluginDownload | プラグインによって、ファイルをダウンロードするテストです。 |
| PluginCustomButton | 「次へ」「前へ」ボタンを表示 | ボタン | button/PluginCustomButton | データ詳細画面に「次へ」「前へ」ボタンを表示します。 |
| Docurain | Docurain | Docurain | docurain/Docurain | Docurainにより、PDFを作成するプラグインです。 |
| document_demo_user | ユーザー情報出力 | ドキュメント | docurain/document_demo_user | ユーザー情報出力のテスト用です。 |
| PluginDemoDocument | Exment新着情報出力 | ドキュメント | docurain/PluginDemoDocument | APIから取得したExment新着情報を出力します。 |
| PluginSyncCity | 外部データ連携 | イベント | event/PluginSyncCity | 都市データの情報を外部データベースと連携します。 |
| ExportTestCsv | エクスポート(CSV) | エクスポート | export/ExportTestCsv | CSVファイルをエクスポートするプラグインです。 |
| ExportTestExcel | エクスポート(Excel) | エクスポート | export/ExportTestExcel | Excelファイルをエクスポートするプラグインです。 |
| PluginImportContract | 独自ロジックによるインポート | インポート | import/PluginImportContract | 契約データを独自ロジックでインポートします。 |
| SystemTableColumnList | システム向け テーブル・列一覧表示 | ページ | page/SystemTableColumnList | 内部パラメータも含めた、Exmentのテーブル・列の一覧を表示します。 |
| YasumiPage | 休みページ | ページ | page/YasumiPage | 表示年の祝日をページに表示します。 |
| YouTubeSearch | YouTube検索 | ページ | page/YouTubeSearch | YouTubeでデータ検索を行うプラグインです。 |
| ReplaceZenHan | 全角半角変換 | スクリプト | script/ReplaceZenHan | 全角の英数字を半角に置き換えます。 |
| ReplaceKanaHanZen | 半角カナ→全角カナ変換 | スクリプト | script/ReplaceKanaHanZen | 半角カナを全角カナに置き換えます。 |
| SetAddress | 入力フォーム 住所セット | スクリプト | script/SetAddress | 入力フォームの郵便番号を使用し、住所をセットします。 |
| TestScript | スクリプトテスト | スクリプト | script/TestScript | 一通りのスクリプトをテストします。 |
| ChangeDynamicForm | 入力フォーム 動的切り替え | スクリプト | script/ChangeDynamicForm | 入力フォームの選択値に応じて、項目の表示非表示を切り替えます。 |
| SetStyle | スタイルテスト | スタイル | style/SetStyle | スタイルテストです。すべての文字色を赤色にします。 |
| TableScroll | 表 横スクロールスタイル | スタイル | style/TableScroll | 表の横スクロールのスタイルを設定します。 |
| PluginValidatorTest | バリデーションテスト | バリデーション | validation/PluginValidatorTest | カスタムテーブルのバリデーションのテストです。 |
| KanbanView | かんばんビュー | ビュー | view/KanbanView | シンプルなかんばんビューを表示するプラグインです。 |
| MySQLWorld | 他のデータベース連携 | CRUDページ | crud/mysqlworld | Exmentとは異なるデータベースと接続し、データの取得・追加・編集・削除を実施します。 |
| ShowLevels3Data | Exmentデータ独自取得 | CRUDページ | crud/show_levels3_data | 独自のSQLを用いて、3階層の項目を同時に一覧・参照できるようにするサンプルです。 |
| WordPress | REST API連携 | CRUDページ | crud/wordpress | REST APIを使用し、指定のWordpressサイトの投稿を一覧表示・詳細表示します。 |
| WordPresses | REST API連携(複数エンドポイント) | CRUDページ | crud/wordpresses | REST APIを使用し、複数のWordpressサイトの投稿を一覧表示・詳細表示します。複数のエンドポイントに対応し、画面からボタンで対象サイトを切り替えます。 |
| WordPressPost | REST API連携(認証、POST) | CRUDページ | crud/wordpress_post | REST APIを使用し、指定のWordpressサイトの投稿を一覧表示・詳細表示します。また、事前設定したアクセスキーを使用し、投稿の追加・編集・削除も実施します。 |
| OtherExment | REST API連携(OAuth認証) | CRUDページ | crud/OtherExment | 別サーバーのExmentとRest API連携し、データ取得を実施します。 |


## 使用方法
- このリポジトリをcloneするか、zipでダウンロードします。  

- 使用したいプラグインのフォルダまで遷移します。  
例："HarddeleteBatch"を使用したい場合：batch/HarddeleteBatch

- そのフォルダの"dist"フォルダを開きます。  
※ReadMe.mdなど、説明用のファイルがある場合、distフォルダと同階層に配置しています。プラグイン実体は、distフォルダ内に配置しています。  

- distフォルダの中身をzip化します。

- zipファイルを、Exmentのプラグインページからインストールしてください。  
インストール方法は[こちら](https://exment.net/docs/#/ja/plugin)です。  

