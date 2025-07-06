<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Specification;
use App\Models\SpecificationGroup;
use Illuminate\Http\Request;

class SpecificationController extends Controller
{
    public function index()
    {
        $specifications = Specification::with('group')->orderBy('specification_group_id')->orderBy('order')->paginate(20);
        return view('admin.specifications.index', compact('specifications'));
    }
    
    // ... Các phương thức create, store, edit, update giữ nguyên ...

    public function create()
    {
        $groups = SpecificationGroup::orderBy('name')->get();
        return view('admin.specifications.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specification_group_id' => 'required|exists:specification_groups,id',
            'type' => 'required|string|in:text,textarea,boolean,select',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        Specification::create($validated);

        return redirect()->route('admin.specifications.index')->with('success', 'Tạo thông số thành công.');
    }

    public function edit(Specification $specification)
    {
        $groups = SpecificationGroup::orderBy('name')->get();
        return view('admin.specifications.edit', compact('specification', 'groups'));
    }

    public function update(Request $request, Specification $specification)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specification_group_id' => 'required|exists:specification_groups,id',
            'type' => 'required|string|in:text,textarea,boolean,select',
            'description' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        $specification->update($validated);

        return redirect()->route('admin.specifications.index')->with('success', 'Cập nhật thông số thành công.');
    }

    /**
     * Thực hiện xoá mềm một bản ghi.
     */
    public function destroy(Specification $specification)
    {
        $specification->delete();
        return back()->with('success', 'Đã chuyển thông số vào thùng rác.');
    }

    /**
     * Hiển thị danh sách các mục trong thùng rác.
     */
    public function trashed()
    {
        $specifications = Specification::onlyTrashed()->with('group')->paginate(15);
        return view('admin.specifications.trashed', compact('specifications'));
    }

    /**
     * Khôi phục một mục từ thùng rác.
     */
    public function restore($id)
    {
        $specification = Specification::onlyTrashed()->findOrFail($id);
        $specification->restore();

        return redirect()->route('admin.specifications.trashed')->with('success', 'Khôi phục thông số thành công.');
    }

    /**
     * Xoá vĩnh viễn một mục.
     */
    public function forceDelete($id)
    {
        $specification = Specification::onlyTrashed()->findOrFail($id);
        $specification->forceDelete();

        return redirect()->route('admin.specifications.trashed')->with('success', 'Đã xoá vĩnh viễn thông số.');
    }
}