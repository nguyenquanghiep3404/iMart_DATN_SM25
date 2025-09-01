# Tóm tắt dự án iMart - Hệ thống thương mại điện tử

## 1. Tổng quan dự án

iMart là một hệ thống thương mại điện tử toàn diện được phát triển trên nền tảng Laravel, cung cấp giải pháp mua sắm trực tuyến với đầy đủ tính năng từ quản lý sản phẩm, đơn hàng đến thanh toán và giao hàng. Hệ thống hỗ trợ đa cửa hàng (multi-location), POS bán hàng tại quầy, quản lý kho hàng phức tạp và hệ thống marketing tự động.

### Công nghệ sử dụng
- **Framework**: Laravel 11.x
- **PHP**: Phiên bản 8.3+
- **Database**: MySQL 8.0+
- **Frontend**: HTML, CSS, JavaScript, Tailwind CSS
- **Thanh toán**: Tích hợp các cổng thanh toán (VNPay, Momo, Casso)
- **Realtime**: Laravel Reverb cho chat và thông báo
- **Export/Import**: Maatwebsite/Excel
- **Queue**: Redis/Database cho xử lý background jobs
- **File Storage**: Local/S3 compatible storage

## 2. Kiến trúc hệ thống

### Mô hình MVC
Dự án được tổ chức theo mô hình MVC (Model-View-Controller) của Laravel:
- **Models**: Quản lý dữ liệu và logic nghiệp vụ
- **Views**: Giao diện người dùng
- **Controllers**: Xử lý yêu cầu và điều hướng

### Phân quyền
- Hệ thống phân quyền dựa trên Roles và Permissions
- Các vai trò: Admin, Nhân viên, Khách hàng, Khách vãng lai

## 3. Cấu trúc dữ liệu chính

### Quản lý người dùng và phân quyền
- **User**: Thông tin người dùng với hỗ trợ khách vãng lai (is_guest), điểm thưởng (loyalty_points_balance)
- **Role**: Vai trò người dùng (admin, customer, shipper, employee)
- **Permission**: Quyền hạn cụ thể cho từng chức năng
- **RoleUser**: Bảng trung gian many-to-many giữa User và Role
- **PermissionRole**: Bảng trung gian many-to-many giữa Permission và Role
- **CustomerGroup**: Phân nhóm khách hàng theo tiêu chí (VIP, thân thiết, mới...)

### Quản lý sản phẩm nâng cao
- **Product**: Thông tin cơ bản sản phẩm (simple/variable), SEO, warranty
- **ProductVariant**: Biến thể sản phẩm với giá cost/sale, quản lý tồn kho, kích thước, trọng lượng
- **Category**: Danh mục đa cấp với SEO, soft delete, hiển thị homepage
- **Attribute**: Thuộc tính sản phẩm (màu sắc, kích thước) với display_type
- **AttributeValue**: Giá trị thuộc tính với metadata (hex color cho color swatch)
- **ProductVariantAttributeValue**: Liên kết variant với attribute values
- **SpecificationGroup**: Nhóm thông số kỹ thuật
- **Specification**: Thông số kỹ thuật chi tiết (CPU, RAM, camera...)
- **ProductSpecificationValue**: Giá trị thông số cho từng variant

### Quản lý đơn hàng và fulfillment
- **Order**: Đơn hàng với hỗ trợ guest checkout, địa chỉ kép, confirmation token
- **OrderItem**: Chi tiết sản phẩm với snapshot data, SKU, image
- **OrderFulfillment**: Quản lý giao hàng theo từng gói/kho
- **OrderFulfillmentItem**: Chi tiết sản phẩm trong từng gói giao hàng
- **ReturnRequest**: Yêu cầu trả hàng với workflow approval
- **ReturnItem**: Chi tiết sản phẩm trả hàng
- **CancellationRequest**: Yêu cầu hủy đơn hàng với quy trình duyệt

### Quản lý kho hàng và POS
- **StoreLocation**: Quản lý đa cửa hàng/kho
- **ProductInventory**: Tồn kho theo từng location và loại (new, defective)
- **InventoryLot**: Quản lý lô hàng với expiry date
- **InventorySerial**: Quản lý serial number cho sản phẩm
- **Stocktake**: Kiểm kê tồn kho
- **StocktakeItem**: Chi tiết kiểm kê từng sản phẩm
- **Register**: Quầy thu ngân POS
- **PosSession**: Phiên làm việc POS với opening/closing balance
- **InventoryMovement**: Lịch sử xuất nhập kho
- **StockTransfer**: Chuyển kho giữa các location

