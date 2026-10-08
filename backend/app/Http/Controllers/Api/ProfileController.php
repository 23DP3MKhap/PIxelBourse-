<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProfileController extends Controller
{
    /**
     * The currently authenticated user.
     */
    public function show(#[CurrentUser] User $user): UserResource
    {
        return new UserResource($user);
    }

    /**
     * Update the user's own username and/or email.
     */
    public function update(UpdateProfileRequest $request, #[CurrentUser] User $user): UserResource
    {
        $user->update($request->validated());

        return new UserResource($user);
    }

    /**
     * Change the password and log out every other device.
     */
    public function updatePassword(UpdatePasswordRequest $request, #[CurrentUser] User $user): Response
    {
        $user->update(['password' => $request->validated('password')]);

        $user->tokens()->whereKeyNot($user->currentAccessToken()->getKey())->delete();

        return response()->noContent();
    }

    /**
     * Delete the user's own account. Requires the password as confirmation.
     */
    public function destroy(Request $request, #[CurrentUser] User $user): Response
    {
        $request->validate([
            'password' => ['required', 'string', 'current_password:sanctum'],
        ]);

        abort_if($user->hasGameHistory(), 409, 'Accounts with images, listings, trades or transactions cannot be deleted.');

        $user->tokens()->delete();
        $user->delete();

        return response()->noContent();
    }
}
