<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;

class ContentStaffManagementController extends Controller
{
    /**
     * Hiển thị trang danh sách nhân viên content.
     */
    public function index(Request $request)
    {
        $contentRole = Role::where('name', 'content_manager')->firstOrFail();

        $query = User::whereHas('roles', fn($q) => $q->where('role_id', $contentRole->id))
            ->withCount([
                'posts as posts_count',
                'posts as views_count' => function ($q) {
                    $q->select(\Illuminate\Support\Facades\DB::raw('COALESCE(SUM(view_count),0)'));
                }
            ]);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $contentStaffs = $query->latest()->paginate(10);

        $allContentQuery = User::whereHas('roles', fn($q) => $q->where('role_id', $contentRole->id));
        $allContentIds = (clone $allContentQuery)->pluck('id');

        $stats = [
            'total' => $allContentQuery->count(),
            'active' => (clone $allContentQuery)->where('status', 'active')->count(),
            'total_posts' => Post::whereIn('user_id', $allContentIds)->count(),
            'total_views' => Post::whereIn('user_id', $allContentIds)->sum('view_count'),
        ];

        return view('admin.content_staffs.index', compact('contentStaffs', 'stats'));
    }

    public function create()
    {
        return view('admin.content_staffs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone_number' => ['required', 'string', 'max:15', 'unique:users,phone_number'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'status' => ['required', 'in:active,inactive,banned'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'status' => $request->status,
        ]);

        $contentRole = Role::where('name', 'content_manager')->first();
        if ($contentRole) {
            $user->roles()->attach($contentRole);
        }

        return redirect()->route('admin.content-staffs.index')->with('success', 'Thêm nhân viên content thành công.');
    }


    public function edit(User $contentStaff)
    {
        if (!$contentStaff->hasRole('content_manager')) {
            abort(404);
        }
        return view('admin.content_staffs.edit', compact('contentStaff'));
    }

    public function update(Request $request, User $contentStaff)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $contentStaff->id],
            'phone_number' => ['required', 'string', 'max:15', 'unique:users,phone_number,' . $contentStaff->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'status' => ['required', 'in:active,inactive,banned'],
        ]);

        $contentStaff->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'status' => $request->status,
        ]);

        if ($request->filled('password')) {
            $contentStaff->password = Hash::make($request->password);
            $contentStaff->save();
        }

        return redirect()->route('admin.content-staffs.index')->with('success', 'Cập nhật thông tin thành công.');
    }

    public function show(User $contentStaff)
    {
        if (!$contentStaff->hasRole('content_manager')) {
            abort(404);
        }

        // Eager load bài viết với coverImage và category
        $posts = Post::with(['coverImage', 'category'])
            ->where('user_id', $contentStaff->id)
            ->latest()
            ->paginate(10);

        // Thống kê
        $postsCount = $posts->total();
        $viewsCount = Post::where('user_id', $contentStaff->id)->sum('view_count');
        $averageViews = $postsCount > 0 ? round($viewsCount / $postsCount, 2) : 0;

        return view('admin.content_staffs.show', compact('contentStaff', 'posts', 'postsCount', 'viewsCount', 'averageViews'));
    }


    public function destroy(User $contentStaff)
    {
        if (!$contentStaff->hasRole('content_manager')) {
            abort(404);
        }
        $contentStaff->delete();
        return redirect()->route('admin.content-staffs.index')->with('success', 'Đã chuyển nhân viên vào thùng rác.');
    }

    public function trash()
    {
        $contentRole = Role::where('name', 'content_manager')->firstOrFail();
        $trashedContentStaffs = User::onlyTrashed()
            ->whereHas('roles', fn($q) => $q->where('role_id', $contentRole->id))
            ->orderBy('deleted_at', 'desc')
            ->paginate(10);
        return view('admin.content_staffs.trash', compact('trashedContentStaffs'));
    }

    public function restore($id)
    {
        $contentStaff = User::onlyTrashed()->findOrFail($id);
        $contentStaff->restore();
        return redirect()->route('admin.content_staffs.trash')->with('success', "Đã khôi phục nhân viên '{$contentStaff->name}' thành công!");
    }

    public function forceDelete($id)
    {
        $contentStaff = User::onlyTrashed()->findOrFail($id);
        $contentStaff->roles()->detach();
        $contentStaff->forceDelete();
        return redirect()->route('admin.content_staffs.trash')->with('success', "Đã xóa vĩnh viễn nhân viên '{$contentStaff->name}'.");
    }
}
