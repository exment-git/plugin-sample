# プラグイン(複数のプラグイン種類) サンプル - 複数のプラグイン種類を1つのプラグインで対応する
プラグイン(ボタン)とプラグイン(バッチ)を、1つのプラグインとして開発します。  
両方のプラグイン種類で呼び出すための共通クラスを作成し、呼び出します。

## 主な機能

- プラグイン(ボタン)とプラグイン(バッチ)をそれぞれ実装します。
- それぞれのプラグインでは、共通のクラスCommon.phpを呼び出します。
- Common.phpでは、ログを出力します。

## 事前準備

- 特になし


## 実行方法
- プラグインをインストールします。

- ボタンの実行方法
    - お知らせテーブルの詳細画面を開きます。  

    - ボタンが表示されます。  


- バッチの実行方法
    - 以下のコマンドを実行します。

```
php artisan exment:batch --name=BatchButton
```


## 実装方法の詳細

- config.jsonでは、以下のように、"plugin_type"をカンマ区切りで複数指定します。

``` json
{
    "uuid": "697b7391-4f14-42c9-2f18-be57a56b0f66",
    "plugin_name": "BatchButton",
    "plugin_view_name": "バッチ・ボタンの同時実装",
    "description": "バッチ・ボタンのプラグインを、同時に実装します。",
    "author":  "Kajitori",
    "version": "1.0.0",
    "plugin_type": "batch,button",
    "event_triggers": "form_menubutton_show",
    "target_tables": "information"
}

```


- PluginBatch.phpで、バッチ用のクラスを作成します。

``` php
<?php

namespace App\Plugins\BatchButton;

use Exceedone\Exment\Services\Plugin\PluginBatchBase;

class PluginBatch extends PluginBatchBase
{
    /**
     */
    public function execute()
    {
        Common::log($this->plugin);
    }
}
```


- PluginButton.phpで、バッチ用のクラスを作成します。

``` php
<?php

namespace App\Plugins\BatchButton;

use Exceedone\Exment\Services\Plugin\PluginButtonBase;

class PluginButton extends PluginButtonBase
{
    /**
     * @param [type] $form
     * @return void
     */
    public function execute()
    {
        Common::log($this->plugin);
    }
}
```


- Common.phpで、共通クラスを作成します。

``` php
<?php

namespace App\Plugins\BatchButton;

class Common
{
    public static function log($plugin)
    {
        \Log::debug($plugin->getCustomOption('text') ?? 'Executed!');
    }
}
```


- PluginSetting.phpで、設定を定義するためのクラスを作成します。

``` php
<?php

namespace App\Plugins\BatchButton;

use Exceedone\Exment\Services\Plugin\PluginSettingBase;

class PluginSetting extends PluginSettingBase
{
    protected $useCustomOption = true;

    /**
     * @param [type] $form
     * @return void
     */
    public function setCustomOptionForm(&$form)
    {
        $form->text('text', 'ログ出力文字列')
            ->help('ログに出力する文字列を設定します。')
            ->default('Executed!');
    }
}
```