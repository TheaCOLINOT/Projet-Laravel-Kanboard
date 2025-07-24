<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class VerifyProjectUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $project = $request->route('project');

        $user = Auth::user();
        if (!$user->projects->contains($project)) {
            return redirect()->route('home')->with('error', 'Vous n\'avez pas accès à ce projet.');
        }

        return $next($request);
    }
}
