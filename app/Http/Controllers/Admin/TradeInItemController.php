<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTradeInItemRequest; // Import Form Request
use App\Models\ProductVariant;
use App\Models\StoreLocation;
use App\Models\TradeInItem;
use App\Models\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TradeInItemController extends Controller
{
    /**
     * Hiển thị trang danh sách các sản phẩm cũ & mở hộp.
     */
    public function index()
    {
        // Logic để lấy và hiển thị danh sách sản phẩm cũ
        // Ví dụ: $items = TradeInItem::with('productVariant')->latest()->paginate(15);
        // return view('admin.trade_in_items.index', compact('items'));
        
        // Tạm thời trả về view trống
        return "Đây là trang danh sách sản phẩm cũ (index).";
    }

    /**
     * Hiển thị form để thêm mới một sản phẩm cũ.
     */
    public function create()
    {
        // Lấy danh sách các sản phẩm mới (product_variants) để hiển thị trong dropdown
        $productVariants = ProductVariant::where('status', 'active')->get();
        
        // Lấy danh sách các cửa hàng/kho
        $storeLocations = StoreLocation::where('is_active', true)->get();

        return view('admin.trade_in_items.create', compact('productVariants', 'storeLocations'));
    }

    /**
     * Lưu một sản phẩm cũ mới vào database.
     *
     * @param  \App\Http\Requests\StoreTradeInItemRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreTradeInItemRequest $request)
    {
        // Lấy dữ liệu đã được validate từ Form Request
        $validatedData = $request->validated();

        // Bắt đầu một transaction để đảm bảo toàn vẹn dữ liệu
        // Nếu có lỗi xảy ra ở bất kỳ bước nào, tất cả sẽ được rollback
        DB::beginTransaction();

        try {
            // 1. Tạo bản ghi trong bảng `trade_in_items`
            $tradeInItem = TradeInItem::create([
                'product_variant_id' => $validatedData['product_variant_id'],
                'store_location_id' => $validatedData['store_location_id'],
                'type' => $validatedData['type'],
                'sku' => $validatedData['sku'] ?? $this->generateSku($validatedData['product_variant_id']),
                'condition_grade' => $validatedData['condition_grade'],
                'condition_description' => $validatedData['condition_description'],
                'selling_price' => $validatedData['selling_price'],
                'imei_or_serial' => $validatedData['imei_or_serial'],
                'status' => $validatedData['status'],
            ]);

            // 2. Xử lý upload hình ảnh (nếu có)
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $imageFile) {
                    // Lưu file vào storage (ví dụ: storage/app/public/trade-in-images)
                    $path = $imageFile->store('trade-in-images', 'public');

                    // Tạo bản ghi trong bảng `uploaded_files`
                    $uploadedFile = UploadedFile::create([
                        'path' => $path,
                        'filename' => $imageFile->hashName(),
                        'original_name' => $imageFile->getClientOriginalName(),
                        'mime_type' => $imageFile->getMimeType(),
                        'size' => $imageFile->getSize(),
                        'disk' => 'public',
                        'attachable_id' => $tradeInItem->id, // Gán id của sản phẩm cũ
                        'attachable_type' => TradeInItem::class, // Gán model class
                        // 'user_id' => auth()->id(), // Nếu cần lưu người upload
                    ]);

                    // 3. Tạo mối quan hệ trong bảng `trade_in_item_images`
                    // Lưu ý: Dựa trên CSDL của bạn, có vẻ bạn dùng bảng `uploaded_files` với attachable polymorphic.
                    // Nếu bạn có bảng trung gian `trade_in_item_images(trade_in_item_id, uploaded_file_id)`,
                    // bạn sẽ dùng lệnh sau:
                    // $tradeInItem->images()->attach($uploadedFile->id);
                }
            }

            // Nếu mọi thứ thành công, commit transaction
            DB::commit();

            return redirect()->route('admin.trade-in-items.index')
                         ->with('success', 'Thêm sản phẩm cũ thành công!');

        } catch (\Exception $e) {
            // Nếu có lỗi, rollback lại tất cả các thay đổi
            DB::rollBack();
            
            // Ghi log lỗi để debug
            Log::error('Lỗi khi thêm sản phẩm cũ: ' . $e->getMessage());

            return back()->withInput()->with('error', 'Đã có lỗi xảy ra. Vui lòng thử lại.');
        }
    }

    /**
     * Helper function để tự động tạo SKU nếu người dùng không nhập.
     */
    private function generateSku($variantId)
    {
        $prefix = 'USED';
        // Logic tạo SKU, ví dụ: USED-14PROMAX-12345
        return $prefix . '-' . $variantId . '-' . strtoupper(uniqid());
    }
    
    // Các phương thức khác như edit, update, destroy sẽ được thêm ở đây...
}
