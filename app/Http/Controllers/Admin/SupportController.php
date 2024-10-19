<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupportStoreRequest;
use App\Http\Requests\SupportUpdateRequest;
use App\Models\Support;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class SupportController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Support::query()->latest()->paginate(10));
    }

    public function store(SupportStoreRequest $request): JsonResponse
    {
        Gate::authorize('create', Support::class);

        $data = $request->validated();

        if($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('supports', 'public');
            $data['image'] = $imagePath;
        }

        $request->user()->supports()->create($data);

        return response()->json(['success' => 'Support has been created successfully.'], Response::HTTP_CREATED);
    }

    public function show(Support $support): JsonResponse
    {
        Gate::authorize('view', $support);

        return response()->json($support, Response::HTTP_OK);
    }

    public function update(SupportUpdateRequest $request): JsonResponse
    {
        $data = $request->validated();

        $support = Support::find($data['support_id']);

        Gate::authorize('update', $support);

        if($request->hasFile('image')) {

            if(Storage::disk('public')->exists($support->image)){
                Storage::disk('public')->delete($support->image);
            }

            $imagePath = $request->file('image')->store('supports', 'public');
            $data['image'] = $imagePath;
        }

        $support->update($data);

        return response()->json([
            'success' => 'Support has been updated successfully.',
            'support' => $support
        ], Response::HTTP_CREATED);
    }

    public function destroy(Support $support): JsonResponse
    {
        Gate::authorize('delete', $support);

        if(Storage::disk('public')->exists($support->image)){
            Storage::disk('public')->delete($support->image);
        }

        $support->delete();

        return response()->json(['success' => 'Support has been deleted successfully.'], Response::HTTP_CREATED);
    }
}
