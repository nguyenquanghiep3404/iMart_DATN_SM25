<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTradeInItemRequest;
use App\Http\Requests\UpdateTradeInItemRequest; 
use App\Models\ProductVariant;
use App\Models\StoreLocation;
use App\Models\TradeInItem;
use App\Models\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;

class TradeInItemController extends Controller
{
    /**
     * Hiển thị trang danh sách các sản phẩm cũ & mở hộp.
     */
    public function index(Request $request)
    {
        // Bắt đầu xây dựng câu truy vấn
        $query = TradeInItem::query();

        // Eager load các relationship để tránh vấn đề N+1 queries
        // Tải sẵn thông tin biến thể, sản phẩm gốc, và các thuộc tính của biến thể
        $query->with([
            'productVariant.product', // Tải sản phẩm gốc (để lấy tên)
            'productVariant.attributeValues.attribute', // Tải các giá trị thuộc tính và tên thuộc tính
            'storeLocation', 
            'images'
        ]);

        // 1. Áp dụng bộ lọc TÌM KIẾM
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('sku', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('imei_or_serial', 'LIKE', "%{$searchTerm}%")
                  // Tìm kiếm trong bảng liên quan (products)
                  ->orWhereHas('productVariant.product', function ($subQuery) use ($searchTerm) {
                      $subQuery->where('name', 'LIKE', "%{$searchTerm}%");
                  });
            });
        }

        // 2. Áp dụng bộ lọc TRẠNG THÁI
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // 3. Áp dụng bộ lọc TÌNH TRẠNG
        if ($request->filled('condition_grade')) {
            $query->where('condition_grade', $request->input('condition_grade'));
        }

        // Sắp xếp các sản phẩm mới nhất lên đầu
        $query->orderBy('created_at', 'desc');

        // Phân trang kết quả, mỗi trang 15 mục
        $items = $query->paginate(15)->withQueryString();

        // Trả về view 'index' cùng với biến $items
        return view('admin.trade_in_items.index', compact('items'));
    }


    /**
     * Hiển thị form để thêm mới một sản phẩm cũ.
     */
    public function create()
    {
        $products = Product::where('status', 'published')
            ->whereHas('variants', fn($q) => $q->where('status', 'active'))
            ->with(['variants' => fn($q) => $q->where('status', 'active')->with('attributeValues.attribute')])
            ->orderBy('name')
            ->get();

        $storeLocations = StoreLocation::where('is_active', true)->get();

        // --- LOGIC MỚI: LẤY DỮ LIỆU ẢNH CŨ KHI VALIDATION LỖI ---
        $old_images_data = [];
        $all_image_ids = array_unique(array_filter(array_merge(
            [old('primary_image_id')],
            old('image_ids', [])
        )));

        if (!empty($all_image_ids)) {
            $images = UploadedFile::whereIn('id', $all_image_ids)->get();
            $old_images_data = $images->keyBy('id')->map(fn ($image) => [
                'id' => $image->id,
                'url' => $image->url,
                'alt_text' => $image->alt_text
            ])->all();
        }
        // --- KẾT THÚC LOGIC MỚI ---

        return view('admin.trade_in_items.create', compact('products', 'storeLocations', 'old_images_data'));
    }

    public function store(StoreTradeInItemRequest $request)
    {
        $validatedData = $request->validated();
        DB::beginTransaction();
        try {
            $tradeInItem = TradeInItem::create([
                'product_variant_id'    => $validatedData['product_variant_id'],
                'store_location_id'     => $validatedData['store_location_id'],
                'type'                  => $validatedData['type'],
                'sku'                   => $validatedData['sku'] ?? $this->generateSku($validatedData['product_variant_id']),
                'condition_grade'       => $validatedData['condition_grade'],
                'condition_description' => $validatedData['condition_description'],
                'selling_price'         => $validatedData['selling_price'],
                'imei_or_serial'        => $validatedData['imei_or_serial'],
                'status'                => $validatedData['status'],
            ]);

            // --- LOGIC MỚI: ĐỒNG BỘ HÌNH ẢNH ---
            $this->syncImages(
                $tradeInItem,
                $validatedData['primary_image_id'],
                $validatedData['image_ids']
            );
            // --- KẾT THÚC LOGIC MỚI ---

            DB::commit();
            return redirect()->route('admin.trade-in-items.index')
                ->with('success', 'Thêm sản phẩm cũ thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi thêm sản phẩm cũ: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Đã có lỗi xảy ra. Vui lòng thử lại.');
        }
    }
    public function edit(TradeInItem $tradeInItem)
    {
        // Tải sẵn các mối quan hệ cần thiết
        $tradeInItem->load(['productVariant', 'storeLocation', 'images']);

        $products = Product::where('status', 'published')
            ->whereHas('variants', fn($q) => $q->where('status', 'active'))
            ->with(['variants' => fn($q) => $q->where('status', 'active')->with('attributeValues.attribute')])
            ->orderBy('name')
            ->get();

        $storeLocations = StoreLocation::where('is_active', true)->get();

        // Chuẩn bị dữ liệu ảnh để hiển thị trên form
        $images_data = [];
        $primary_image_id = null;
        $image_ids = [];

        // Nếu có lỗi validation, ưu tiên dữ liệu cũ (old input)
        if (session()->hasOldInput()) {
            $all_image_ids = array_unique(array_filter(array_merge(
                [old('primary_image_id')],
                old('image_ids', [])
            )));

            if (!empty($all_image_ids)) {
                $images = UploadedFile::whereIn('id', $all_image_ids)->get();
                $images_data = $images->keyBy('id')->map(fn ($image) => [
                    'id' => $image->id, 'url' => $image->url, 'alt_text' => $image->alt_text
                ])->all();
            }
        } else {
            // Nếu không, lấy dữ liệu từ chính model
            $primary_image = $tradeInItem->images()->where('type', 'primary_image')->first();
            $primary_image_id = $primary_image ? $primary_image->id : ($tradeInItem->images->first()->id ?? null);
            
            $image_ids = $tradeInItem->images->pluck('id')->toArray();

            $images_data = $tradeInItem->images->keyBy('id')->map(fn ($image) => [
                'id' => $image->id, 'url' => $image->url, 'alt_text' => $image->alt_text
            ])->all();
        }

        return view('admin.trade_in_items.edit', [
            'item' => $tradeInItem,
            'products' => $products,
            'storeLocations' => $storeLocations,
            'images_data' => $images_data,
            'primary_image_id' => $primary_image_id,
            'image_ids' => $image_ids,
        ]);
    }

    /**
     * Cập nhật thông tin sản phẩm cũ trong database.
     */
    public function update(UpdateTradeInItemRequest $request, TradeInItem $tradeInItem)
    {
        $validatedData = $request->validated();
        DB::beginTransaction();
        try {
            $tradeInItem->update([
                'product_variant_id'    => $validatedData['product_variant_id'],
                'store_location_id'     => $validatedData['store_location_id'],
                'type'                  => $validatedData['type'],
                'sku'                   => $validatedData['sku'] ?? $tradeInItem->sku, // Giữ SKU cũ nếu để trống
                'condition_grade'       => $validatedData['condition_grade'],
                'condition_description' => $validatedData['condition_description'],
                'selling_price'         => $validatedData['selling_price'],
                'imei_or_serial'        => $validatedData['imei_or_serial'],
                'status'                => $validatedData['status'],
            ]);

            // Gọi lại hàm syncImages để cập nhật ảnh
            $this->syncImages(
                $tradeInItem,
                $validatedData['primary_image_id'],
                $validatedData['image_ids']
            );

            DB::commit();
            return redirect()->route('admin.trade-in-items.index')
                ->with('success', 'Cập nhật sản phẩm cũ thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi cập nhật sản phẩm cũ: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Đã có lỗi xảy ra. Vui lòng thử lại.');
        }
    }
    /**
     * Helper function để đồng bộ ảnh chính và ảnh album cho sản phẩm.
     */
    private function syncImages(TradeInItem $item, int $primaryImageId, array $allImageIds): void
    {
        // Gỡ liên kết tất cả ảnh cũ (quan trọng cho chức năng edit sau này)
        $item->images()->update(['attachable_id' => null, 'attachable_type' => null, 'type' => null]);

        // Gán lại các ảnh mới
        $order = 1;
        foreach ($allImageIds as $imageId) {
            UploadedFile::where('id', $imageId)->update([
                'attachable_id'   => $item->id,
                'attachable_type' => TradeInItem::class,
                'type'            => ($imageId == $primaryImageId) ? 'primary_image' : 'album_image',
                'order'           => $order++
            ]);
        }
    }

    private function generateSku($variantId)
    {
        return 'USED-' . $variantId . '-' . strtoupper(uniqid());
    }
    /**
     * Chuyển sản phẩm vào thùng rác (Soft Delete).
     */
    public function destroy(TradeInItem $tradeInItem)
    {
        try {
            $tradeInItem->delete(); // Thực hiện soft delete
            return redirect()->route('admin.trade-in-items.index')->with('success', 'Sản phẩm đã được chuyển vào thùng rác.');
        } catch (\Exception $e) {
            Log::error("Lỗi khi xóa mềm sản phẩm thu cũ ID {$tradeInItem->id}: " . $e->getMessage());
            return back()->with('error', 'Đã có lỗi xảy ra khi xóa sản phẩm.');
        }
    }

    /**
     * Hiển thị danh sách các sản phẩm trong thùng rác.
     */
    public function trash()
    {
        $items = TradeInItem::onlyTrashed()->with([
            'productVariant.product',
            'storeLocation'
        ])->latest('deleted_at')->paginate(15);

        return view('admin.trade_in_items.trash', compact('items'));
    }

    /**
     * Khôi phục một sản phẩm từ thùng rác.
     */
    public function restore($id)
    {
        $item = TradeInItem::onlyTrashed()->findOrFail($id);
        $item->restore();
        return redirect()->route('admin.trade-in-items.trash')->with('success', 'Sản phẩm đã được khôi phục thành công.');
    }

    /**
     * Xóa vĩnh viễn một sản phẩm.
     */
    public function forceDelete($id)
    {
        $item = TradeInItem::onlyTrashed()->findOrFail($id);
        // Cần thêm logic xóa ảnh liên quan nếu có
        $item->forceDelete();
        return redirect()->route('admin.trade-in-items.trash')->with('success', 'Sản phẩm đã được xóa vĩnh viễn.');
    }
}
