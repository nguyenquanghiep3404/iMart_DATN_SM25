<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\FlashSale;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\FlashSaleProduct;
use App\Http\Controllers\Controller;

class FlashSaleController extends Controller
{
    public function index()
    {
        // Lấy tất cả Flash Sales
        $flashSales = FlashSale::with('flashSaleTimeSlots', 'flashSaleProducts')->get();

        // Cập nhật trạng thái của từng Flash Sale trước khi hiển thị
        // Điều này đảm bảo trạng thái trên trang danh sách là chính xác
        foreach ($flashSales as $flashSale) {
            $flashSale->syncStatusBasedOnTime();
        }

        return view('admin.flash_sales.index', compact('flashSales'));
    }

    // Hiển thị form tạo Flash Sale mới
    public function create()
    {
        return view('admin.flash_sales.create');
    }
    // public function store(Request $request)
    // {
    //     $messages = [
    //         'name.required' => 'Vui lòng nhập tên chiến dịch.',
    //         'slug.required' => 'Vui lòng nhập slug.',
    //         'slug.unique' => 'Slug đã tồn tại. Vui lòng chọn slug khác.',
    //         'start_time.required' => 'Vui lòng chọn thời gian bắt đầu.',
    //         'start_time.date' => 'Thời gian bắt đầu không hợp lệ.',
    //         'start_time.after_or_equal' => 'Thời gian bắt đầu phải từ hôm nay trở đi.',
    //         'end_time.required' => 'Vui lòng chọn thời gian kết thúc.',
    //         'end_time.date' => 'Thời gian kết thúc không hợp lệ.',
    //         'end_time.after_or_equal' => 'Thời gian kết thúc phải sau hoặc bằng thời gian bắt đầu.', // Cập nhật message
    //         'time_slots.required' => 'Vui lòng thêm ít nhất một khung giờ.', // Đảm bảo có message này
    //         'time_slots.array' => 'Khung giờ không hợp lệ.',
    //         'time_slots.*.start_time.required' => 'Vui lòng nhập thời gian bắt đầu cho từng khung giờ.',
    //         'time_slots.*.start_time.date_format' => 'Thời gian bắt đầu phải đúng định dạng HH:MM.',
    //         'time_slots.*.end_time.required' => 'Vui lòng nhập thời gian kết thúc cho từng khung giờ.',
    //         'time_slots.*.end_time.date_format' => 'Thời gian kết thúc phải đúng định dạng HH:MM.',
    //         'time_slots.*.end_time.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu của cùng khung giờ.',
    //     ];

    //     $validated = $request->validate([
    //         'name' => 'required|string',
    //         'slug' => 'required|unique:flash_sales,slug',
    //         'start_time' => 'required|date|after_or_equal:today',
    //         'end_time' => 'required|date|after_or_equal:start_time', // THAY ĐỔI Ở ĐÂY
    //         'banner_image_url' => 'nullable|string',
    //         'time_slots' => 'required|array',
    //         'time_slots.*.start_time' => 'required|date_format:H:i',
    //         'time_slots.*.end_time' => [
    //             'required',
    //             'date_format:H:i',
    //             function ($attribute, $value, $fail) use ($request) {
    //                 if (preg_match('/time_slots\.(\d+)\.end_time/', $attribute, $matches)) {
    //                     $index = $matches[1];
    //                     $start = $request->input("time_slots.$index.start_time");
    //                     // Kiểm tra thời gian kết thúc phải sau thời gian bắt đầu của cùng khung giờ
    //                     if ($start && $value && strtotime($value) <= strtotime($start)) {
    //                         $fail('Thời gian kết thúc phải sau thời gian bắt đầu của cùng khung giờ.');
    //                     }
    //                 }
    //             },
    //         ],
    //     ], $messages);

    //     // Convert start_time và end_time thành full ngày
    //     $startDate = Carbon::parse($validated['start_time'])->startOfDay();   // 00:00:00
    //     $endDate   = Carbon::parse($validated['end_time'])->endOfDay();      // 23:59:59

    //     // Tạo Flash Sale
    //     $flashSale = FlashSale::create([
    //         'name' => $validated['name'],
    //         'slug' => $validated['slug'],
    //         'start_time' => $startDate,
    //         'end_time' => $endDate,
    //         'banner_image_url' => $validated['banner_image_url'] ?? null,
    //         'status' => 'scheduled',
    //     ]);

    //     // Lưu các khung giờ
    //     if (isset($validated['time_slots'])) {
    //         foreach ($validated['time_slots'] as $slot) {
    //             $flashSale->flashSaleTimeSlots()->create([
    //                 'start_time' => $slot['start_time'],
    //                 'end_time' => $slot['end_time'],
    //             ]);
    //         }
    //     }

    //     return redirect()->route('admin.flash-sales.index')
    //         ->with('success', 'Tạo Flash Sale thành công!');
    // }

