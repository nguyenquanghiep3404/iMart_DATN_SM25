<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderManagerController extends Controller
{
    public function index(){
        $users = User::whereIn('id', function ($query) {
            $query->select('user_id')
                  ->from('role_user')
                  ->where('role_id', 5);
        })->get();
        // dd($users->toArray());
        // Trả về view kèm dữ liệu
        return view('admin.oderMannager.index', compact('users'));
    }
}
