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