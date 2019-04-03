<?php

namespace App\Http\Middleware;

use Auth;

use Closure;
use Session;

class Adminlogin
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
	
		// dd( Auth::check() );
	
		if ( !Auth::check() ) {
			return redirect('/');
		}
		if ( Auth::user()->role != 1 ) {
			return redirect('/');
		}	

        return $next($request);
    }
}
