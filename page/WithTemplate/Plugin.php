<?php

namespace App\Plugins\YasumiPage;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Exceedone\Exment\Services\Plugin\PluginPageBase;

class Plugin extends PluginPageBase
{
    /**
     * Display a listing of the resource.
     *
     * @return Content|\Illuminate\Http\Response
     */

    public function index()
    {
        $holidays = \Yasumi\Yasumi::create('Japan', \Carbon\Carbon::now()->year);
        return view('exment_yasumi_page::holidays');
    }
}