<?php

namespace App\Http\Controllers;

/**
 * HeartbeatController class
 *
 * @package App\Http\Controllers
 */
class HeartbeatController extends Controller
{
    public function index()
    {
        echo config('app.name') . ' ' . config('app.version');
    }
}
