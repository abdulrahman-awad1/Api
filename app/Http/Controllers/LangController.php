<?php

namespace App\Http\Controllers;

use App\Models\language;
use Illuminate\Http\Request;

class LangController extends Controller
{
    public function index(){
        $posts= language::selection('id','name_'.app()->getLocale().'as name')->get(); // api دا معناه ان اللغه هتكون بناء ع ال
        return response()->json($posts);

    }}
