# Hướng dẫn Debug thông tin lưu địa chỉ

## Vấn đề
Khi tiến hành mua sản phẩm và muốn kiểm tra thông tin lưu địa chỉ qua console.log, nhưng khi mua thành công thì trang sẽ chuyển hướng đến trang success và mất hết console.log của trang thanh toán.

## Giải pháp
Đã thêm hệ thống debug logs sử dụng sessionStorage để lưu trữ thông tin debug và có thể xem lại trên bất kỳ trang nào.

## Cách sử dụng

### 1. Thực hiện mua hàng bình thường
- Vào trang thanh toán
- Điền thông tin địa chỉ mới
- Tích vào checkbox "Lưu địa chỉ này vào sổ địa chỉ" (nếu đã đăng nhập)
- Tiến hành thanh toán

### 2. Xem debug logs sau khi chuyển trang
Sau khi chuyển đến trang success (hoặc bất kỳ trang nào), mở Console (F12) và sử dụng các lệnh sau:

#### Xem log mới nhất:
```javascript
showLatestPaymentDebugLog()
```

#### Xem tất cả logs:
```javascript
showPaymentDebugLogs()
```

#### Xóa tất cả logs:
```javascript
clearPaymentDebugLogs()
```

### 3. Thông tin được log
Mỗi log sẽ chứa:
- `timestamp`: Thời gian thực hiện
- `saveAddressCheckbox`: Checkbox có tồn tại không ('found' hoặc 'not found')
- `checked`: Trạng thái checkbox (true/false)
- `isLoggedIn`: Người dùng đã đăng nhập chưa
- `hasAddresses`: Người dùng có địa chỉ đã lưu chưa
- `addressId`: ID địa chỉ được chọn (nếu có)

## Ví dụ output
```javascript
// Gọi showLatestPaymentDebugLog()
🔍 Latest Payment Debug Log
Timestamp: 2024-01-15T10:30:45.123Z
Save Address Checkbox: found
Checked: true
Is Logged In: true
Has Addresses: false
Address ID: none
```

## Debug logs trong server
Ngoài ra, đã thêm debug logs trong PaymentController để theo dõi việc lưu địa chỉ:
- Kiểm tra file `storage/logs/laravel.log` để xem logs từ server
- Tìm kiếm "Saving new address" hoặc "Not saving address"

## Lưu ý
- Debug logs được lưu trong sessionStorage, sẽ bị xóa khi đóng tab/browser
- Script debug helper được load tự động trên tất cả các trang
- Nếu có logs từ trang thanh toán, console sẽ hiển thị thông báo gợi ý