# プラグイン(バッチ) サンプル - 他のMySQL連携
Exmentとは異なるデータベースと接続し、データの取得・追加・編集・削除を実施するサンプルです。

## 事前準備
事前準備として、以下の処理を実行してください。
- 外部データベースを作成します。本プラグインではMySQLのサンプルデータベース「world」を利用しています。[公式サイト](https://dev.mysql.com/doc/index-other.html)からzipをダウンロードした上で、お使いのMySQL（またはMariaDB）環境で解凍したSQLを実行してください。  

- config/database.phpを開き、connectionsに以下を追加します。

``` php
        'world' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE_WORLD', 'world'), // データベース名を変更する場合、こちらを修正
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
```

## 権限設定
プラグイン登録後に役割グループ設定で、権限の付与を行ってください。  
プラグインの権限としては以下の2種類があります。  
- "設定変更"はプラグイン管理の編集画面で設定を変更できる権限です。  
- "利用・アクセス"はプラグインのエンドポイントにアクセスしてCRUD機能を利用する権限です。  

## プラグイン管理の設定項目
### 詳細設定
- "URI"はこのプラグインにアクセスするためのURIを指定します。  
※http(s)://(あなたのサイトURL)/admin/plugins/(上記で指定したURI) が実際のエンドポイントになります。  
- "すべてのユーザーが利用可能"をYESにすると、役割グループの設定に関わらず、すべてのユーザーがこのプラグインを利用できます。  

## メニュー設定
本プラグインをメニューから利用する場合は管理者設定＞メニューから登録を行ってください。  
- "メニュー種類"は「プラグイン」を選択します。  
- "対象"で本プラグイン（MySQL World連携）を選択します。  
※その他の項目についてはExmentのドキュメントで「その他標準機能＞メニュー」のページをご覧ください。  
