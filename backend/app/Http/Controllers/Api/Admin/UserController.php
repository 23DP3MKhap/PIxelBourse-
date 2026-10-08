<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * List users, optionally filtered by ?search= (username or email) and ?role=.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = User::query()->latest('id');

        $search = $request->string('search')->trim()->value();

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->enum('role', UserRole::class)) {
            $query->where('role', $role);
        }

        return UserResource::collection($query->paginate(20)->withQueryString());
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }

    /**
     * Update a user's username, email or role.
     */
    public function update(UpdateUserRequest $request, User $user, #[CurrentUser] User $admin): UserResource
    {
        $role = $request->enum('role', UserRole::class);

        if ($user->is($admin) && $role !== null && $role !== UserRole::Admin) {
            throw ValidationException::withMessages([
                'role' => 'You cannot remove your own administrator role.',
            ]);
        }

        $user->fill($request->safe()->only(['username', 'email']));

        if ($role !== null) {
            $user->role = $role;
        }

        $user->save();

        return new UserResource($user);
    }

    public function destroy(User $user, #[CurrentUser] User $admin): Response
    {
        abort_if($user->is($admin), 403, 'You cannot delete your own account from the admin panel.');
        abort_if($user->hasGameHistory(), 409, 'Users with images, listings, trades or transactions cannot be deleted.');

        $user->tokens()->delete();
        $user->delete();

        return response()->noContent();
    }
}
