<?php
namespace App\Plugins\PluginSyncBatch;

use Exceedone\Exment\Services\Plugin\PluginBatchBase;
use Exceedone\Exment\Model\Define;
use Exceedone\Exment\Model\CustomTable;
use Illuminate\Support\Str;

class Plugin extends PluginBatchBase
{
    protected $useCustomOption = true;

    /**
     * execute
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

        $cities = \DB::connection('plugin_connection')
            ->table('city')
            ->where('CountryCode', $country_code)
            ->get();

        $custom_table = CustomTable::getEloquent('city');

        \DB::beginTransaction();

        try {
            foreach ($cities as $city) {
                $custom_value = $custom_table->getValueModel()
                    ->where('value->name_en', $city->Name)
                    ->orWhere('value->name_en', Str::lower($city->Name))->first();

                if (!isset($custom_value)) {
                    $custom_value = $custom_table->getValueModel();
                    $custom_value->setValue("name", $city->Name);
                    $custom_value->setValue("name_en", Str::lower($city->Name));
                }
                $custom_value->setValue("population", $city->Population);
                $custom_value->setValue("prefectures", Str::lower($city->District));
                $custom_value->save();
            }
            \DB::commit();
        } catch (\Exception $ex) {
            \DB::rollback();
            return false;
        }

        return true;
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
