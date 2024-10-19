<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class NoticeController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Notice::query()->latest()->paginate(5));
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Notice::class);

        $data = $request->validate([
            'body' => 'required',
        ]);

        $request->user()->notices()->create($data);

        return response()->json(['success' => 'Notice has been created successfully.'], Response::HTTP_CREATED);
    }

    public function show(Notice $notice): JsonResponse
    {
        Gate::authorize('view', $notice);

        return response()->json($notice, Response::HTTP_OK);
    }

    public function update(Request $request, Notice $notice): JsonResponse
    {
        Gate::authorize('update', $notice);

        $data = $request->validate([
            'body' => 'required',
        ]);


        $notice->update($data);

        return response()->json([
            'success' => 'Notice has been updated successfully.',
            'notice' => $notice,
        ], Response::HTTP_CREATED);
    }

    public function destroy(Notice $notice): JsonResponse
    {
        Gate::authorize('delete', $notice);

        $notice->delete();

        return response()->json(['success' => 'Notice has been deleted successfully.'], Response::HTTP_CREATED);
    }
}
