<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function welcome(): View
    {
        return view('welcome');
    }
}
