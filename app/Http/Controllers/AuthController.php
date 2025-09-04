<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request) {

        $credentials =  $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6']
        ]);

        if(!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();

        //-----------------------------------//
        //---THIS BLOCK GET ALL DATA VALUES--//
        //-----------------------------------//
       
        //Load roles and permissions
        // $user->load('roles.permissions');
        // //get abillities from user
        // $abillities = $user->roles->flatMap( function($role) {
        //     return $role->permissions->pluck('name');
        // })->unique()->toArray();

        //-----------------------------------//
        //---THIS BLOCK GET ONLY DATA ABILLITIES--//
        //-----------------------------------//
        $abillities = $user->roles()
                    ->with('permissions')
                    ->get()
                    ->pluck('permissions.*.name')
                    ->flatten()
                    ->unique()
                    ->toArray();

        //create token with abillities
        $token = $user->createToken('api-token', $abillities, now()->addDay())->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'abillities' => $abillities
        ]);
    }

    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
