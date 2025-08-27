<script>
document.addEventListener('DOMContentLoaded', function() {
    // Lấy các phần tử DOM cho Modal
    const modal = document.getElementById('order-detail-modal');
    if (!modal) {
        console.error('Modal element not found');
        return;
    }
    
    const modalOverlay = modal.querySelector('.modal-overlay');
    const openModalBtns = document.querySelectorAll('.open-modal-btn');
    const closeModalBtns = modal.querySelectorAll('.close-modal-btn');

    // Các phần tử bên trong Modal
    const modalLoading = document.getElementById('modal-loading');
    const modalOrderContent = document.getElementById('modal-order-content');
    const modalOrderId = document.getElementById('modal-order-id');
    const modalPackageId = document.getElementById('modal-package-id');
    const modalCustomerName = document.getElementById('modal-customer-name');
    const modalAddress = document.getElementById('modal-address');
    // const modalTotal = document.getElementById('modal-total'); // Đã bỏ hiển thị tổng giá trị
    const modalOrderStatus = document.getElementById('modal-order-status');
    const modalProducts = document.getElementById('modal-products');
    const shippingUnitSelect = document.getElementById('modal-shipping-unit');
    const assignBtn = document.getElementById('modal-assign-btn');
    const successBtn = document.getElementById('modal-success-btn');
    const successMessage = document.getElementById('modal-success-message');
    const errorMessage = document.getElementById('modal-error-message');
    
    // Kiểm tra các element quan trọng
    if (!modalLoading || !modalOrderContent || !shippingUnitSelect || !assignBtn || !successBtn) {
        console.error('Some required modal elements not found');
        return;
    }

    let currentFulfillmentId = null;

    // Hàm reset trạng thái modal
    const resetModalState = () => {
        modalLoading.classList.remove('hidden');
        modalOrderContent.classList.add('hidden');
        shippingUnitSelect.value = "";
        shippingUnitSelect.disabled = false;
        assignBtn.disabled = false;
        successBtn.disabled = true;
        successMessage.classList.add('hidden');
        errorMessage.classList.add('hidden');
        successMessage.textContent = '';
        errorMessage.textContent = '';
    };

    // Hàm mở modal
    const openModal = async (fulfillmentId, packageId, customerName) => {
        resetModalState();
        currentFulfillmentId = fulfillmentId;
        modal.classList.remove('hidden');
        
        try {
            // Gọi API để lấy thông tin chi tiết gói hàng
            const response = await fetch(`/admin/external-shipping/${fulfillmentId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                // Hiển thị thông tin gói hàng
                modalOrderId.textContent = `#${data.fulfillment.order?.order_code || data.fulfillment.order?.id}`;
                
                // Hiển thị mã vận đơn từ OrderFulfillment
                let trackingCode = data.fulfillment.tracking_code || `GH${data.fulfillment.order?.order_code || data.fulfillment.order?.id}`;
                modalPackageId.textContent = `#${trackingCode}`;
                
                modalCustomerName.textContent = data.fulfillment.order?.user?.name || 'N/A';
                
                // Hiển thị đơn vị vận chuyển từ OrderFulfillment
                const shippingUnitInfo = document.getElementById('shipping-unit-info');
                const shippingUnitName = document.getElementById('modal-shipping-unit-name');
                let shippingCarrier = data.fulfillment.shipping_carrier;
                
                if (shippingCarrier) {
                    shippingUnitName.textContent = shippingCarrier;
                    shippingUnitInfo.style.display = 'block';
                } else {
                    shippingUnitInfo.style.display = 'none';
                }
                
                
                // Hiển thị địa chỉ đầy đủ
                let fullAddress = 'N/A';
                const addressParts = [];
                
                // Thêm địa chỉ chi tiết
                if (data.fulfillment.order?.shipping_address_line1) {
                    addressParts.push(data.fulfillment.order.shipping_address_line1);
                }
                if (data.fulfillment.order?.shipping_address_line2) {
                    addressParts.push(data.fulfillment.order.shipping_address_line2);
                }
                
                // Thêm thông tin phường/xã, quận/huyện, tỉnh/thành
                if (data.fulfillment.order?.shipping_ward && data.fulfillment.order.shipping_ward.name_with_type) {
                    addressParts.push(data.fulfillment.order.shipping_ward.name_with_type);
                }
                if (data.fulfillment.order?.shipping_district && data.fulfillment.order.shipping_district.name_with_type) {
                    addressParts.push(data.fulfillment.order.shipping_district.name_with_type);
                }
                if (data.fulfillment.order?.shipping_province && data.fulfillment.order.shipping_province.name_with_type) {
                    addressParts.push(data.fulfillment.order.shipping_province.name_with_type);
                }
                
                if (addressParts.length > 0) {
                    fullAddress = addressParts.join(', ');
                }
                
                modalAddress.textContent = fullAddress;
                
                // Cập nhật trạng thái
                updateStatus(data.fulfillment.status);
                
                // Hiển thị sản phẩm
                displayProducts(data.fulfillment.items || []);
                
                modalLoading.classList.add('hidden');
                modalOrderContent.classList.remove('hidden');
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            console.error('Error loading fulfillment details:', error);
            errorMessage.textContent = 'Có lỗi xảy ra khi tải thông tin gói hàng: ' + error.message;
            errorMessage.classList.remove('hidden');
            modalLoading.classList.add('hidden');
        }
    };

    // Hàm đóng modal
    const closeModal = () => {
        modal.classList.add('hidden');
        currentFulfillmentId = null;
    };

    // Hàm cập nhật trạng thái
    function updateStatus(status) {
        let text, bgColor, textColor;
        
        switch(status) {
            case 'packed':
                text = 'Đã đóng gói';
                bgColor = 'bg-yellow-100';
                textColor = 'text-yellow-800';
                assignBtn.disabled = false;
                shippingUnitSelect.disabled = false;
                successBtn.disabled = true;
                successBtn.textContent = 'Đánh dấu Giao thành công';
                break;
            case 'shipped':
                text = 'Đang vận chuyển';
                bgColor = 'bg-blue-100';
                textColor = 'text-blue-800';
                assignBtn.disabled = true;
                shippingUnitSelect.disabled = true;
                successBtn.disabled = false;
                successBtn.textContent = 'Đánh dấu Giao thành công';
                break;
            case 'delivered':
                text = 'Đã giao hàng';
                bgColor = 'bg-green-100';
                textColor = 'text-green-800';
                assignBtn.disabled = true;
                shippingUnitSelect.disabled = true;
                successBtn.disabled = true;
                successBtn.textContent = 'Đã giao thành công';
                break;
            default:
                text = status;
                bgColor = 'bg-gray-100';
                textColor = 'text-gray-800';
        }
        
        modalOrderStatus.textContent = text;
        modalOrderStatus.className = `ml-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${bgColor} ${textColor} transition-all`;
    }

    // Hàm hiển thị sản phẩm
    function displayProducts(orderItems) {
        modalProducts.innerHTML = '';
        
        if (!orderItems || !Array.isArray(orderItems)) {
            modalProducts.innerHTML = '<div class="text-center text-gray-500 py-4">Không có sản phẩm nào</div>';
            return;
        }
        
        orderItems.forEach(item => {
            // Prepare product image
            let productImage = null;
            if (item.order_item?.product_variant?.primary_image?.path) {
                productImage = `/storage/${item.order_item.product_variant.primary_image.path}`;
            } else if (item.order_item?.product_variant?.product?.cover_image?.path) {
                productImage = `/storage/${item.order_item.product_variant.product.cover_image.path}`;
            } else if (item.image_url) {
                productImage = item.image_url;
            } else if (item.product_image) {
                productImage = item.product_image;
            }

            // Get product name
            let productName = 'N/A';
            if (item.order_item?.product_variant?.product?.name) {
                productName = item.order_item.product_variant.product.name;
            } else if (item.product_name) {
                productName = item.product_name;
            }

            // Get SKU
            let sku = '';
            if (item.order_item?.product_variant?.sku) {
                sku = item.order_item.product_variant.sku;
            }

            // Prepare variant info
            let variantInfo = '';
            let variantAttrs = null;
            
            // Try to get variant attributes from order_item first
            if (item.order_item?.product_variant?.variant_attributes) {
                if (typeof item.order_item.product_variant.variant_attributes === 'string') {
                    try {
                        variantAttrs = JSON.parse(item.order_item.product_variant.variant_attributes);
                    } catch (e) {
                        console.log('Failed to parse variant_attributes from order_item:', item.order_item.product_variant.variant_attributes);
                    }
                } else if (typeof item.order_item.product_variant.variant_attributes === 'object') {
                    variantAttrs = item.order_item.product_variant.variant_attributes;
                }
            }
            // Fallback to item variant_attributes
            else if (item.variant_attributes && item.variant_attributes !== null) {
                if (typeof item.variant_attributes === 'string') {
                    try {
                        variantAttrs = JSON.parse(item.variant_attributes);
                    } catch (e) {
                        console.log('Failed to parse variant_attributes:', item.variant_attributes);
                    }
                } else if (typeof item.variant_attributes === 'object') {
                    variantAttrs = item.variant_attributes;
                }
            }

            if (variantAttrs && Object.keys(variantAttrs).length > 0) {
                const variants = Object.entries(variantAttrs)
                    .map(([key, value]) => `${key}: ${value}`)
                    .join(', ');
                if (variants) {
                    variantInfo = `<div class="text-xs text-gray-500 mt-1">${variants}</div>`;
                }
            }
            
            const productHtml = `
                <div class="grid grid-cols-12 gap-4 items-center border-t md:border-none pt-4 md:pt-0 px-4">
                    <!-- Thông tin sản phẩm -->
                    <div class="col-span-12 md:col-span-6 flex items-center space-x-4">
                        <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                            ${productImage ? 
                                `<img src="${productImage}" alt="${item.product_name || 'Sản phẩm'}"
                                    class="w-full h-full object-cover"
                                    onerror="this.parentElement.innerHTML='<div class=\'flex items-center justify-center w-full h-full text-gray-400\'><i class=\'fas fa-image text-lg\'></i></div>'">`
                                : `<div class="flex items-center justify-center w-full h-full text-gray-400">
                                    <i class="fas fa-box text-lg"></i>
                                  </div>`}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-800 line-clamp-2">${productName}</p>
                            ${variantInfo}
                            ${sku ? `<div class="text-xs text-gray-400 mt-1">SKU: ${sku}</div>` : ''}
                        </div>
                    </div>
                    <!-- Số lượng -->
                    <div class="col-span-4 md:col-span-2 text-left md:text-center">
                        <span class="md:hidden text-xs font-bold text-gray-500">SỐ LƯỢNG: </span>${item.quantity}
                    </div>
                    <!-- Đơn giá -->
                    <div class="col-span-4 md:col-span-2 text-left md:text-right">
                        <span class="md:hidden text-xs font-bold text-gray-500">ĐƠN GIÁ: </span>${new Intl.NumberFormat('vi-VN').format(item.order_item?.price || item.price || 0)} VNĐ
                    </div>
                    <!-- Thành tiền -->
                    <div class="col-span-4 md:col-span-2 text-left md:text-right font-bold text-blue-600">
                        <span class="md:hidden text-xs font-bold text-gray-500">THÀNH TIỀN: </span>${new Intl.NumberFormat('vi-VN').format((item.order_item?.price || item.price || 0) * (item.quantity || 0))} VNĐ
                    </div>
                </div>
            `;
            modalProducts.insertAdjacentHTML('beforeend', productHtml);
        });
    }

    // Thêm sự kiện cho dropdown đơn vị vận chuyển
    if (shippingUnitSelect) {
        shippingUnitSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const selectedText = selectedOption.text;
            
            // Hiển thị tên đơn vị vận chuyển đã chọn
            let shippingUnitDisplay = document.getElementById('selected-shipping-unit');
            if (!shippingUnitDisplay) {
                shippingUnitDisplay = document.createElement('div');
                shippingUnitDisplay.id = 'selected-shipping-unit';
                shippingUnitDisplay.className = 'mt-2 text-sm text-gray-600';
                this.parentNode.appendChild(shippingUnitDisplay);
            }
            
            if (this.value) {
                shippingUnitDisplay.textContent = `Đã chọn: ${selectedText}`;
                shippingUnitDisplay.classList.remove('hidden');
            } else {
                shippingUnitDisplay.classList.add('hidden');
            }
        });
    }

    // Gán sự kiện cho các nút mở modal
    if (openModalBtns && openModalBtns.length > 0) {
        openModalBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const fulfillmentId = btn.dataset.fulfillmentId;
                const packageId = btn.dataset.packageId;
                const customerName = btn.dataset.customerName;
                openModal(fulfillmentId, packageId, customerName);
            });
        });
    }

    // Gán sự kiện cho các nút đóng modal
    if (closeModalBtns && closeModalBtns.length > 0) {
        closeModalBtns.forEach(btn => btn.addEventListener('click', closeModal));
    }
    if (modalOverlay) {
        modalOverlay.addEventListener('click', closeModal);
    }
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    // Xử lý gán đơn vị vận chuyển
    assignBtn.addEventListener('click', async () => {
        if (!shippingUnitSelect.value) {
            alert('Vui lòng chọn một đơn vị vận chuyển!');
            return;
        }
        
        try {
            const response = await fetch(`/admin/external-shipping/${currentFulfillmentId}/assign`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    shipping_unit: shippingUnitSelect.value
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                updateStatus('shipped');
                successMessage.textContent = data.message;
                successMessage.classList.remove('hidden');
                
                // Reload trang sau 2 giây
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            console.error('Error assigning shipping unit:', error);
            errorMessage.textContent = 'Có lỗi xảy ra: ' + error.message;
            errorMessage.classList.remove('hidden');
        }
    });

    // Xử lý đánh dấu giao thành công
    successBtn.addEventListener('click', async () => {
        try {
            const response = await fetch(`/admin/external-shipping/${currentFulfillmentId}/delivered`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                updateStatus('delivered');
                successMessage.textContent = `Gói hàng ${modalPackageId.textContent} đã được cập nhật thành công!`;
                successMessage.classList.remove('hidden');
                
                // Reload trang sau 2 giây
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            console.error('Error marking as delivered:', error);
            errorMessage.textContent = 'Có lỗi xảy ra: ' + error.message;
            errorMessage.classList.remove('hidden');
        }
    });
});
</script>