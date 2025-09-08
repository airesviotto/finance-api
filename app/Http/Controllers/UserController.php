<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\HttpResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Tag(
 *     name="User",
 *     description="Operations related to user operations"
 * )
 */
class UserController extends Controller
{

    protected $http;

    public function __construct(HttpResponseService $http)
    {
        $this->http = $http;
    }
   

    /**
     * @OA\Get(
     *     path="/api/user/profile",
     *     tags={"Users"},
     *     summary="Get the authenticated user's profile",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="User profile returned"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function profile()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $this->http->ok($user, 'User profile');
    }

    /**
     * @OA\Put(
     *     path="/api/user/profile",
     *     tags={"Users"},
     *     summary="Update the authenticated user's profile",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Jane Doe"),
     *             @OA\Property(property="email", type="string", example="jane@example.com")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Profile updated successfully"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'name' => ['sometimes','string','max:255'],
            'email' => ['sometimes','email','unique:users,email,' . $user->id],
        ]);

        $user->update($request->only(['name', 'email']));

        return $this->http->ok($user, 'Profile updated successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/user/change-password",
     *     tags={"Users"},
     *     summary="Change the authenticated user's password",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password","new_password","new_password_confirmation"},
     *             @OA\Property(property="current_password", type="string", example="oldpass"),
     *             @OA\Property(property="new_password", type="string", example="newpass123"),
     *             @OA\Property(property="new_password_confirmation", type="string", example="newpass123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Password changed successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Access denied")
     * )
     */
    public function changePassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required','min:8'],
            'new_password_confirmation' => ['required', 'min:8', 'same:new_password']
        ]);

        // verify current password
       if (!Hash::check($request->current_password, $user->password)) {
            return $this->http->forbidden('Current password is incorrect');
        }

        // update password
        $user->password = bcrypt($request->new_password);
        $user->save();

        return $this->http->ok(null, 'Password updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/user/delete-account",
     *     tags={"Users"},
     *     summary="Delete the authenticated user's account",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Account deleted successfully"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function deleteProfile()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->delete();

        return $this->http->ok(null, 'User account deleted');
    }

    /**
     * @OA\Post(
     *     path="/api/user/avatar",
     *     tags={"Users"},
     *     summary="Upload or update the authenticated user's avatar",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="avatar", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Avatar updated successfully"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required','image','mimes:jpg,jpeg,png','max:2048']
        ]);
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Apagar avatar anterior se existir
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Salvar novo avatar
        $path = $request->file('avatar')->store('avatars', 'public');

        $user->avatar = $path;
        $user->save();

        return $this->http->ok($user->avatar_url, 'Avatar updated successfully');

    }

    /**
     * @OA\Delete(
     *     path="/api/user/avatar",
     *     tags={"Users"},
     *     summary="Delete the authenticated user's avatar",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Avatar deleted successfully"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function deleteAvatar()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->avatar) {
            //Remove avatar file
            Storage::disk('public')->delete($user->avatar);

            // reset null to database
            $user->avatar = null;
            $user->save();
        }

        return response()->json([
            'message' => 'Avatar deleted successfully',
            'avatar_url' => $user->avatar_url // drop to gravatar
        ]);
    }

    

}
