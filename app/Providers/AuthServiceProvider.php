<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

// --- Thêm các Model cần thiết cho việc kiểm tra quyền ---
use App\Models\User;
use App\Models\ChatConversation;
use App\Models\ChatParticipant;
use App\Models\Post;
use App\Policies\PostPolicy;
use Illuminate\Support\Facades\Log;
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Post::class => PostPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
   public function boot(): void
{
    // LOG #1: Kiểm tra xem Gate::before có chạy không
    Gate::before(function ($user, $ability) {
        $hasRole = $user->hasAnyRole(['admin', 'super_admin']);
        Log::info("--- Bắt đầu kiểm tra quyền ---");
        Log::info("Gate::before => User ID: {$user->id}, Quyền: '{$ability}', Có vai trò Admin?: " . ($hasRole ? 'CÓ' : 'KHÔNG'));
        
        if ($hasRole) {
            Log::info("Gate::before => Trả về TRUE (cho phép).");
            return true;
        }
    });

    Gate::define('manage_chat', function (User $user) {
        return $user->hasAnyRole(['admin', 'super_admin']);
    });

    // LOG #2: Kiểm tra xem Gate 'participate' có chạy và kết quả là gì
    Gate::define('participate', function (User $user, ChatConversation $conversation) {
        $isParticipant = $conversation->participants()->where('user_id', $user->id)->exists();
        Log::info("Gate 'participate' => User ID: {$user->id}, Hội thoại ID: {$conversation->id}, Là thành viên?: " . ($isParticipant ? 'CÓ' : 'KHÔNG'));
        Log::info("--- Kết thúc kiểm tra quyền ---");
        return $isParticipant;
    });
}



}