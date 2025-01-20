<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EvaluationToken;

class EvaluationTokenController extends Controller
{
    public function recent()
    {
        return EvaluationToken::with('module')
            ->latest()
            ->take(10)
            ->get();
    }
}
