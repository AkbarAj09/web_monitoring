<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LogbookController extends Controller
{
    /**
     * Show the leads master view
     */
    public function index()
    {
        return view('logbook.index');
    }
}
