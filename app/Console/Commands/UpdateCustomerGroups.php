<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CustomerGroup;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateCustomerGroups extends Command
{
    protected $signature = 'customers:update-groups';
    protected $description = 'Cập nhật nhóm khách hàng dựa trên đơn hàng và chi tiêu';

    public function handle()
    {
        $groups = CustomerGroup::orderByDesc('priority')->get();

        $users = User::whereHas('orders', function ($q) {
                $q->where('payment_status', 'paid');
            })
            ->withCount(['orders as total_orders' => function ($q) {
                $q->where('payment_status', 'paid');
            }])
            ->withSum(['orders as total_spent' => function ($q) {
                $q->where('payment_status', 'paid');
            }], 'grand_total')
            ->get();

        DB::beginTransaction();

        try {
            foreach ($users as $user) {
                $assignedGroupId = null;

                foreach ($groups as $group) {
                    if ($user->total_orders >= $group->min_order_count
                        && $user->total_spent >= $group->min_total_spent) {
                        $assignedGroupId = $group->id;
                        break; // nhóm ưu tiên cao nhất tìm thấy thì dừng
                    }
                }

                if ($assignedGroupId) {
                    // Gán nhóm mới, đồng thời xóa các nhóm cũ (nếu có)
                    $user->customerGroups()->sync([$assignedGroupId]);
                } else {
                    // Xóa hết nhóm nếu không thỏa nhóm nào
                    $user->customerGroups()->sync([]);
                }
            }

            DB::commit();
            $this->info('Cập nhật nhóm khách hàng thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Cập nhật nhóm khách hàng thất bại: ' . $e->getMessage());
        }
    }
}