### Marketing và khuyến mãi
- **Coupon**: Mã giảm giá với điều kiện áp dụng, soft delete
- **CouponUsage**: Lịch sử sử dụng coupon
- **FlashSale**: Khuyến mãi flash sale theo thời gian
- **FlashSaleTimeSlot**: Khung giờ flash sale
- **FlashSaleProduct**: Sản phẩm tham gia flash sale
- **MarketingCampaign**: Chiến dịch email marketing
- **MarketingCampaignLog**: Log gửi email campaign
- **Banner**: Quản lý banner quảng cáo với soft delete
- **ProductBundle**: Gói sản phẩm combo
- **LoyaltyPointLog**: Lịch sử tích điểm thưởng

### Tương tác người dùng
- **Review**: Đánh giá sản phẩm
- **Comment**: Bình luận với hỗ trợ guest, upload ảnh
- **Wishlist**: Danh sách yêu thích
- **WishlistItem**: Chi tiết sản phẩm yêu thích
- **Cart**: Giỏ hàng với polymorphic support
- **CartItem**: Chi tiết giỏ hàng
- **AbandonedCart**: Giỏ hàng bỏ dở để remarketing

### Chat và hỗ trợ
- **ChatConversation**: Cuộc hội thoại hỗ trợ
- **ChatMessage**: Tin nhắn chat
- **ChatParticipant**: Người tham gia chat

### Quản lý địa chỉ
- **Province**: Tỉnh/thành phố (hệ thống mới)
- **Ward**: Phường/xã (hệ thống mới)
- **ProvinceOld/DistrictOld/WardOld**: Hệ thống địa chỉ cũ (backward compatibility)
- **Address**: Địa chỉ người dùng với default shipping/billing

### Blog và nội dung
- **Post**: Bài viết blog với SEO
- **PostCategory**: Danh mục bài viết
- **PostTag**: Thẻ bài viết
- **PostPostTag**: Bảng trung gian post-tag
- **ContactForm**: Form liên hệ
- **UploadedFile**: Quản lý file upload với soft delete

### Hệ thống và logs
- **ActivityLog**: Log hoạt động hệ thống
- **Notification**: Thông báo người dùng
- **SystemSetting**: Cài đặt hệ thống
- **WorkShift**: Ca làm việc nhân viên
- **EmployeeSchedule**: Lịch làm việc nhân viên

## 4. Luồng hoạt động chính

### Luồng mua hàng đa kênh
1. **Duyệt sản phẩm**: Khách hàng xem sản phẩm với thông số kỹ thuật chi tiết
2. **Thêm giỏ hàng**: Hỗ trợ cả khách đăng ký và khách vãng lai
3. **Checkout**: Chọn địa chỉ giao hàng/thanh toán, áp dụng coupon, tích điểm
4. **Thanh toán**: Đa phương thức thanh toán với xác thực
5. **Xử lý đơn hàng**: Phân bổ từ kho gần nhất, tạo fulfillment
6. **Giao hàng**: Theo dõi từng gói hàng với tracking code
7. **Hoàn thành**: Cập nhật điểm thưởng, yêu cầu đánh giá

### Luồng quản lý sản phẩm nâng cao
1. **Tạo sản phẩm**: Thiết lập loại (simple/variable), SEO, warranty
2. **Quản lý variant**: Tạo biến thể với attributes, specifications
3. **Quản lý tồn kho**: Phân bổ theo location, lot, serial number
4. **Pricing**: Thiết lập giá cost/sale, flash sale, bundle
5. **Marketing**: Tạo campaign, coupon, loyalty points

### Luồng xử lý đơn hàng đa kho
1. **Nhận đơn**: Xác thực thanh toán, kiểm tra tồn kho
2. **Phân bổ kho**: Chọn location tối ưu, tạo fulfillment
3. **Picking**: Xuất kho theo fulfillment, cập nhật inventory
4. **Packing**: Đóng gói, tạo tracking code
5. **Shipping**: Giao hàng với carrier, cập nhật trạng thái
6. **Delivery**: Xác nhận giao hàng, cập nhật completed
7. **Post-delivery**: Xử lý return/cancellation nếu có

