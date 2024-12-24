<?php
namespace App\Http\Middleware;

use Closure;

class Gc
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
        $response = $next($request);
        gc_collect_cycles();
        return $response;
    }
}
