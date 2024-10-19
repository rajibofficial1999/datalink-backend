<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Notice::query()->latest()->take(3)->get(), Response::HTTP_OK);
    }
}