### Luồng POS và bán hàng tại cửa hàng
1. **Mở ca**: Nhân viên mở POS session với opening balance
2. **Bán hàng**: Quét barcode, tính tiền, thanh toán
3. **Xuất kho**: Tự động trừ inventory tại location
4. **Đóng ca**: Kiểm đếm tiền, closing balance, báo cáo

### Luồng quản lý kho hàng
1. **Nhập kho**: Tạo inventory movement, cập nhật stock
2. **Chuyển kho**: Stock transfer giữa các location
3. **Kiểm kê**: Tạo stocktake, so sánh system vs counted
4. **Điều chỉnh**: Cập nhật inventory theo kết quả kiểm kê

### Luồng marketing tự động
1. **Phân nhóm khách hàng**: Dựa trên purchase history, loyalty points
2. **Tạo campaign**: Email marketing theo customer group
3. **Abandoned cart**: Gửi email nhắc nhở giỏ hàng bỏ dở
4. **Loyalty program**: Tích điểm, đổi thưởng, tier upgrade
5. **Flash sale**: Thiết lập time slot, sản phẩm tham gia

### Luồng hỗ trợ khách hàng
1. **Chat support**: Khách hàng tạo conversation
2. **Assignment**: Phân công cho support agent
3. **Resolution**: Xử lý vấn đề, cập nhật trạng thái
4. **Follow-up**: Theo dõi satisfaction, close ticket

## 5. Tính năng nổi bật

### Hệ thống đa cửa hàng (Multi-store)
- Quản lý nhiều cửa hàng/kho từ một hệ thống
- Phân bổ tồn kho theo từng location
- Chuyển kho tự động giữa các chi nhánh
- Báo cáo theo từng cửa hàng

### Quản lý sản phẩm nâng cao
- Sản phẩm simple/variable với attributes phức tạp
- Thông số kỹ thuật chi tiết (specifications)
- Quản lý warranty và after-sales service
- Hỗ trợ product bundle và combo
- SEO tự động cho sản phẩm

### Hệ thống POS tích hợp
- Bán hàng tại cửa hàng với barcode scanner
- Quản lý ca làm việc (shift management)
- Đồng bộ tồn kho real-time
- Báo cáo doanh thu theo ca/ngày

### Quản lý kho hàng thông minh
- Tracking theo lot number và expiry date
- Quản lý serial number cho electronics
- Kiểm kê tự động với variance report
- Inventory movement history
- Stock transfer giữa các location

### Marketing automation
- Email marketing theo customer segments
- Abandoned cart recovery
- Loyalty points program với tier system
- Flash sale với time slots
- Coupon engine với điều kiện phức tạp

### Fulfillment và logistics
- Đóng gói đơn hàng theo kho
- Tracking code tự động
- Hỗ trợ multiple carriers
- Return/cancellation workflow
- Guest checkout cho khách vãng lai

### Customer experience
- Chat support tích hợp
- Review system với image upload
- Wishlist và comparison
- Address book với shipping/billing
- Order history và reorder

### Hệ thống thanh toán mở rộng
- Tích hợp Casso cho bank reconciliation
- Multiple payment methods
- Installment payment support
- Refund automation

### Analytics và reporting
- Real-time dashboard
- Sales performance by location
- Inventory turnover analysis
- Customer behavior tracking
- Marketing campaign ROI

### Technical excellence
- Laravel 11.x với PHP 8.3+
- Real-time notifications với Laravel Reverb
- Queue system cho heavy tasks
- File storage với cloud support
- API-first architecture

## 6. Kết luận

iMart là một hệ thống thương mại điện tử toàn diện, được xây dựng với kiến trúc hiện đại, đáp ứng đầy đủ nhu cầu của cả người mua và người bán. Hệ thống không chỉ cung cấp trải nghiệm mua sắm thuận tiện cho người dùng mà còn mang đến công cụ quản lý hiệu quả cho doanh nghiệp.

Với khả năng mở rộng và tùy biến cao, iMart có thể dễ dàng thích ứng với các yêu cầu kinh doanh khác nhau, từ cửa hàng nhỏ đến hệ thống thương mại điện tử quy mô lớn.
