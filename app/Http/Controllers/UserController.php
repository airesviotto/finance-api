<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\HttpResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    protected $http;

    public function __construct(HttpResponseService $http)
    {
        $this->http = $http;
    }
   

     // Profile user
    public function profile()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $this->http->ok($user, 'User profile');
    }

    // Update name and email
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

    // Update password
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

    // delete account
    public function deleteProfile()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->delete();

        return $this->http->ok(null, 'User account deleted');
    }

}
