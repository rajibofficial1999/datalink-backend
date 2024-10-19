<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Category;
use App\Enums\Sites;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Jobs\Auth\OtpCodeJob;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index($firstCondition = null, $secondCondition = null): JsonResponse
    {
        $condition = $firstCondition === 'pending' ? '=' : '!=';

        $authUser = request()->user();
        $users = [];
        $sites = [];
        $categories = [];
        $status = UserStatus::cases();

        if ($authUser->isSuperAdmin || $authUser->isAdmin) {
            $initialUsers = User::with(['roles', 'team'])
                ->when(
                    $authUser->isSuperAdmin,
                    fn($query) =>
                    $query->when(!$secondCondition, fn($query) => $query->where('id', '!=', $authUser->id))
                )
                ->when($authUser->isAdmin, fn() => $authUser->teamMembers())
                ->where('status', $condition, UserStatus::PENDING)
                ->when($secondCondition, fn($query) => $query->whereNull('team_id')->whereNotNull('email_verified_at'))
                ->latest();

            if ($secondCondition) {
                $users = $initialUsers->get()->map(function ($user) {
                    return [
                        'value' => $user->id,
                        'label' => $user->name,
                    ];
                });


                $sites = collect(Sites::cases());

                $sites = $sites->map(function ($case) {
                    return [
                        'value' => $case->value,
                        'label' => Str::upper($case->value),
                    ];
                });

                $categories = collect(Category::cases());

                $categories = $categories->map(function ($case) {
                    return [
                        'value' => $case->value,
                        'label' => Str::upper($case->value),
                    ];
                });

                $status = [];
            } else {
                $users = $initialUsers->paginate(10);
            }
        }

        return response()->json([
            'users' => $users,
            'sites' => $sites,
            'categories' => $categories,
            'status' => $status
        ], Response::HTTP_OK);
    }

    public function roles(Request $request): JsonResponse
    {
        $roles = $request->user()->isSuperAdmin
            ? Role::where('name', '!=', 'simple-user')->get()
            : [];

        return response()->json($roles, Response::HTTP_OK);
    }

    public function store(UserStoreRequest $request): JsonResponse
    {
        Gate::authorize('create', User::class);

        if($request->user()->isAdmin) {
            $packageDetails = $request->user()->package?->details();
            $memberAbility = Arr::has($packageDetails, 'team') ? $packageDetails['team'] : null;

            if($memberAbility){
                if($request->user()->teamMembers()->count() >= $memberAbility){
                    throw ValidationException::withMessages(['package_error' => "You have permission to create only {$memberAbility} users. If want more then update your package."]);
                }
            }else{
                throw ValidationException::withMessages(['package_error' => 'Something went wrong!']);
            }

        }

        $data = $this->prepareUserData($request);

        $user = User::create($data);
        $user->roles()->attach($data['role']);

        $verifyToken = Str::uuid()->toString();
        $this->sendOTPCode($user, $verifyToken);

        return response()->json([
            'success' => 'Success! Please verify the email.',
            'verifyToken' => $verifyToken
        ], Response::HTTP_CREATED);
    }

    public function show(User $user): JsonResponse
    {
        Gate::authorize('view', $user);

        return response()->json($user, Response::HTTP_OK);
    }

    public function destroy(User $user): JsonResponse
    {
        Gate::authorize('delete', $user);

        $this->deleteUserAssets($user);

        $user->delete();

        return response()->json(['success' => 'User has been deleted successfully.'], Response::HTTP_OK);
    }

    public function update(UserUpdateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = User::find($data['user_id']);

        if (!$data['password']) {
            unset($data['password']);
        }

        Gate::authorize('update', $user);
        $this->updateUserRole($user, $data['role']);
        $this->handleProfilePhoto($request, $user);

        $user->update($data);

        return response()->json([
            'success' => 'User has been updated successfully.',
            'user' => $user,
        ], Response::HTTP_OK);
    }

    public function userStatus(Request $request, User $user): JsonResponse
    {
        Gate::authorize('changeStatus', $user);

        $data = $request->validate([
            'status' => ['required', new Enum(UserStatus::class)]
        ]);

        if ($user->isAdmin) {
            $user->teamMembers()->update(['status' => $data['status']]);
        }

        $user->update(['status' => $data['status']]);

        return response()->json(['success' => 'User status has been changed.'], Response::HTTP_OK);
    }

    protected function prepareUserData(Request $request): array
    {
        $data = $request->validated();
        $data['access_token'] = bin2hex(random_bytes(4));
        $data['status'] = UserStatus::APPROVED;

        if ($request->user()->isAdmin || !$request->role) {
            $data['role'] = Role::whereName('normal-user')->first();
            $data['team_id'] = $request->user()->id;
        }

        if ($request->hasFile('profile_photo')) {
            $data['avatar'] = $request->file('profile_photo')->store('users', 'public');
        }

        return $data;
    }

    protected function deleteUserAssets(User $user): void
    {
        if (Storage::disk('public')->exists($user->avatar ?? '')) {
            Storage::disk('public')->delete($user->avatar ?? '');
        }

        if ($user->isAdmin) {
            foreach ($user->teamMembers as $member) {
                if (Storage::disk('public')->exists($member->avatar ?? '')) {
                    Storage::disk('public')->delete($member->avatar ?? '');
                }
            }
            $user->teamMembers()->delete();
        }
    }

    protected function updateUserRole(User $user, $role): void
    {
        if ($role) {
            $user->roles()->sync($role);
        }
    }

    protected function handleProfilePhoto(Request $request, User $user): void
    {
        if ($request->hasFile('profile_photo')) {
            if (Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('profile_photo')->store('users', 'public');
        }
    }

    protected function sendOTPCode(User $user, string $emailVerifyToken, ?string $email = null): void
    {
        OtpCodeJob::dispatch($user, $emailVerifyToken, $email);
    }
}
