<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpecificationGroup;
use Illuminate\Http\Request;

class SpecificationGroupController extends Controller
{
    public function index()
    {
        // Mặc định Eloquent sẽ chỉ lấy các bản ghi chưa bị xoá mềm
        $groups = SpecificationGroup::with('specifications')->orderBy('order')->paginate(15);
        return view('admin.specifications.groups.index', compact('groups'));
    }

    public function create()
    {
        return view('admin.specifications.groups.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:specification_groups,name',
            'order' => 'nullable|integer',
        ]);

        SpecificationGroup::create($validated);

        return redirect()->route('admin.specification-groups.index')->with('success', 'Tạo nhóm thông số thành công.');
    }

    public function edit(SpecificationGroup $specificationGroup)
    {
        return view('admin.specifications.groups.edit', compact('specificationGroup'));
    }

    public function update(Request $request, SpecificationGroup $specificationGroup)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:specification_groups,name,' . $specificationGroup->id,
            'order' => 'nullable|integer',
        ]);

        $specificationGroup->update($validated);

        return redirect()->route('admin.specification-groups.index')->with('success', 'Cập nhật nhóm thông số thành công.');
    }

    /**
     * Thực hiện xoá mềm một bản ghi.
     */
    public function destroy(SpecificationGroup $specificationGroup)
    {
        // Tự động xoá mềm các thông số con trước khi xoá mềm nhóm
        // Đây là cách xử lý ở tầng ứng dụng thay cho cascadeOnDelete
        $specificationGroup->specifications()->delete();
        
        $specificationGroup->delete();
        
        return back()->with('success', 'Đã chuyển nhóm thông số vào thùng rác.');
    }

    /**
     * Hiển thị danh sách các mục trong thùng rác.
     */
    public function trashed()
    {
        $groups = SpecificationGroup::onlyTrashed()->paginate(15);
        return view('admin.specifications.groups.trashed', compact('groups'));
    }

    /**
     * Khôi phục một mục từ thùng rác.
     */
    public function restore($id)
    {
        $group = SpecificationGroup::onlyTrashed()->findOrFail($id);
        
        // Khôi phục các thông số con trước
        $group->specifications()->onlyTrashed()->restore();
        
        $group->restore();

        return redirect()->route('admin.specification-groups.trashed')->with('success', 'Khôi phục nhóm thông số thành công.');
    }

    /**
     * Xoá vĩnh viễn một mục.
     */
    public function forceDelete($id)
    {
        $group = SpecificationGroup::onlyTrashed()->findOrFail($id);
        
        // Xóa vĩnh viễn các thông số con trước
        $group->specifications()->onlyTrashed()->forceDelete();

        $group->forceDelete();

        return redirect()->route('admin.specification-groups.trashed')->with('success', 'Đã xoá vĩnh viễn nhóm thông số.');
    }
}