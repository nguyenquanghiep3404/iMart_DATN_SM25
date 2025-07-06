<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardAdminController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }
    public function headerData()
{
    $user = auth()->user();

    $unreadNotificationsCount = $user->unreadNotifications()->count();

    $recentNotifications = $user->notifications()
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get()
        ->map(function ($notification) {
            return [
                'type' => $notification->type,
                'title' => $notification->data['message'] ?? 'Thông báo mới',
                'time' => $notification->created_at->diffForHumans(),
                'icon' => match (true) {
                    str_contains($notification->type, 'User') => '<svg class="h-6 w-6 text-green-500" ...>...</svg>',
                    str_contains($notification->type, 'Order') => '<svg class="h-6 w-6 text-blue-500" ...>...</svg>',
                    str_contains($notification->type, 'Review') => '<svg class="h-6 w-6 text-yellow-500" ...>...</svg>',
                    default => '<svg class="h-6 w-6 text-gray-400" ...>...</svg>',
                }
            ];
        });

    return view('admin.partials.header', compact('unreadNotificationsCount', 'recentNotifications'));
}

}

