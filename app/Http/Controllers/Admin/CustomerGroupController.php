<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class CustomerGroupController extends Controller
{
    public function index()
    {
        $groups = CustomerGroup::withCount('users')->get();
        // dd($groups->toArray());
        return view('admin.customer_groups.index', compact('groups'));
    }

    public function save(Request $request, $id = null)
    {
        $messages = [
            'name.required' => 'Tên nhóm khách hàng là bắt buộc.',
            'name.unique' => 'Tên nhóm khách hàng đã tồn tại.',
            'min_order_count.required' => 'Số đơn hàng tối thiểu là bắt buộc.',
            'min_order_count.integer' => 'Số đơn hàng tối thiểu phải là số nguyên.',
            'min_order_count.min' => 'Số đơn hàng tối thiểu không được nhỏ hơn 0.',
            'min_total_spent.required' => 'Tổng chi tiêu tối thiểu là bắt buộc.',
            'min_total_spent.numeric' => 'Tổng chi tiêu tối thiểu phải là số.',
            'min_total_spent.min' => 'Tổng chi tiêu tối thiểu không được nhỏ hơn 0.',
            'priority.required' => 'Độ ưu tiên là bắt buộc.',
            'priority.integer' => 'Độ ưu tiên phải là số nguyên.',
            'priority.min' => 'Độ ưu tiên không được nhỏ hơn 0.',
        ];

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('customer_groups')->ignore($id),
            ],
            'description' => 'nullable|string',
            'min_order_count' => 'required|integer|min:0',
            'min_total_spent' => 'required|numeric|min:0',
            'priority' => 'required|integer|min:0',  // Thêm validate cho priority
        ], $messages);

        if ($id) {
            // Cập nhật
            $group = CustomerGroup::findOrFail($id);
            $group->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? '',
                'min_order_count' => $validated['min_order_count'] ?? 0,
                'min_total_spent' => $validated['min_total_spent'] ?? 0,
                'priority' => $validated['priority'] ?? 0,   // Lưu priority
            ]);
            $message = 'Cập nhật nhóm khách hàng thành công';
        } else {
            // Tạo mới
            CustomerGroup::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? '',
                'min_order_count' => $validated['min_order_count'] ?? 0,
                'min_total_spent' => $validated['min_total_spent'] ?? 0,
                'priority' => $validated['priority'] ?? 0,   // Lưu priority
            ]);
            $message = 'Tạo nhóm khách hàng thành công';
        }

        return response()->json(['message' => $message]);
    }

    public function updateCustomerGroups()
    {
        $groups = CustomerGroup::all();

        foreach ($groups as $group) {
            $qualifiedUsers = User::whereHas('orders', function ($query) {
                $query->where('payment_status', 'paid');
            })
            ->withCount(['orders as total_orders' => function ($query) {
                $query->where('payment_status', 'paid');
            }])
            ->withSum(['orders as total_spent' => function ($query) {
                $query->where('payment_status', 'paid');
            }], 'grand_total')
            ->having('total_orders', '>=', $group->min_order_count)
            ->having('total_spent', '>=', $group->min_total_spent)
            ->get();

            DB::table('customer_group_user')->where('customer_group_id', $group->id)->delete();

            foreach ($qualifiedUsers as $user) {
                DB::table('customer_group_user')->insert([
                    'customer_group_id' => $group->id,
                    'user_id' => $user->id,
                ]);
            }
        }

        return response()->json(['message' => 'Cập nhật nhóm khách hàng thành công']);
    }
    // xóa
    public function destroy($id)
    {
        $group = CustomerGroup::findOrFail($id);
        $group->delete();

        return response()->json(['message' => 'Đã xóa nhóm khách hàng.']);
    }
    // khôi phục
    public function trashed()
    {
        $groups = CustomerGroup::onlyTrashed()->get();
        return view('admin.customer_groups.trashed', compact('groups'));
    }
    public function restore($id)
    {
        $group = CustomerGroup::onlyTrashed()->findOrFail($id);
        $group->restore();

        return response()->json(['message' => 'Khôi phục nhóm thành công']);
    }
    public function forceDelete($id)
    {
        $group = CustomerGroup::withTrashed()->findOrFail($id);
        if ($group->trashed()) {
            $group->forceDelete();
            return response()->json(['message' => 'Nhóm khách hàng đã bị xóa vĩnh viễn.']);
        }
        return response()->json(['message' => 'Nhóm khách hàng phải ở trạng thái đã xóa để xóa vĩnh viễn.'], 400);
    }
}

