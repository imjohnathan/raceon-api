<?php

namespace App\Http\Middleware;

use Closure;

class HmacMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    //protected $webhook_secret = env("WEBHOOK_SECRET", "RUUVUSq831j056TbSAaywEAgoMuH0FwK");
    protected $webhook_secret = "RUUVUSq831j056TbSAaywEAgoMuH0FwK";

    public function handle($request, Closure $next)
    {
        if (!$request->hasHeader('X-Cyberbiz-Hmac-Sha256')) {
            return response()->json(['error' => 'Header Not Found.'], 401);
        }

        $header_hash = $request->header('X-Cyberbiz-Hmac-Sha256');

        $hash = base64_encode(hash_hmac('sha256', $request->getContent(), $this->webhook_secret, true));

        if (!hash_equals($header_hash, $hash)) {
            return response()->json(
                ['error' => 'Unauthorized.', 
                'header-hash' => $header_hash, 
                'cacl-hash' => $hash], 401);
        }
        return $next($request);
    }
}
