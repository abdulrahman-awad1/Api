<?php

namespace App\Http\Middleware;

use App\trait\apiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckAdminToken
{
    use apiResponse;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = null;
        try {
            $user = JWTAuth:: parseToken()->authenticate();

        }
        catch (\Exception $e)
        {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return $this->returnError('e3001','INVALID_TOKEN');
            }elseif ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return $this->returnError('e3001','Expired_TOKEN');

            } else return $this->returnError('e3001','NOTFOUND_TOKEN');
        }
        catch (\Throwable $e)
        {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return $this->returnError('e3001','INVALID_TOKEN');
            }elseif ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return $this->returnError('e3001','Expired_TOKEN');

            } else return $this->returnError('e3001','NOTFOUND_TOKEN');
        }
        if (!$user)
            $this->returnError('e3001','UNAUTHENTICATED' );

        return $next($request);
    }
}
