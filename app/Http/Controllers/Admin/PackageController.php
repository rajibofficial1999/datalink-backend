<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Package;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PackageController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Package::allDetails(), Response::HTTP_OK);
    }
}
