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
            // Attempt to authenticate the request using JWT tokens
            JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            // dd($e->getMessage());
            // Handle different token-related exceptions
            if ($e instanceof \PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException) {
                // Return response for invalid tokens
                return ResponseService::response('UNAUTHORIZED', null, 'Token is Invalid');
            }
            else if ($e instanceof \PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException) {
                // Return response for expired tokens
                return ResponseService::response('UNAUTHORIZED', null, 'Token is Expired');
            }
            else {
                // Return response for missing authorization tokens
                return ResponseService::response('UNAUTHORIZED', null, 'Authorization Token not found');
            }
        }

        // Continue with the next middleware or the route handler if authentication is successful
        return $next($request);
    }
}
