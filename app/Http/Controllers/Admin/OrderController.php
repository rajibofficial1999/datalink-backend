<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\Package;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderStoreRequest;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Enum;

class OrderController extends Controller
{
    public function index($condition = null): JsonResponse
    {
        $condition = $condition ? '=' : '!=';
        $authUser = request()->user();

        $orders = Order::query()
            ->with('user')
            ->where('status', $condition, OrderStatus::PENDING)
            ->when($authUser->isAdmin || $authUser->isUser, function ($query) use ($authUser, $condition) {
                return $query->where('user_id', $authUser->id)->where('status', $condition, OrderStatus::PENDING);
            })
            ->latest()
            ->paginate(10);

        $status = $authUser->isSuperAdmin ? OrderStatus::cases() : [];

        return response()->json([
            'orders' => $orders,
            'status' => $status
        ], Response::HTTP_OK);
    }


    public function store(OrderStoreRequest $request): JsonResponse
    {
        $data = $request->validated();


        if ($request->hasFile('payment_screenshot')) {
            $imagePath = $request->file('payment_screenshot')->store('screenshots', 'public');
            $data['payment_screenshot'] = $imagePath;
        }

        $request->user()->orders()->create($data);

        return response()->json(['success' => 'Order has placed successfully.'], Response::HTTP_CREATED);
    }


    public function destroy(Order $order): JsonResponse
    {
        Gate::authorize('delete', $order);

        if (Storage::disk('public')->exists($order->payment_screenshot ?? '')) {
            Storage::disk('public')->delete($order->payment_screenshot ?? '');
        }

        $order->delete();

        return response()->json(['success' => 'Order has deleted successfully.'], Response::HTTP_OK);
    }


    public function orderStatus(Request $request, Order $order): JsonResponse
    {
        Gate::authorize('updateOrderStatus', Order::class);

        $data = $request->validate([
            'status' => ['required', new Enum(OrderStatus::class)],
        ]);

        $this->updateUserSubscription($data['status'], $order);

        $order->update($data);

        //        return response()->json(['success' => $r], Response::HTTP_OK);

        return response()->json(['success' => 'Order status has changed.'], Response::HTTP_OK);
    }

    protected function updateUserSubscription(string $status, Order $order): void
    {
        // If only Status as confirmed in that case
        if ($status === OrderStatus::CONFIRMED->value) {

            $data = $this->prepareSubscribeData($order);

            if ($order->user->isAdmin || $order->user->isUser) {
                $data['team_id'] = null;
                $order->user->roles()->sync($data['role_id']);
            }

            Arr::forget($data, 'role_id');

            $this->handleTeamMembers($order->user, $order);

            // Update Current User Subscription
            $order->user()->update($data);

            $order->user->teamMembers()->update($data);
        }
    }

    protected function prepareSubscribeData(Order $order): array
    {
        $data = [
            'package' => $order->package,
            'subscribed_at' => Carbon::now(),
            'expired_at' => Carbon::now()->addMonths((int) $order->period),
        ];

        $roleName = 'admin-user';

        // If selected package is Starter then user Role to be simple-user
        if ($order->package === Package::STARTER) {
            $roleName = 'normal-user';
        }

        $role = Role::whereName($roleName)->first();
        $data['role_id'] = $role->id;

        return $data;
    }

    protected function handleTeamMembers(User $user, Order $order): void
    {
        $packageDetails = $order->package->details();
        $memberAbility = $packageDetails['team'];

        if ($order->package === Package::STARTER) {
            $user->teamMembers()->delete();
        }

        if ($order->package === Package::STANDARD || $order->package === Package::PREMIUM) {
            // deleting members if package users limit is over
            if ($user->teamMembers()->count() > $memberAbility) {
                $user->teamMembers()->delete();
            }
        }
    }
}
