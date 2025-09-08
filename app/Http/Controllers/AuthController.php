<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\HttpResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="Operations related to user authentication"
 * )
 */
class AuthController extends Controller
{
    protected $http;

    public function __construct(HttpResponseService $http)
    {
        $this->http = $http;
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authentication"},
     *     summary="Authenticate user and return token",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="secret")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Token generated successfully"),
     *     @OA\Response(response=401, description="Invalid credentials")
     * )
     */
    public function login(Request $request) {

       
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6']
        ]);

        $email = $request->email;
        $password = $request->password;

        $attempt = Auth::attempt([
            'email' => $email,
            'password' => $password
        ]);

        if(!$attempt) {
            return $this->http->unauthorized('Invalid credentials');
        }

         /** @var \App\Models\User $user */
        $user = Auth::user();
      
        //create token with abillities
        $token = $user->createToken( 'api-token', $user->abilities, now()->addDay() )->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Authentication"},
     *     summary="Revoke current token",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Logged out successfully"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function logout(Request $request) {
        
        //  /** @var \App\Models\User $user */
        // $user = Auth::user();

        // var_dump($request->token);
        // exit;

        if (!$request->user() || !$request->user()->currentAccessToken()) {
            return $this->http->notFound('Operation error or access denied');
        }
        
        $request->user()->currentAccessToken()->delete();

        return $this->http->ok('Logged out successfully');
       
    }
}
