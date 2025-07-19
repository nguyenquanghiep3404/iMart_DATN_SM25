<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FlashSale;
use App\Models\ProductVariant;
use App\Models\FlashSaleProduct;

class FlashSaleController extends Controller
{
    public function index()
    {
        // Lấy tất cả chiến dịch Flash Sale, kèm số lượng sản phẩm và các khung giờ
        $flashSales = FlashSale::withCount('flashSaleProducts')
            ->with('flashSaleTimeSlots')
            ->latest()
            ->get();

        return view('admin.flash_sales.index', compact('flashSales'));
    }

    // Hiển thị form tạo Flash Sale mới
    public function create()
    {
        return view('admin.flash_sales.create');
    }
    // Lưu Flash Sale mới
    public function store(Request $request)
    {
        $messages = [
    'name.required' => 'Vui lòng nhập tên chiến dịch.',
    'slug.required' => 'Vui lòng nhập slug.',
    'slug.unique' => 'Slug đã tồn tại. Vui lòng chọn slug khác.',
    'start_time.required' => 'Vui lòng chọn thời gian bắt đầu.',
    'start_time.date' => 'Thời gian bắt đầu không hợp lệ.',
    'end_time.required' => 'Vui lòng chọn thời gian kết thúc.',
    'end_time.date' => 'Thời gian kết thúc không hợp lệ.',
    'end_time.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
    'status.in' => 'Trạng thái không hợp lệ.',
    'time_slots.array' => 'Khung giờ không hợp lệ.',
    'time_slots.*.start_time.date_format' => 'Thời gian bắt đầu phải đúng định dạng HH:MM.',
    'time_slots.*.end_time.date_format' => 'Thời gian kết thúc phải đúng định dạng HH:MM.',
    'time_slots.*.end_time.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu của cùng khung giờ.',
];


        $validated = $request->validate([
    'name' => 'required|string',
    'slug' => 'required|unique:flash_sales,slug',
    'start_time' => 'required|date',
    'end_time' => 'required|date|after:start_time',
    'banner_image_url' => 'nullable|string',
    'status' => 'in:scheduled,active,finished,inactive',
    'time_slots' => 'nullable|array',
    'time_slots.*.start_time' => 'nullable|date_format:H:i',
    'time_slots.*.end_time' => [
        'nullable',
        'date_format:H:i',
        function ($attribute, $value, $fail) use ($request) {
            // Lấy index của phần tử hiện tại
            if (preg_match('/time_slots\.(\d+)\.end_time/', $attribute, $matches)) {
                $index = $matches[1];
                $start = $request->input("time_slots.$index.start_time");

                if ($start && $value && strtotime($value) <= strtotime($start)) {
                    $fail('Thời gian kết thúc phải sau thời gian bắt đầu của cùng khung giờ.');
                }
            }
        },
    ],
], $messages);


        // Tạo Flash Sale
        $flashSale = FlashSale::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'banner_image_url' => $validated['banner_image_url'] ?? null,
            'status' => $validated['status'],
        ]);

        // Lưu các khung giờ
        foreach ($validated['time_slots'] as $slot) {
            $flashSale->flashSaleTimeSlots()->create([
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
            ]);
        }

        return redirect()->route('admin.flash-sales.index')->with('success', 'Tạo Flash Sale thành công!');
    }


    // Hiển thị form chỉnh sửa Flash Sale
    public function edit(FlashSale $flashSale)
    {
        $variants = ProductVariant::with('product')->get();
        $flashSaleProducts = $flashSale->products()->with('variant.product')->get();

        return view('admin.flash_sales.edit', compact('flashSale', 'variants', 'flashSaleProducts'));
    }
    // Cập nhật Flash Sale
    public function update(Request $request, FlashSale $flashSale)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'slug' => 'required|unique:flash_sales,slug,' . $flashSale->id,
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'time_slot' => 'nullable|string',
            'banner_image_url' => 'nullable|string',
            'status' => 'in:scheduled,active,finished,inactive',
        ]);

        $flashSale->update($validated);

        return redirect()->route('admin.flash-sales.index')->with('success', 'Cập nhật Flash Sale thành công!');
    }
    // Xoá Flash Sale
    public function destroy(FlashSale $flashSale)
    {
        $flashSale->delete();
        return back()->with('success', 'Xoá Flash Sale thành công!');
    }
    // Hiển thị chi tiết Flash Sale
    public function show(FlashSale $flashSale)
    {
        $flashSale->load('flashSaleTimeSlots', 'products.variant.product');
        

        $variants = ProductVariant::with('product')
            ->whereHas('product', function ($query) {
                $query->where('type', '!=', 'simple'); // chỉ lấy product khác simple
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);


        return view('admin.flash_sales.show', compact('flashSale', 'variants'));
    }
    // Thêm khung giờ cho Flash Sale
    public function addTimeSlot(Request $request, FlashSale $flashSale)
    {
        $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'label' => 'nullable|string',
            'total_quantity_limit' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'date' => 'nullable|date',
        ]);

        $flashSale->flashSaleTimeSlots()->create([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'label' => $request->label,
            'total_quantity_limit' => $request->total_quantity_limit,
            'sort_order' => $request->sort_order ?? 0,
            'date' => $request->date,
            'status' => true,
        ]);

        return back()->with('success', 'Thêm khung giờ thành công!');
    }


    // Gán sản phẩm vào Flash Sale
    public function attachProduct(Request $request, FlashSale $flashSale)
    {
        
        $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'flash_price' => 'required|numeric|min:0',
            'quantity_limit' => 'required|integer|min:1',
            'flash_sale_time_slot_id' => 'nullable|exists:flash_sale_time_slots,id',

        ]);

        // 🔍 Kiểm tra nếu sản phẩm đã tồn tại trong chiến dịch
        $exists = $flashSale->products()
            ->where('product_variant_id', $request->product_variant_id)
            ->exists();

        if ($exists) {
            // ⚠️ Trả về với thông báo lỗi thân thiện
            return back()->with('error', 'Sản phẩm này đã có trong chiến dịch.');
        }

        // ✅ Nếu chưa tồn tại thì thêm mới
        $flashSale->products()->create([
            'product_variant_id' => $request->product_variant_id,
            'flash_price' => $request->flash_price,
            'quantity_limit' => $request->quantity_limit,
            'flash_sale_time_slot_id' => $request->flash_sale_time_slot_id, // <--- thêm dòng này
        ]);


        return back()->with('success', 'Thêm sản phẩm vào Flash Sale thành công!');
    }


    // Xoá sản phẩm khỏi Flash Sale
    public function detachProduct(FlashSale $flashSale, FlashSaleProduct $product)
    {
        if ($product->flash_sale_id !== $flashSale->id) {
            abort(403);
        }

        $product->delete();

        return back()->with('success', 'Xoá sản phẩm khỏi Flash Sale thành công!');
    }

    public function updateProduct(Request $request, FlashSale $flashSale, $flashProductId)
    {
        // Validate input
        $request->validate([
            'flash_price' => 'required|numeric|min:0',
            'quantity_limit' => 'required|integer|min:1',
            'flash_sale_time_slot_id' => 'nullable|exists:flash_sale_time_slots,id',
        ]);

        // Find the FlashSaleProduct record
        $flashProduct = $flashSale->products()->findOrFail($flashProductId);

        // Update the record
        $flashProduct->update([
            'flash_price' => $request->flash_price,
            'quantity_limit' => $request->quantity_limit,
            'flash_sale_time_slot_id' => $request->flash_sale_time_slot_id ?: null,
        ]);

        // Redirect back with success message
        return redirect()->back()->with('success', 'Cập nhật sản phẩm thành công!');
    }
}
