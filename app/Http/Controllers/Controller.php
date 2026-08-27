<?php

namespace App\Http\Controllers;

use App\Helpers\General;

abstract class Controller
{
    public $general;
    
    public function __construct()
    {
        $this->general = new General();
        config($this->general->getAllSettings());
        \View::share('general', $this->general);
    }
}
