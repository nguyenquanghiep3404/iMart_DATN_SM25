<?php

namespace App\Http\Controllers;

// Import các lớp cần thiết từ framework
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

// Lớp Controller của bạn phải kế thừa từ BaseController của Laravel
class Controller extends BaseController
{
    /**
     * Sử dụng 2 traits quan trọng này để cung cấp các năng lực
     * phân quyền và validation cho tất cả các controller con.
     */
    use AuthorizesRequests, ValidatesRequests;
}
