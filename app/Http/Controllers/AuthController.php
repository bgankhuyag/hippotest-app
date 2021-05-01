<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request){
    	$validator = Validator::make($request->all(), [
          'phone' => 'required|integer|min:10000000|regex:/^[0-9]{8}$/',
          'password' => 'required|string|min:6',
      ]);
      if ($validator->fails()) {
          return response()->json($validator->errors(), 422);
      }
      if (! $token = auth()->attempt($validator->validated())) {
          return response()->json(['error' => 'Unauthorized'], 401);
      }
      return $this->createNewToken($token);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {
      $validDate = date('1998-01-01');
      $validator = Validator::make($request->all(), [
          'first_name' => 'required|string|between:2,100',
          'last_name' => 'required|string|between:2,100',
          // 'email' => 'required|string|email|max:100|unique:users',
          'birth_date' => 'date_format:Y-m-d|after_or_equal:'.$validDate,
          'register' => 'required|regex:/^[A-Z]{2,2}[0-9]{8,8}$/|unique:users',
          'phone' => 'required|integer|min:10000000|regex:/^[0-9]{8}$/|unique:users',
          'password' => 'required|string|confirmed|min:6',
          'student_id' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
      ]);
      if($validator->fails()){
          return response()->json(['error' => $validator->errors()->toJson(), 'success' => false], 400);
      }
      $file = $request->student_id;
      $imageName = time() . $file->getClientOriginalName();
      Storage::disk('s3')->put($imageName, file_get_contents($file));
      $user = User::create([
          'first_name' => $request->first_name,
          'last_name' => $request->last_name,
          'birth_date' => $request->birth_date,
          // 'email' => $request->email,
          'phone' => $request->phone,
          'register' => $request->register,
          'student_id' => $imageName,
          'points' => 0,
          'password' => bcrypt($request->password),
      ]);

      return response()->json([
        'success' => true,
        'message' => 'User successfully registered',
        'user' => $user
      ], 201);
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
      auth()->logout();

      return response()->json(['message' => 'User successfully signed out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh() {
      return $this->createNewToken(auth()->refresh());
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile() {
      return response()->json(auth()->user());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token){
      return response()->json([
        'access_token' => $token,
        'token_type' => 'bearer',
        'expires_in' => auth()->factory()->getTTL() * 60,
        'user' => auth()->user()
      ]);
    }

}
