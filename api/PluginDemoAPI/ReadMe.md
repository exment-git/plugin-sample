# プラグイン(API) サンプル - カスタム列情報取得API
列名からカスタム列情報を取得するサンプルAPIです。

## 主な機能

- パラメータ又はURLでカスタムテーブルとカスタム列の名前を指定します。
- 指定されたカスタム列の情報をJSON形式で返します。

## 事前準備

※事前に、[こちら](https://exment.net/docs/#/ja/api)を参考にAPIの利用設定を行ってください。


## 実行方法
- プラグインをインストールします。

- APIを利用するためのプログラム等を用意します。  

    - トークンを取得する際のAPIスコープは「plugin」にする必要があります。  
    - APIにアクセスするためのURLは以下の二通りです。
        - 「http(s)://(ExmentのURL)/admin/api/plugins/sampleapi/column」  
            ※テーブル名と列名はパラメータ渡し
        - 「http(s)://(ExmentのURL)/admin/api/plugins/sampleapi/tablecolumn/(テーブル名)/(列名)」


