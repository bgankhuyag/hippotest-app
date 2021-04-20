<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;
use Exception;
use JWTAuth;
use Closure;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    // protected function redirectTo($request)
    // {
    //   if (! $request->expectsJson()) {
    //     return route('getQuiz');
    //   }
    // }

  public function handle($request, Closure $next) {
    // dd(Auth::user());
    try {
      // $response = $next($request);
      $request->headers->set('Authorization', JWTAuth::getToken());
      // $headers = apache_request_headers(); //get header
      // $request->headers->set('Authorization', $headers['authorization']);// set header in request
      // dd(auth()->check());

    // dd(JWTAuth::getToken());
    // dd('here');
      $user = JWTAuth::parseToken()->authenticate();
    } catch (Exception $e) {
      // dd('here');
      if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
        return response()->json(['error' => 'Token is Invalid', 'success' => false]);
      } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
        return response()->json(['error' => 'Token is Expired', 'success' => false]);
      } else {
        return response()->json(['error' => 'Authorization Token not found', 'success' => false]);
      }
    }
    // if (!Auth::guard('api')->check()) {
    //   return response()->json(['error' => 'Please login first!', 'success' => false], 401);
    // }
    return $next($request);
  }
}
