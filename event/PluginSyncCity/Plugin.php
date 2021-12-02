<?php
namespace App\Plugins\PluginSyncCity;

use Exceedone\Exment\Model\Define;
use Exceedone\Exment\Services\Plugin\PluginEventBase;
use Illuminate\Support\Str;

class Plugin extends PluginEventBase
{
    protected $useCustomOption = true;

    /**
     * Plugin Trigger
     */
    public function execute()
    {
        config(['database.connections.plugin_connection' => [
            'driver'    => $this->plugin->getCustomOption('custom_driver', 'mysql'),
            'host'      => $this->plugin->getCustomOption('custom_host', '127.0.0.1'),
            'port'  => $this->plugin->getCustomOption('custom_port', '3306'),
            'database'  => $this->plugin->getCustomOption('custom_database', 'test'),
            'username'  => $this->plugin->getCustomOption('custom_user', 'root'),
            'password'  => $this->plugin->getCustomOption('custom_password', 'password'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci'
        ]]);

        $country_code = 'JPN';
        $name_en = Str::ucfirst(Str::lower($this->custom_value->getValue('name_en')));
        $population = $this->custom_value->getValue('population');
        $prefectures = Str::ucfirst(Str::lower($this->custom_value->getValue('prefectures')));

        $city = \DB::connection('plugin_connection')
            ->table('city')
            ->where('CountryCode', $country_code)
            ->where('Name', $name_en)
            ->first();

        if (isset($city)) {
            if ($this->isDelete) {
                $result = \DB::connection('plugin_connection')
                    ->table('city')
                    ->where('ID', $city->ID)
                    ->delete();
            } else {
                $result = \DB::connection('plugin_connection')
                    ->table('city')
                    ->where('ID', $city->ID)
                    ->update([
                        'Population' => $population?? $city->Population,
                        'District' => $prefectures?? $city->District
                    ]);
            }
        } else {
            $result = \DB::connection('plugin_connection')
                ->table('city')
                ->insert([
                    'Name' => $name_en,
                    'CountryCode' => $country_code,
                    'Population' => $population,
                    'District' => $prefectures
                ]);
        }

        return $result;
    }

    /**
     * カスタムオプション（外部データベース接続先）
     *
     * @param $form
     * @return void
     */
    public function setCustomOptionForm(&$form)
    {
        $form->exmheader('外部データベースの情報');
        $form->select('custom_driver', 'データベースの種類')
            ->options(Define::DATABASE_TYPE)
            ->default('mysql');
        $form->text('custom_host', 'ホスト名')
            ->default('127.0.0.1');
        $form->text('custom_port', 'ポート番号')
            ->default('3306');
        $form->text('custom_database', 'データベース名');
        $form->text('custom_user', 'ユーザー名')
            ->default('root');
        $form->password('custom_password', 'パスワード');
    }
}