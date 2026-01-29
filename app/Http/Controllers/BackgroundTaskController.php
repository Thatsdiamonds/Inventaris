<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BackgroundTaskController extends Controller
{
    public function index()
    {
        $tasks = \App\Models\BackgroundTask::latest()->get();
        return view('tasks.index', compact('tasks'));
    }
}
