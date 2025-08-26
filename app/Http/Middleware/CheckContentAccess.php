<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Route;

class CheckContentAccess
{
    public function handle($request, Closure $next)
    {
        $user = auth()->user();

        if ($user && $user->hasRole('content') || $user->hasRole('content_manager')) {
            $allowedPrefixes = [
                'admin.posts', 'admin.post-tags', 'admin.categories', 'admin.banner', 'admin.media'
            ];

            $currentRoute = Route::currentRouteName();

            $allowed = false;
            foreach ($allowedPrefixes as $prefix) {
                if (str_starts_with($currentRoute, $prefix)) {
                    $allowed = true;
                    break;
                }
            }

            if (!$allowed) {
                return redirect()->route('admin.posts.index')->with('error', 'Bạn không có quyền truy cập trang này.');
            }
        }

        return $next($request);
    }
}

