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