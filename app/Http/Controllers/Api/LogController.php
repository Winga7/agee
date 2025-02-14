<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogController extends Controller
{
  /**
   * Enregistre un log depuis le frontend
   *
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function store(Request $request)
  {
    try {
      $validated = $request->validate([
        'message' => 'required|string',
        'data' => 'nullable|array',
        'level' => 'nullable|string|in:emergency,alert,critical,error,warning,notice,info,debug'
      ]);

      $level = $validated['level'] ?? 'info';

      Log::$level($validated['message'], $validated['data'] ?? []);

      return response()->json(['status' => 'success']);
    } catch (\Exception $e) {
      return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
  }
}
