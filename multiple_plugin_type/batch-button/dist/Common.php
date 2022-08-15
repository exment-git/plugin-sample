<?php

namespace App\Plugins\BatchButton;

class Common
{
    public static function log($plugin)
    {
        \Log::debug($plugin->getCustomOption('text') ?? 'Executed!');
    }
}