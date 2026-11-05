<?php

namespace App\Http\Controllers;

use App\Models\Groepsles;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainerController extends Controller
{
    public function index(Request $request): View
    {
        $lessen = Groepsles::where('trainer_id', $request->user()->id)
            ->orderBy('starttijd')->orderBy('id')->paginate(20);

        return view('trainer', compact('lessen'));
    }
}
