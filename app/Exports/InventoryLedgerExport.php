<?php

namespace App\Exports;

use App\Models\InventoryMovement;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Http\Request;

class InventoryLedgerExport implements FromView
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $query = InventoryMovement::with([
            'productVariant.product',
            'productVariant.attributeValues',
            'storeLocation',
            'user',
        ]);

        // Apply filters (giống như trong controller)
        if ($province = $this->request->province_code) {
            $query->whereHas('storeLocation', fn ($q) => $q->where('province_code', $province));
        }

        if ($district = $this->request->district_code) {
            $query->whereHas('storeLocation', fn ($q) => $q->where('district_code', $district));
        }

        if ($reason = $this->request->transaction_type) {
            $query->where('reason', $reason);
        }

        if ($this->request->filled('date_start')) {
            $query->whereDate('created_at', '>=', $this->request->date_start);
        }

        if ($this->request->filled('date_end')) {
            $query->whereDate('created_at', '<=', $this->request->date_end);
        }

        if ($search = $this->request->search) {
            $query->whereHas('productVariant', function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                    ->orWhereHas('product', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        $movements = $query->orderByDesc('created_at')->get();

        return view('admin.reports.export', [
            'movements' => $movements,
        ]);
    }
}
