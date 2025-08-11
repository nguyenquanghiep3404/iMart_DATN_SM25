# Hướng Dẫn Hệ Thống Quản Lý Tồn Kho Mới

## Tổng Quan

Hệ thống quản lý tồn kho đã được cập nhật để hỗ trợ mô hình "tạm giữ tồn kho" (inventory commitment), giúp tránh tình trạng bán quá số lượng có sẵn.

## Cấu Trúc Database

### Bảng `product_inventories`

- **`quantity`**: Tồn kho thực tế (On-Hand Quantity) - Số lượng sản phẩm thực sự có mặt trên kệ
- **`quantity_committed`**: Số lượng đã tạm giữ - Số lượng đã được khách hàng đặt mua nhưng chưa xuất kho
- **Available Quantity** (tính toán): `quantity - quantity_committed` - Số lượng có thể bán

## Luồng Hoạt Động

### 1. Khi Khách Hàng Đặt Hàng
```php
// Tự động tạm giữ tồn kho
$inventoryService = new InventoryCommitmentService();
$inventoryService->commitInventoryForOrder($order);
```

**Kết quả:**
- `quantity`: Không đổi
- `quantity_committed`: Tăng
- Available Quantity: Giảm

### 2. Khi Giao Hàng (Xuất Kho)
```php
// Xuất kho thực tế
$inventoryService->fulfillInventoryForOrder($order);
```

**Kết quả:**
- `quantity`: Giảm
- `quantity_committed`: Giảm
- Available Quantity: Không đổi

### 3. Khi Hủy Đơn Hàng
```php
// Thả tồn kho đã tạm giữ
$inventoryService->releaseInventoryForOrder($order);
```

**Kết quả:**
- `quantity`: Không đổi
- `quantity_committed`: Giảm
- Available Quantity: Tăng

### 4. Khi Nhập Hàng Mới
```php
// Nhập kho
$inventory->receiveStock($quantity);
```

**Kết quả:**
- `quantity`: Tăng
- `quantity_committed`: Không đổi
- Available Quantity: Tăng

## Sử Dụng Trong Code

### Model ProductInventory

```php
// Kiểm tra có đủ tồn kho không
$inventory->hasAvailableStock(5); // true/false

// Tạm giữ tồn kho
$inventory->commitStock(3);

// Thả tồn kho
$inventory->releaseStock(2);

// Xuất kho thực tế
$inventory->fulfillStock(1);

// Nhập kho
$inventory->receiveStock(10);

// Lấy số lượng có thể bán
$availableQty = $inventory->available_quantity;
```

### Model ProductVariant

```php
// Tổng tồn kho có thể bán (tất cả kho)
$totalAvailable = $variant->available_stock;

// Kiểm tra có đủ tồn kho không
$variant->hasAvailableStock(5);

// Chi tiết tồn kho theo từng kho
$details = $variant->getInventoryDetails();
```

### Service InventoryCommitmentService

```php
$service = new InventoryCommitmentService();

// Tạm giữ tồn kho cho đơn hàng
$service->commitInventoryForOrder($order);

// Thả tồn kho khi hủy đơn
$service->releaseInventoryForOrder($order);

// Xuất kho khi giao hàng
$service->fulfillInventoryForOrder($order);

// Kiểm tra tồn kho khả dụng
$service->checkAvailableStock($variantId, $quantity);

// Lấy tổng tồn kho khả dụng
$service->getTotalAvailableStock($variantId);
```

## Tích Hợp Với Hệ Thống Hiện Tại

### PaymentController
- Tự động tạm giữ tồn kho khi tạo đơn hàng
- Nếu không đủ tồn kho, đơn hàng sẽ bị hủy

### OrderController
- Tự động xuất kho khi chuyển trạng thái sang "Đã giao"
- Tự động thả tồn kho khi hủy đơn hàng

## Migration

Để áp dụng hệ thống mới, chạy migration:

```bash
php artisan migrate
```

Migration sẽ thêm cột `quantity_committed` vào bảng `product_inventories`.

## Test

Chạy script test để kiểm tra hệ thống:

```bash
php test_inventory_commitment.php
```

## Lưu Ý Quan Trọng

1. **Backward Compatibility**: Thuộc tính `sellable_stock` cũ vẫn hoạt động, nhưng nên chuyển sang `available_stock`

2. **Hiển thị cho khách hàng**: Luôn sử dụng `available_stock` để hiển thị số lượng có thể mua

3. **Xử lý lỗi**: Luôn wrap các thao tác tồn kho trong try-catch để xử lý trường hợp không đủ hàng

4. **Logging**: Hệ thống tự động ghi log các thao tác quan trọng để theo dõi

## Ví Dụ Thực Tế

```php
// Trước khi cho phép khách hàng thêm vào giỏ hàng
if (!$variant->hasAvailableStock($requestedQuantity)) {
    return response()->json([
        'success' => false,
        'message' => 'Không đủ tồn kho'
    ]);
}

// Hiển thị số lượng có thể mua
echo "Còn lại: {$variant->available_stock} sản phẩm";

// Trong view sản phẩm
@if($variant->available_stock > 0)
    <button class="btn-add-to-cart">Thêm vào giỏ hàng</button>
@else
    <button disabled>Hết hàng</button>
@endif
```