<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index()
    {
        return view('test', ['message' => 'Привіт з Laravel 11!']);
    }

    public function hello()
    {
        return '<h1>Привіт з тестового методу Laravel!</h1>';
    }
}