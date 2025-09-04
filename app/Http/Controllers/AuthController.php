<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\HttpResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected $http;

    public function __construct(HttpResponseService $http)
    {
        $this->http = $http;
    }

    public function login(Request $request) {

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $credentials =  $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6']
        ]);

        // $email = $request->email;
        // $password = $request->password;

        // $attempt = Auth::attempt([
        //     'email' => $email,
        //     'password' => $password
        // ]);

        // if(!$attempt) {
        //     return response()->json([
        //         'message' => 'Unauthorized'
        //     ]);
        // }

        if(!Auth::attempt($credentials)) {
            return $this->http->unauthorized('Invalid credentials');
        }

       

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
        
         /** @var \App\Models\User $user */
        $user = Auth::user();

        var_dump($request->token);
        exit;

        if (!$request->user() || !$request->user()->currentAccessToken()) {
            return $this->http->notFound('Operation error or access denied');
        }
        
        $request->user()->currentAccessToken()->delete();

        return $this->http->ok('Logged out successfully');
       
    }
}
