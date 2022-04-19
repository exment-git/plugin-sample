# プラグイン(CRUDページ) サンプル - 独自のSQLでExmentデータ表示
AテーブルがBテーブルを参照、BテーブルがCテーブルを参照、しているような場合に、Aテーブルのビューからは通常Bテーブルの項目しか参照できません。独自のSQLを用いて、3階層の項目を同時に参照できるようにするサンプルです。

## 事前準備

### テンプレート導入

- サンプルテンプレートをダウンロードします。  
[サンプルテンプレート](show_levels3.zip)

- 管理者設定＞テンプレートから対象テンプレートのアップロードを行ってください。  
    - "顧客","仕入先","製品","受注"の4テーブルとそれぞれのメニューが追加されます。  
    - "受注"は→"顧客"と"製品"を、"製品"は→"仕入先"を、参照しています。  

### プラグインのインストール
プラグインのインストールを行います。

### (補足)メニュー設定
本プラグインをメニューから利用する場合は、管理者設定＞メニューから登録を行ってください。  
- "メニュー種類"は「プラグイン」を選択します。  
- "対象"で本プラグイン（他のサイトのExmentデータ表示）を選択します。  
※その他の項目についてはExmentのドキュメントで「その他標準機能＞メニュー」のページをご覧ください。  

## (補足)プラグインのカスタマイズ
本プラグインのサンプルテンプレートではなく、独自のテーブルを使用して本プラグインを使用する場合、以下のカスタマイズを実施してください。

- プラグインファイル"Plugin.php"を開きます。

- 関数"getTargetCustomColumns"を編集します。  
取得する列のテーブル名・列名を、配列で記入してください。

``` php
    /**
     * 取得対象のテーブル・列一覧を返却
     *
     * @return array
     */
    protected function getTargetCustomColumns() : array
    {
        // 'table': テーブル名
        // 'column': 列名
        return [
            ['table' => 'product', 'column' => 'price'],
            ['table' => 'order', 'column' => 'number'],
            ['table' => 'order', 'column' => 'amount'],
            ['table' => 'purchasing', 'column' => 'email'],
        ];
    }
```

- 関数"getJoinTableColumns"を編集します。3テーブルを結合して取得するので、それらを結合するための列情報を返却します。  
  - 第一引数には、自テーブルのテーブル名、外部キーに該当する列名を記入します。
  - 第二引数には、親テーブルのテーブル名、外部キーに該当する列名を記入します。
  - 第三引数には、親々テーブルのテーブル名を記入します。

``` php
    /**
     * テーブル結合の情報を取得
     *
     * @return array
     */
    protected function getJoinTableColumns() : array
    {
        return [
            // 第一引数：自テーブルのテーブル名、外部キーに該当する列名
            // ※リレーション設定で、1:nリレーションの場合は、foreign_columnを"parent_id"と記入してください
            ['table' => 'order', 'foreign_column' => 'product'],
            // 第二引数：1つ上のテーブルのテーブル名、外部キーに該当する列名
            // ※リレーション設定で、1:nリレーションの場合は、foreign_columnを"parent_id"と記入してください
            ['table' => 'product', 'foreign_column' => 'purchasing'],
            // 第三引数：2つ上のテーブルのテーブル名
            ['table' => 'purchasing'],
        ];
    }
```

- これで、正常にテーブルを取得できます。