     public function store(Request $request)
    {
        $messages = [
            'name.required' => 'Vui lòng nhập tên chiến dịch.',
            'slug.required' => 'Vui lòng nhập slug.',
            'slug.unique' => 'Slug đã tồn tại. Vui lòng chọn slug khác.',
            'start_time.required' => 'Vui lòng chọn thời gian bắt đầu.',
            'start_time.date' => 'Thời gian bắt đầu không hợp lệ.',
            'start_time.after_or_equal' => 'Thời gian bắt đầu phải từ hôm nay trở đi.',
            'end_time.required' => 'Vui lòng chọn thời gian kết thúc.',
            'end_time.date' => 'Thời gian kết thúc không hợp lệ.',
            'end_time.after_or_equal' => 'Thời gian kết thúc phải sau hoặc bằng thời gian bắt đầu.',
            'time_slots.required' => 'Vui lòng thêm ít nhất một khung giờ.',
            'time_slots.array' => 'Khung giờ không hợp lệ.',
            'time_slots.*.start_time.required' => 'Vui lòng nhập thời gian bắt đầu cho từng khung giờ.',
            'time_slots.*.start_time.date_format' => 'Thời gian bắt đầu phải đúng định dạng HH:MM.',
            'time_slots.*.end_time.required' => 'Vui lòng nhập thời gian kết thúc cho từng khung giờ.',
            'time_slots.*.end_time.date_format' => 'Thời gian kết thúc phải đúng định dạng HH:MM.',
            'time_slots.*.end_time.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu của cùng khung giờ.',
            'time_slots.*.overlap' => 'Khung giờ :index bị trùng lặp với một khung giờ khác.',
        ];

        $validated = $request->validate([
            'name' => 'required|string',
            'slug' => 'required|unique:flash_sales,slug',
            'start_time' => 'required|date|after_or_equal:today',
            'end_time' => 'required|date|after_or_equal:start_time',
            'banner_image_url' => 'nullable|string',
            'time_slots' => 'required|array',
            'time_slots.*.start_time' => 'required|date_format:H:i',
            'time_slots.*.end_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) use ($request) {
                    if (preg_match('/time_slots\.(\d+)\.end_time/', $attribute, $matches)) {
                        $index = $matches[1];
                        $start = $request->input("time_slots.$index.start_time");
                        if ($start && $value && strtotime($value) <= strtotime($start)) {
                            $fail('Thời gian kết thúc phải sau thời gian bắt đầu của cùng khung giờ.');
                        }
                    }
                },
            ],
            'time_slots.*' => [
                function ($attribute, $value, $fail) use ($request) {
                    if (preg_match('/time_slots\.(\d+)/', $attribute, $matches)) {
                        $index = $matches[1];
                        $currentStart = $request->input("time_slots.$index.start_time");
                        $currentEnd = $request->input("time_slots.$index.end_time");
                        
                        // Kiểm tra overlap với các khung giờ khác trong request
                        foreach ($request->time_slots as $i => $slot) {
                            if ($i == $index) continue; // Bỏ qua chính khung giờ đang kiểm tra
                            $otherStart = $slot['start_time'];
                            $otherEnd = $slot['end_time'];
                            
                            if ($currentStart && $currentEnd && $otherStart && $otherEnd) {
                                $currentStartTime = strtotime($currentStart);
                                $currentEndTime = strtotime($currentEnd);
                                $otherStartTime = strtotime($otherStart);
                                $otherEndTime = strtotime($otherEnd);
                                
                                // Kiểm tra overlap: start1 <= end2 && end1 >= start2
                                if ($currentStartTime <= $otherEndTime && $currentEndTime >= $otherStartTime) {
                                    $fail("Khung giờ $index ($currentStart - $currentEnd) bị trùng lặp với khung giờ $i ($otherStart - $otherEnd).");
                                }
                            }
                        }
                    }
                },
            ],
        ], $messages);

        // Convert start_time và end_time thành full ngày
        $startDate = Carbon::parse($validated['start_time'])->startOfDay();
        $endDate = Carbon::parse($validated['end_time'])->endOfDay();

        // Tạo Flash Sale
        $flashSale = FlashSale::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'start_time' => $startDate,
            'end_time' => $endDate,
            'banner_image_url' => $validated['banner_image_url'] ?? null,
            'status' => 'scheduled',
        ]);

        // Lưu các khung giờ
        if (isset($validated['time_slots'])) {
            foreach ($validated['time_slots'] as $slot) {
                $flashSale->flashSaleTimeSlots()->create([
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                ]);
            }
        }

        return redirect()->route('admin.flash-sales.index')
            ->with('success', 'Tạo Flash Sale thành công!');
    }


    // Hiển thị form chỉnh sửa Flash Sale
    public function edit(FlashSale $flashSale)
    {
        // Cập nhật trạng thái của flash sale trước khi hiển thị form
        // Điều này đảm bảo trạng thái trong database là chính xác tại thời điểm truy cập
        $flashSale->syncStatusBasedOnTime(); // Dòng này đã được thêm vào

        $variants = ProductVariant::with('product')->get();
        $flashSaleProducts = $flashSale->products()->with('variant.product')->get();

        return view('admin.flash_sales.edit', compact('flashSale', 'variants', 'flashSaleProducts'));
    }
    // public function update(Request $request, FlashSale $flashSale)
    // {
    //     $messages = [
    //         'name.required' => 'Vui lòng nhập tên chiến dịch.',
    //         'slug.required' => 'Vui lòng nhập slug.',
    //         'slug.unique' => 'Slug đã tồn tại. Vui lòng chọn slug khác.',
    //         'start_time.required' => 'Vui lòng chọn thời gian bắt đầu.',
    //         'start_time.date' => 'Thời gian bắt đầu không hợp lệ.',
    //         'end_time.required' => 'Vui lòng chọn thời gian kết thúc.',
    //         'end_time.date' => 'Thời gian kết thúc không hợp lệ.',
    //         'end_time.after_or_equal' => 'Thời gian kết thúc phải sau hoặc bằng thời gian bắt đầu.', // Cập nhật message
    //         'status.in' => 'Trạng thái không hợp lệ.',
    //         'time_slots.array' => 'Khung giờ không hợp lệ.',
    //         'time_slots.required' => 'Vui lòng thêm ít nhất một khung giờ.',
    //         'time_slots.*.start_time.required' => 'Vui lòng nhập thời gian bắt đầu cho từng khung giờ.',
    //         'time_slots.*.start_time.date_format' => 'Thời gian bắt đầu phải đúng định dạng HH:MM.',
    //         'time_slots.*.end_time.required' => 'Vui lòng nhập thời gian kết thúc cho từng khung giờ.',
    //         'time_slots.*.end_time.date_format' => 'Thời gian kết thúc phải đúng định dạng HH:MM.',
    //         'time_slots.*.end_time.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu của cùng khung giờ.',
    //         'start_time.custom_check' => 'Thời gian bắt đầu không thể là ngày trong quá khứ nếu nó bị thay đổi.',
    //     ];

    //     $validated = $request->validate([
    //         'name' => 'required|string',
    //         'slug' => 'required|unique:flash_sales,slug,' . $flashSale->id,
    //         'start_time' => [
    //             'required',
    //             'date',
    //             // Quy tắc tùy chỉnh để kiểm tra start_time cho hàm update
    //             function ($attribute, $value, $fail) use ($flashSale) {
    //                 // Chỉ kiểm tra khi thời gian bắt đầu bị thay đổi
    //                 if (Carbon::parse($value)->format('Y-m-d') !== $flashSale->start_time->format('Y-m-d')) {
    //                     if (Carbon::parse($value)->startOfDay()->lt(Carbon::now()->startOfDay())) {
    //                         $fail('Thời gian bắt đầu không thể là ngày trong quá khứ.');
    //                     }
    //                 }
    //             },
    //         ],
    //         'end_time' => 'required|date|after_or_equal:start_time',
    //         'banner_image_url' => 'nullable|string',
    //         'status' => ['in:active,inactive,scheduled,finished'], // THAY ĐỔI Ở ĐÂY
    //         'time_slots' => 'required|array',
    //         'time_slots.*.id' => 'nullable|integer|exists:flash_sale_time_slots,id',
    //         'time_slots.*.start_time' => 'required|date_format:H:i',
    //         'time_slots.*.end_time' => [
    //             'required',
    //             'date_format:H:i',
    //             function ($attribute, $value, $fail) use ($request) {
    //                 if (preg_match('/time_slots\.(\d+)\.end_time/', $attribute, $matches)) {
    //                     $index = $matches[1];
    //                     $start = $request->input("time_slots.$index.start_time");
    //                     // Kiểm tra thời gian kết thúc phải sau thời gian bắt đầu của cùng khung giờ
    //                     if ($start && $value && strtotime($value) <= strtotime($start)) {
    //                         $fail('Thời gian kết thúc phải sau thời gian bắt đầu của cùng khung giờ.');
    //                     }
    //                 }
    //             },
    //         ],
    //     ], $messages);

    //     // Convert start_time và end_time thành full ngày
    //     $startDate = Carbon::parse($validated['start_time'])->startOfDay();
    //     $endDate   = Carbon::parse($validated['end_time'])->endOfDay();

    //     // Cập nhật Flash Sale
    //     $flashSale->update([
    //         'name' => $validated['name'],
    //         'slug' => $validated['slug'],
    //         'start_time' => $startDate,
    //         'end_time' => $endDate,
    //         'banner_image_url' => $validated['banner_image_url'] ?? null,
    //         'status' => $validated['status'],
    //     ]);

    //     // Xử lý time_slots
    //     $inputSlots = $validated['time_slots'] ?? [];
    //     $existingSlotIds = $flashSale->flashSaleTimeSlots()->pluck('id')->toArray();
    //     $requestSlotIds = collect($inputSlots)->pluck('id')->filter()->toArray();

    //     // Xóa các slot không còn trong request
    //     $toDelete = array_diff($existingSlotIds, $requestSlotIds);
    //     $cannotDelete = [];
    //     if (!empty($toDelete)) {
    //         foreach ($toDelete as $slotId) {
    //             $slot = $flashSale->flashSaleTimeSlots()->find($slotId);
    //             if ($slot && $slot->products()->count() > 0) {
    //                 $cannotDelete[] = $slot;
    //             } else {
    //                 $slot?->delete();
    //             }
    //         }
    //     }

    //     // Thêm mới hoặc cập nhật
    //     foreach ($inputSlots as $slot) {
    //         if (!empty($slot['id'])) {
    //             // Update
    //             $flashSale->flashSaleTimeSlots()->where('id', $slot['id'])->update([
    //                 'start_time' => $slot['start_time'],
    //                 'end_time' => $slot['end_time'],
    //             ]);
    //         } else {
    //             // Create
    //             $flashSale->flashSaleTimeSlots()->create([
    //                 'start_time' => $slot['start_time'],
    //                 'end_time' => $slot['end_time'],
    //             ]);
    //         }
    //     }

    //     if (!empty($cannotDelete)) {
    //         $slotTimes = collect($cannotDelete)->map(function ($slot) {
    //             return ($slot->start_time ? date('H:i', strtotime($slot->start_time)) : '') . ' - ' . ($slot->end_time ? date('H:i', strtotime($slot->end_time)) : '');
    //         })->implode(', ');
    //         return redirect()->back()
    //             ->withInput()
    //             ->withErrors(['time_slots' => 'Không thể xóa các khung giờ sau vì đã có sản phẩm: ' . $slotTimes]);
    //     }

    //     return redirect()->route('admin.flash-sales.index')->with('success', 'Cập nhật Flash Sale thành công!');
    // }

    public function update(Request $request, FlashSale $flashSale)
    {
        $messages = [
            'name.required' => 'Vui lòng nhập tên chiến dịch.',
            'slug.required' => 'Vui lòng nhập slug.',
            'slug.unique' => 'Slug đã tồn tại. Vui lòng chọn slug khác.',
            'start_time.required' => 'Vui lòng chọn thời gian bắt đầu.',
            'start_time.date' => 'Thời gian bắt đầu không hợp lệ.',
            'end_time.required' => 'Vui lòng chọn thời gian kết thúc.',
            'end_time.date' => 'Thời gian kết thúc không hợp lệ.',
            'end_time.after_or_equal' => 'Thời gian kết thúc phải sau hoặc bằng thời gian bắt đầu.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'time_slots.array' => 'Khung giờ không hợp lệ.',
            'time_slots.required' => 'Vui lòng thêm ít nhất một khung giờ.',
            'time_slots.*.start_time.required' => 'Vui lòng nhập thời gian bắt đầu cho từng khung giờ.',
            'time_slots.*.start_time.date_format' => 'Thời gian bắt đầu phải đúng định dạng HH:MM.',
            'time_slots.*.end_time.required' => 'Vui lòng nhập thời gian kết thúc cho từng khung giờ.',
            'time_slots.*.end_time.date_format' => 'Thời gian kết thúc phải đúng định dạng HH:MM.',
            'time_slots.*.end_time.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu của cùng khung giờ.',
            'time_slots.*.overlap' => 'Khung giờ :index bị trùng lặp với một khung giờ khác.',
            'start_time.custom_check' => 'Thời gian bắt đầu không thể là ngày trong quá khứ nếu nó bị thay đổi.',
        ];

        $validated = $request->validate([
            'name' => 'required|string',
            'slug' => 'required|unique:flash_sales,slug,' . $flashSale->id,
            'start_time' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($flashSale) {
                    if (Carbon::parse($value)->format('Y-m-d') !== $flashSale->start_time->format('Y-m-d')) {
                        if (Carbon::parse($value)->startOfDay()->lt(Carbon::now()->startOfDay())) {
                            $fail('Thời gian bắt đầu không thể là ngày trong quá khứ.');
                        }
                    }
                },
            ],
            'end_time' => 'required|date|after_or_equal:start_time',
            'banner_image_url' => 'nullable|string',
            'status' => 'in:active,inactive,scheduled,finished',
            'time_slots' => 'required|array',
            'time_slots.*.id' => 'nullable|integer|exists:flash_sale_time_slots,id',
            'time_slots.*.start_time' => 'required|date_format:H:i',
            'time_slots.*.end_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) use ($request) {
                    if (preg_match('/time_slots\.(\d+)\.end_time/', $attribute, $matches)) {
                        $index = $matches[1];
                        $start = $request->input("time_slots.$index.start_time");
                        if ($start && $value && strtotime($value) <= strtotime($start)) {
                            $fail('Thời gian kết thúc phải sau thời gian bắt đầu của cùng khung giờ.');
                        }
                    }
                },
            ],
            'time_slots.*' => [
                function ($attribute, $value, $fail) use ($request, $flashSale) {
                    if (preg_match('/time_slots\.(\d+)/', $attribute, $matches)) {
                        $index = $matches[1];
                        $currentStart = $request->input("time_slots.$index.start_time");
                        $currentEnd = $request->input("time_slots.$index.end_time");
                        $currentId = $request->input("time_slots.$index.id");
                        
                        // Kiểm tra overlap với các khung giờ khác trong request
                        foreach ($request->time_slots as $i => $slot) {
                            if ($i == $index) continue;
                            $otherStart = $slot['start_time'];
                            $otherEnd = $slot['end_time'];
                            
                            if ($currentStart && $currentEnd && $otherStart && $otherEnd) {
                                $currentStartTime = strtotime($currentStart);
                                $currentEndTime = strtotime($currentEnd);
                                $otherStartTime = strtotime($otherStart);
                                $otherEndTime = strtotime($otherEnd);
                                
                                if ($currentStartTime <= $otherEndTime && $currentEndTime >= $otherStartTime) {
                                    $fail("Khung giờ $index ($currentStart - $currentEnd) bị trùng lặp với khung giờ $i ($otherStart - $otherEnd).");
                                }
                            }
                        }

                        // Kiểm tra overlap với các khung giờ hiện có trong database
                        $existingSlots = $flashSale->flashSaleTimeSlots()->where('id', '!=', $currentId)->get();
                        foreach ($existingSlots as $existingSlot) {
                            $existingStart = strtotime($existingSlot->start_time);
                            $existingEnd = strtotime($existingSlot->end_time);
                            if ($currentStart && $currentEnd && $existingStart && $existingEnd) {
                                $currentStartTime = strtotime($currentStart);
                                $currentEndTime = strtotime($currentEnd);
                                if ($currentStartTime <= $existingEnd && $currentEndTime >= $existingStart) {
                                    $fail("Khung giờ $index ($currentStart - $currentEnd) bị trùng lặp với khung giờ hiện có (" . date('H:i', $existingStart) . " - " . date('H:i', $existingEnd) . ").");
                                }
                            }
                        }
                    }
                },
            ],
        ], $messages);

        // Convert start_time và end_time thành full ngày
        $startDate = Carbon::parse($validated['start_time'])->startOfDay();
        $endDate = Carbon::parse($validated['end_time'])->endOfDay();

        // Cập nhật Flash Sale
        $flashSale->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'start_time' => $startDate,
            'end_time' => $endDate,
            'banner_image_url' => $validated['banner_image_url'] ?? null,
            'status' => $validated['status'],
        ]);

        // Xử lý time_slots
        $inputSlots = $validated['time_slots'] ?? [];
        $existingSlotIds = $flashSale->flashSaleTimeSlots()->pluck('id')->toArray();
        $requestSlotIds = collect($inputSlots)->pluck('id')->filter()->toArray();

        // Xóa các slot không còn trong request
        $toDelete = array_diff($existingSlotIds, $requestSlotIds);
        $cannotDelete = [];
        if (!empty($toDelete)) {
            foreach ($toDelete as $slotId) {
                $slot = $flashSale->flashSaleTimeSlots()->find($slotId);
                if ($slot && $slot->products()->count() > 0) {
                    $cannotDelete[] = $slot;
                } else {
                    $slot?->delete();
                }
            }
        }

        // Thêm mới hoặc cập nhật
        foreach ($inputSlots as $slot) {
            if (!empty($slot['id'])) {
                $flashSale->flashSaleTimeSlots()->where('id', $slot['id'])->update([
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                ]);
            } else {
                $flashSale->flashSaleTimeSlots()->create([
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                ]);
            }
        }

        if (!empty($cannotDelete)) {
            $slotTimes = collect($cannotDelete)->map(function ($slot) {
                return ($slot->start_time ? date('H:i', strtotime($slot->start_time)) : '') . ' - ' . ($slot->end_time ? date('H:i', strtotime($slot->end_time)) : '');
            })->implode(', ');
            return redirect()->back()
                ->withInput()
                ->withErrors(['time_slots' => 'Không thể xóa các khung giờ sau vì đã có sản phẩm: ' . $slotTimes]);
        }

        return redirect()->route('admin.flash-sales.index')->with('success', 'Cập nhật Flash Sale thành công!');
    }
    // Xoá Flash Sale
    public function destroy(FlashSale $flashSale)
    {
        $flashSale->delete();
        return back()->with('success', 'Xoá Flash Sale thành công!');
    }

    // Thêm khung giờ cho Flash Sale
    // public function addTimeSlot(Request $request, FlashSale $flashSale)
    // {
    //     $request->validate([
    //         'start_time' => 'required|date_format:H:i',
    //         'end_time' => 'required|date_format:H:i|after:start_time',
    //         'label' => 'nullable|string',
    //         'total_quantity_limit' => 'nullable|integer|min:0',
    //         'sort_order' => 'nullable|integer|min:0',
    //         'date' => 'nullable|date',
    //     ]);

    //     $flashSale->flashSaleTimeSlots()->create([
    //         'start_time' => $request->start_time,
    //         'end_time' => $request->end_time,
    //         'label' => $request->label,
    //         'total_quantity_limit' => $request->total_quantity_limit,
    //         'sort_order' => $request->sort_order ?? 0,
    //         'date' => $request->date,
    //         'status' => true,
    //     ]);

    //     return back()->with('success', 'Thêm khung giờ thành công!');
    // }

     public function addTimeSlot(Request $request, FlashSale $flashSale)
    {
        $messages = [
            'start_time.required' => 'Vui lòng nhập thời gian bắt đầu.',
            'start_time.date_format' => 'Thời gian bắt đầu phải đúng định dạng HH:MM.',
            'end_time.required' => 'Vui lòng nhập thời gian kết thúc.',
            'end_time.date_format' => 'Thời gian kết thúc phải đúng định dạng HH:MM.',
            'end_time.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
            'overlap' => 'Khung giờ mới ($start_time - $end_time) bị trùng lặp với khung giờ hiện có.',
        ];

        $validated = $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'label' => 'nullable|string',
            'total_quantity_limit' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'date' => 'nullable|date',
            '' => [
                function ($attribute, $value, $fail) use ($request, $flashSale) {
                    $currentStart = $request->start_time;
                    $currentEnd = $request->end_time;
                    
                    // Kiểm tra overlap với các khung giờ hiện có
                    $existingSlots = $flashSale->flashSaleTimeSlots()->get();
                    foreach ($existingSlots as $existingSlot) {
                        $existingStart = strtotime($existingSlot->start_time);
                        $existingEnd = strtotime($existingSlot->end_time);
                        if ($currentStart && $currentEnd && $existingStart && $existingEnd) {
                            $currentStartTime = strtotime($currentStart);
                            $currentEndTime = strtotime($currentEnd);
                            if ($currentStartTime <= $existingEnd && $currentEndTime >= $existingStart) {
                                $fail("Khung giờ mới ($currentStart - $currentEnd) bị trùng lặp với khung giờ hiện có (" . date('H:i', $existingStart) . " - " . date('H:i', $existingEnd) . ").");
                            }
                        }
                    }
                },
            ],
        ], $messages);

        $flashSale->flashSaleTimeSlots()->create([
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'label' => $validated['label'],
            'total_quantity_limit' => $validated['total_quantity_limit'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'date' => $validated['date'],
            'status' => true,
        ]);

        return back()->with('success', 'Thêm khung giờ thành công!');
    }
    // Hiển thị chi tiết Flash Sale
    public function show(Request $request, FlashSale $flashSale)
    {
        $flashSale->load('flashSaleTimeSlots', 'products.variant.product');

        $variants = ProductVariant::with('product')
            ->whereHas('product', function ($query) {
                $query->where('type', '!=', 'simple');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->through(function ($variant) {
                $variant->available_stock = $variant->available_stock;
                return $variant;
            });

        // Lọc sản phẩm theo khung giờ nếu có tham số time_slot_id
        $timeSlotId = $request->query('time_slot_id');
        if ($timeSlotId && $timeSlotId !== 'all') {
            $flashSale->products = $flashSale->products()->where('flash_sale_time_slot_id', $timeSlotId)->get();
        } elseif ($timeSlotId === 'all' || !$timeSlotId) {
            $flashSale->products = $flashSale->products()->get();
        }

        return view('admin.flash_sales.show', compact('flashSale', 'variants', 'timeSlotId'));
    }

    // Gán sản phẩm vào Flash Sale
    // Gán sản phẩm vào Flash Sale
    public function attachProduct(Request $request, FlashSale $flashSale)
    {
        // Tìm biến thể sản phẩm trước khi validate
        $variant = ProductVariant::find($request->product_variant_id);

        // Kiểm tra nếu không tìm thấy biến thể, trả về lỗi ngay lập tức
        if (!$variant) {
            return back()->with('error', 'Sản phẩm không tồn tại.');
        }

        $request->validate(
            [
                'product_variant_id' => 'required|exists:product_variants,id',
                'flash_price' => [
                    'required',
                    'numeric',
                    'min:0',
                    // Thêm quy tắc xác thực tùy chỉnh cho giá flash
                    function ($attribute, $value, $fail) use ($variant) {
                        if ($value >= $variant->price) {
                            $fail('Giá Flash (' . number_format($value) . 'đ) phải nhỏ hơn giá gốc (' . number_format($variant->price) . 'đ).');
                        }
                    },
                ],
                'quantity_limit' => [
                    'required',
                    'integer',
                    'min:1',
                    // Thêm quy tắc xác thực tùy chỉnh cho số lượng
                    function ($attribute, $value, $fail) use ($variant) {
                        if ($value > $variant->available_stock) {
                            $fail('Số lượng giới hạn (' . number_format($value) . ') không được lớn hơn tồn kho hiện tại (' . number_format($variant->available_stock) . ').');
                        }
                    },
                ],
                'flash_sale_time_slot_id' => 'nullable|exists:flash_sale_time_slots,id',
            ],
            [
                'product_variant_id.required' => 'Mã biến thể sản phẩm là bắt buộc.',
                'product_variant_id.exists' => 'Mã biến thể sản phẩm không tồn tại.',
                'flash_price.required' => 'Giá Flash là bắt buộc.',
                'flash_price.numeric' => 'Giá Flash phải là một số.',
                'flash_price.min' => 'Giá Flash phải lớn hơn hoặc bằng 0.',
                'quantity_limit.required' => 'Số lượng giới hạn là bắt buộc.',
                'quantity_limit.integer' => 'Số lượng giới hạn phải là số nguyên.',
                'quantity_limit.min' => 'Số lượng giới hạn phải lớn hơn hoặc bằng 1.',
                'flash_sale_time_slot_id.exists' => 'Khung giờ không hợp lệ.',
            ]
        );

        // 🔍 Kiểm tra nếu sản phẩm đã tồn tại trong cùng một khung giờ
        $query = $flashSale->products()->where('product_variant_id', $request->product_variant_id);

        // Thêm điều kiện kiểm tra khung giờ
        if ($request->has('flash_sale_time_slot_id')) {
            $query->where('flash_sale_time_slot_id', $request->flash_sale_time_slot_id);
        } else {
            // Nếu không có khung giờ (null), cần kiểm tra trường hợp sản phẩm đã được thêm vào toàn bộ chiến dịch
            $query->whereNull('flash_sale_time_slot_id');
        }

        $exists = $query->exists();

        if ($exists) {
            // ⚠️ Trả về với thông báo lỗi thân thiện
            return back()->with('error', 'Sản phẩm này đã có trong chiến dịch (hoặc khung giờ này).');
        }

        // ✅ Nếu chưa tồn tại thì thêm mới
        $flashSale->products()->create([
            'product_variant_id' => $request->product_variant_id,
            'flash_price' => $request->flash_price,
            'quantity_limit' => $request->quantity_limit,
            'flash_sale_time_slot_id' => $request->flash_sale_time_slot_id,
        ]);

        return back()->with('success', 'Thêm sản phẩm vào Flash Sale thành công!');
    }


    public function updateProduct(Request $request, FlashSale $flashSale, $flashProductId)
    {
        // Tìm bản ghi sản phẩm flash sale hiện tại và biến thể sản phẩm liên quan
        $flashProduct = $flashSale->products()->with('variant')->findOrFail($flashProductId);
        $variant = $flashProduct->variant;

        // Validate input với thông báo tiếng Việt và các quy tắc tùy chỉnh
        $request->validate(
            [
                'flash_price' => [
                    'required',
                    'numeric',
                    'min:0',
                    function ($attribute, $value, $fail) use ($variant) {
                        if ($value >= $variant->price) {
                            $fail('Giá Flash (' . number_format($value) . '₫) phải nhỏ hơn giá gốc (' . number_format($variant->price) . '₫).');
                        }
                    },
                ],
                'quantity_limit' => [
                    'required',
                    'integer',
                    'min:1',
                    function ($attribute, $value, $fail) use ($variant, $flashProduct) {
                        // Tính tồn kho còn lại sau khi trừ đi số lượng đã bán
                        $availableStock = $variant->available_stock + $flashProduct->quantity_sold;

                        if ($value > $availableStock) {
                            $fail('Số lượng giới hạn (' . number_format($value) . ') không được lớn hơn tồn kho hiện tại (' . number_format($availableStock) . ').');
                        }
                    },
                ],
                'flash_sale_time_slot_id' => 'nullable|exists:flash_sale_time_slots,id',
            ],
            [
                'flash_price.required' => 'Giá Flash không được để trống.',
                'flash_price.numeric' => 'Giá Flash phải là một số.',
                'flash_price.min' => 'Giá Flash không thể là số âm.',
                'quantity_limit.required' => 'Số lượng giới hạn không được để trống.',
                'quantity_limit.integer' => 'Số lượng giới hạn phải là một số nguyên.',
                'quantity_limit.min' => 'Số lượng giới hạn phải lớn hơn hoặc bằng 1.',
                'flash_sale_time_slot_id.exists' => 'Khung giờ đã chọn không hợp lệ.',
            ]
        );

        // Cập nhật bản ghi
        $flashProduct->update([
            'flash_price' => $request->flash_price,
            'quantity_limit' => $request->quantity_limit,
            'flash_sale_time_slot_id' => $request->flash_sale_time_slot_id ?: null,
        ]);

        // Chuyển hướng về trang trước với thông báo thành công
        return redirect()->back()->with('success', 'Cập nhật sản phẩm thành công!');
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

    // Thống kê Flash Sale
    public function statistics(FlashSale $flashSale)
    {
        // Tải dữ liệu Flash Sale cùng với các sản phẩm, biến thể và thuộc tính liên quan
        $flashSale->load([
            'products.variant.product',
            'products.variant.attributeValues',
        ]);

        // Tạo mảng để lưu trữ dữ liệu thống kê
        $statistics = $flashSale->products->map(function ($flashSaleProduct) {
            $variant = $flashSaleProduct->variant;
            $product = $variant->product;

            // Lấy tên biến thể từ attributeValues
            $variantName = $variant->attributeValues->pluck('value')->filter()->join(', ');
            $productName = $variantName ? $product->name . ' (' . $variantName . ')' : $product->name;

            // Tính giá vốn trung bình từ các lô hàng còn tồn kho trong bảng inventory_lots
            $lots = \App\Models\InventoryLot::where('product_variant_id', $variant->id)
                ->where('quantity_on_hand', '>', 0)
                ->select('cost_price', 'quantity_on_hand')
                ->get();
            $totalQuantity = $lots->sum('quantity_on_hand');
            $totalCost = $lots->sum(function ($lot) {
                return $lot->cost_price * $lot->quantity_on_hand;
            });
            $costPrice = $totalQuantity > 0 ? $totalCost / $totalQuantity : ($variant->cost_price ?? 0); // Giá vốn trung bình

            // Tính toán các giá trị cần thiết
            $originalPrice = $variant->price; // Giá gốc
            $flashPrice = $flashSaleProduct->flash_price; // Giá sale
            $quantityLimit = $flashSaleProduct->quantity_limit; // Số lượng giới hạn FS
            $quantitySold = $flashSaleProduct->quantity_sold; // Đã bán (FS)
            $remainingStockFS = max(0, $quantityLimit - $quantitySold); // Tồn kho FS
            $totalStock = $variant->available_stock + $quantitySold; // Tổng tồn kho
            $totalStockAfterFS = $totalStock - $quantitySold; // Tổng tồn kho sau FS
            $revenue = $quantitySold * $flashPrice; // Doanh thu
            $grossProfitPerUnit = $flashPrice - $costPrice; // Lợi nhuận gộp trên mỗi sản phẩm

            return [
                'product_name' => $productName, // Tên sản phẩm kèm biến thể
                'original_price' => number_format($originalPrice) . 'đ', // Giá gốc
                'cost_price' => number_format($costPrice) . 'đ', // Giá nhập
                'flash_price' => number_format($flashPrice) . 'đ', // Giá sale
                'quantity_limit' => $quantityLimit, // SL giới hạn FS
                'quantity_sold' => $quantitySold, // Đã bán (FS)
                'remaining_stock_fs' => $remainingStockFS, // Tồn kho FS
                'total_stock' => $totalStock, // Tổng tồn kho
                'total_stock_after_fs' => $totalStockAfterFS, // Tổng tồn kho sau FS
                'revenue' => number_format($revenue) . 'đ', // Doanh thu (định dạng)
                'gross_profit_per_unit' => number_format($grossProfitPerUnit) . 'đ/sp', // Lợi nhuận gộp
                'revenue_value' => $revenue, // Giá trị doanh thu để sắp xếp và biểu đồ
                'gross_profit_total' => $grossProfitPerUnit * $quantitySold, // Tổng lợi nhuận gộp cho sản phẩm
                'chart_label' => $productName, // Nhãn cho biểu đồ
                'chart_revenue' => $revenue, // Doanh thu cho biểu đồ
            ];
        });

        // Tính toán các giá trị tổng
        $totalRevenue = $statistics->sum('revenue_value');
        $totalQuantitySold = $statistics->sum('quantity_sold');
        $totalGrossProfit = $statistics->sum('gross_profit_total');
        $totalQuantityLimit = $flashSale->products->sum('quantity_limit');

        // Tính tỷ lệ đã bán hết sản phẩm
        $soldPercentage = $totalQuantityLimit > 0 ? round(($totalQuantitySold / $totalQuantityLimit) * 100) : 0;

        // Tính tỷ suất lợi nhuận gộp
        $grossProfitMargin = $totalRevenue > 0 ? round(($totalGrossProfit / $totalRevenue) * 100) : 0;

        // Tìm chiến dịch Flash Sale trước đó
        $previousFlashSale = \App\Models\FlashSale::where('id', '<', $flashSale->id)
            ->orderBy('id', 'desc')
            ->first();

        // Tính tỷ lệ tăng/giảm so với chiến dịch trước đó (nếu có)
        $revenueChange = null;
        $quantitySoldChange = null;
        $grossProfitChange = null;

        if ($previousFlashSale) {
            $previousFlashSale->load(['products']);
            $previousStatistics = $previousFlashSale->products->map(function ($flashSaleProduct) {
                $variant = $flashSaleProduct->variant;
                $lots = \App\Models\InventoryLot::where('product_variant_id', $variant->id)
                    ->where('quantity_on_hand', '>', 0)
                    ->select('cost_price', 'quantity_on_hand')
                    ->get();
                $totalQuantity = $lots->sum('quantity_on_hand');
                $totalCost = $lots->sum(function ($lot) {
                    return $lot->cost_price * $lot->quantity_on_hand;
                });
                $costPrice = $totalQuantity > 0 ? $totalCost / $totalQuantity : ($variant->cost_price ?? 0);
                $flashPrice = $flashSaleProduct->flash_price;
                $quantitySold = $flashSaleProduct->quantity_sold;
                $revenue = $quantitySold * $flashPrice;
                $grossProfitPerUnit = $flashPrice - $costPrice;

                return [
                    'revenue_value' => $revenue,
                    'quantity_sold' => $quantitySold,
                    'gross_profit_total' => $grossProfitPerUnit * $quantitySold,
                ];
            });

            $previousTotalRevenue = $previousStatistics->sum('revenue_value');
            $previousTotalQuantitySold = $previousStatistics->sum('quantity_sold');
            $previousTotalGrossProfit = $previousStatistics->sum('gross_profit_total');

            // Tính tỷ lệ thay đổi (%)
            $revenueChange = $previousTotalRevenue > 0 ? round((($totalRevenue - $previousTotalRevenue) / $previousTotalRevenue) * 100) : 0;
            $quantitySoldChange = $previousTotalQuantitySold > 0 ? round((($totalQuantitySold - $previousTotalQuantitySold) / $previousTotalQuantitySold) * 100) : 0;
            $grossProfitChange = $previousTotalGrossProfit > 0 ? round((($totalGrossProfit - $previousTotalGrossProfit) / $previousTotalGrossProfit) * 100) : 0;
        }

        // Sắp xếp theo doanh thu giảm dần và lấy top 5
        $topFive = $statistics->sortByDesc('revenue_value')->take(5)->values();

        // Lấy các sản phẩm còn lại (loại bỏ top 5)
        $remaining = $statistics->sortByDesc('revenue_value')->slice(5)->values();

        return view('admin.flash_sales.statistics', compact(
            'flashSale',
            'topFive',
            'remaining',
            'totalRevenue',
            'totalQuantitySold',
            'totalGrossProfit',
            'totalQuantityLimit',
            'soldPercentage',
            'grossProfitMargin',
            'revenueChange',
            'quantitySoldChange',
            'grossProfitChange',
            'statistics'
        ));
    }
}
