<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DomainStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\DomainStoreRequest;
use App\Http\Requests\DomainUpdateRequest;
use App\Models\Domain;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;

class DomainController extends Controller
{
    public function index($condition = null): JsonResponse
    {
        $condition = $condition ? '=' : '!=';
        $authUser = request()->user();

        $domains = [];
        if ($authUser->isAdmin || $authUser->isSuperAdmin) {
            $domains = Domain::query()
                ->with('user')
                ->where('status', $condition, DomainStatus::PENDING)
                ->when($authUser->isAdmin, function ($query) use ($authUser, $condition) {
                    return $query->where('user_id', $authUser->id)->where('status', $condition, DomainStatus::PENDING);
                })
                ->latest()
                ->paginate(10);
        }

        $status = $authUser->isSuperAdmin ? DomainStatus::cases() : [];

        return response()->json([
            'domains' => $domains,
            'status' => $status
        ], Response::HTTP_OK);
    }


    public function store(DomainStoreRequest $request): JsonResponse
    {
        Gate::authorize('create', Domain::class);

        $data = $request->validated();

        $name = $data['domain'];
        if (Str::startsWith($name, 'www.')) {
            $name = Str::replace('www.', '', $name);
        }

        $data['name'] = $name;
        $data['is_default'] = $data['privacy'];

        $data['amount'] = config('services.domain.price');

        if ($request->hasFile('screenshot')) {
            $imagePath = $request->file('screenshot')->store('screenshots', 'public');
            $data['screenshot'] = $imagePath;
        }

        $request->user()->domains()->create($data);

        return response()->json(['success' => 'User has been created successfully.'], Response::HTTP_CREATED);
    }

    public function show(Domain $domain): JsonResponse
    {
        Gate::authorize('view', $domain);

        return response()->json($domain, Response::HTTP_OK);
    }

    public function destroy(Domain $domain): JsonResponse
    {
        Gate::authorize('delete', $domain);

        if (Storage::disk('public')->exists($domain->screenshot ?? '')) {
            Storage::disk('public')->delete($domain->screenshot ?? '');
        }

        $domain->delete();

        return response()->json(['success' => 'Domain has been deleted successfully.'], Response::HTTP_OK);
    }

    public function updateDomain(DomainUpdateRequest $request): JsonResponse
    {
        $data = $request->validated();

        $domain = Domain::find($data['domain_id']);

        Gate::authorize('update', $domain);

        $name = $data['domain'];
        if (Str::startsWith($name, 'www.')) {
            $name = Str::replace('www.', '', $name);
        }

        $data['name'] = $name;
        $data['is_default'] = $data['privacy'];

        if ($request->hasFile('screenshot')) {

            if (Storage::disk('public')->exists($domain->screenshot ?? '')) {
                Storage::disk('public')->delete($domain->screenshot ?? '');
            }

            $imagePath = $request->file('screenshot')->store('screenshots', 'public');
            $data['screenshot'] = $imagePath;
        }

        $domain->update($data);

        return response()->json([
            'success' => 'Domain has been updated successfully.',
            'domain' => $domain,
        ], Response::HTTP_OK);
    }

    public function domainStatus(Request $request, Domain $domain): JsonResponse
    {
        Gate::authorize('updateDomainStatus', Domain::class);

        $data = $request->validate([
            'status' => ['required', new Enum(DomainStatus::class)],
        ]);

        $domain->update($data);

        return response()->json(['success' => 'Domain status has been changed.'], Response::HTTP_OK);
    }

    public function userDomains(User $user): JsonResponse
    {

        $domains = $user->domains->map(function ($domain) {
            return [
                'value' => $domain->id,
                'label' => $domain->name,
            ];
        });

        return response()->json([
            'domains' => $domains,
        ], Response::HTTP_OK);
    }
}
