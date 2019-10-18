<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Authorization Token is Invalid',
                ]);
            }

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                'status' => 401,
                'message' => 'Authorization Token is Expired',
            ]);

            // try {
            //     $newToken = JWTAuth::refresh(JWTAuth::getToken());
            //     $user = JWTAuth::setToken($newToken)->toUser();
            //     $response = $next($request);
            //     $response->headers->set('Authorization-Token', $newToken);
            //     return $response;

            // } catch (JWTException $e) {
            //     return response()->json([
            //         'status' => 401,
            //         'message' => 'Authorization Token is Expired',
            //     ]);
            // }

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json([
                'status' => 401,
                'message' => 'Authorization Token is Invalid',
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 401,
                'message' => 'Authorization Token is Invalid',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 401,
                'message' => 'Authorization Token not found',
            ]);
        }

        return $next($request);
    }
}
