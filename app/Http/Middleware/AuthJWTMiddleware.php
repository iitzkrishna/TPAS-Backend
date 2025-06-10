<?php

namespace App\Http\Middleware;

use Closure, Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthJWTMiddleware
{
    /**
     * Middleware for authenticating JWT tokens.
     *
     * This middleware attempts to authenticate the incoming request using JWT tokens.
     * It catches token-related exceptions and returns appropriate responses for invalid,
     * expired, or missing tokens. If the token is valid, the request continues to the next
     * middleware or the route handler.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof \PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json(['status' => 'Token is Invalid'], 403);
            }
            else if ($e instanceof \PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json(['status' => 'Token is Expired'], 401);
            }
            else if ($e instanceof \PHPOpenSourceSaver\JWTAuth\Exceptions\TokenBlacklistedException){
                return response()->json(['status' => 'Token is Blacklisted'], 400);
            }
            else {
		        return response()->json(['status' => 'Authorization Token not found'], 404);
            }
        }
        return $next($request);
    }
}
