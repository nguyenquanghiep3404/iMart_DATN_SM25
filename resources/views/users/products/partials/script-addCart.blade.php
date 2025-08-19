<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputVariantId = document.getElementById('wishlist-variant-id'); // id đồng nhất với form
        const inputVariantKey = document.getElementById('wishlist-variant-key');
        const inputImage = document.getElementById('wishlist-variant-image');
        const quantityInput = document.getElementById('quantity_input');

        const variantData = window.variantData || {};
        const attributeOrder = window.attributeOrder || [];

        let currentSelections = {}; // khởi tạo rỗng, hoặc từ biến global nếu có

        // Hàm cập nhật variant fields
        function updateVariantFields() {
            const key = attributeOrder.map(attr => currentSelections[attr] || '').join('_');
            inputVariantKey.value = key;

            if (variantData[key]) {
                inputVariantId.value = variantData[key].id || '';
                inputImage.value = variantData[key].image || '';

                // Cập nhật max số lượng theo tồn kho biến thể
                if (variantData[key].stock_quantity !== undefined) {
                    quantityInput.max = variantData[key].stock_quantity;
                } else {
                    quantityInput.removeAttribute('max');
                }
            } else {
                inputVariantId.value = '';
                inputImage.value = '';
                quantityInput.removeAttribute('max');
            }

            // Reset số lượng về 1 mỗi khi chọn biến thể mới
            quantityInput.value = 1;

            console.log('Variant Key:', key);
            console.log('Variant ID:', inputVariantId.value);
            console.log('Image:', inputImage.value);
            console.log('Max Quantity:', quantityInput.max);
        }

        // Bắt sự kiện radio chọn thuộc tính
        document.querySelectorAll('input[type="radio"][data-attr-name]').forEach(input => {
            input.addEventListener('change', function() {
                const attrName = this.dataset.attrName;
                const attrValue = this.value;
                currentSelections[attrName] = attrValue;
                updateVariantFields();
            });
        });

        // Xử lý submit form Thêm vào giỏ
        document.getElementById('add-to-cart-form').addEventListener('submit', function(e) {
            e.preventDefault();

            // Lấy giá trị số lượng hiện tại và giới hạn min/max
            let quantity = parseInt(quantityInput.value);
            const min = parseInt(quantityInput.min) || 1;
            const max = parseInt(quantityInput.max) || 1000;

            if (isNaN(quantity) || quantity < min) quantity = min;
            if (quantity > max) quantity = max;
            quantityInput.value = quantity; // cập nhật lại input số lượng nếu vượt giới hạn

            const token = this.querySelector('input[name="_token"]').value;

            const postData = {
                product_variant_id: inputVariantId.value,
                variant_key: inputVariantKey.value,
                image: inputImage.value,
                product_id: this.querySelector('input[name="product_id"]').value,
                quantity: quantity,
                _token: token,
            };

            fetch("{{ route('cart.add') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": token,
                    },
                    body: JSON.stringify(postData)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const cartUrl = "{{ route('cart.index') }}";
                        const message =
                            `${data.success} <br><a href="${cartUrl}" class="btn btn-sm btn-primary mt-2">Xem giỏ hàng</a>`;

                        toastr.options = {
                            closeButton: true,
                            progressBar: true,
                            escapeHtml: false,
                            timeOut: 3000,
                            positionClass: 'toast-bottom-right'
                        };

                        toastr.success(message);

                        const cartBadge = document.getElementById('cart-badge');
                        if (cartBadge) {
                            if (data.cartItemCount > 0) {
                                cartBadge.textContent = data.cartItemCount;
                                cartBadge.style.display = 'flex';
                            } else {
                                cartBadge.style.display = 'none';
                            }
                        }
                    } else if (data.error) {
                        toastr.error(data.error);
                    }
                })
                .catch(err => {
                    toastr.error('Có lỗi xảy ra, vui lòng thử lại.');
                    console.error(err);
                });
        });

        // Xử lý nút "Mua ngay"
        const buyNowBtn = document.getElementById('buy-now-btn');
        if (buyNowBtn) {
            buyNowBtn.addEventListener('click', function() {
                const form = document.getElementById('add-to-cart-form');
                const formData = new FormData(form);

                const variantKey = inputVariantKey.value?.trim();
                let quantity = parseInt(quantityInput.value) || 1;
                const min = parseInt(quantityInput.min) || 1;
                const max = parseInt(quantityInput.max) || 1000;
                if (quantity < min) quantity = min;
                if (quantity > max) quantity = max;
                quantityInput.value = quantity;

                const productId = formData.get('product_id');
                const hasVariants = Object.keys(variantData).length > 1;

                if (hasVariants && (!variantKey || variantKey === '' || variantKey === '_' || variantKey
                        .includes('undefined'))) {
                    toastr.error('Vui lòng chọn đầy đủ thông tin sản phẩm');
                    return;
                }
                if (!productId) {
                    toastr.error('Không tìm thấy thông tin sản phẩm.');
                    return;
                }

                const currentVariant = variantData[variantKey];
                if (currentVariant && currentVariant.stock_quantity !== undefined) {
                    if (quantity > currentVariant.stock_quantity) {
                        toastr.error(
                            `Số lượng vượt quá tồn kho. Chỉ còn ${currentVariant.stock_quantity} sản phẩm.`
                        );
                        return;
                    }
                }

                buyNowBtn.disabled = true;
                buyNowBtn.innerHTML =
                    '<span class="inline-block animate-spin mr-2"></span>Đang xử lý...';

                const buyNowData = {
                    product_id: parseInt(productId),
                    variant_key: variantKey,
                    quantity: quantity,
                };

                fetch('{{ route('buy-now.checkout') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(buyNowData)
                    })
                    .then(async res => {
                        const contentType = res.headers.get('content-type');
                        if (!contentType || !contentType.includes('application/json')) {
                            throw new Error('Server trả về định dạng không hợp lệ');
                        }
                        const data = await res.json();
                        if (!res.ok) {
                            throw new Error(data.message || `Lỗi server: ${res.status}`);
                        }
                        return data;
                    })
                    .then(data => {
                        if (data.success && data.redirect_url) {
                            window.location.href = data.redirect_url;
                        } else {
                            throw new Error(data.message || 'Phản hồi không hợp lệ từ server.');
                        }
                    })
                    .catch(error => {
                        toastr.error(error.message || 'Đã xảy ra lỗi khi xử lý. Vui lòng thử lại.');
                        console.error(error);
                    })
                    .finally(() => {
                        setTimeout(() => {
                            buyNowBtn.disabled = false;
                            buyNowBtn.innerHTML = 'MUA NGAY';
                        }, 1000);
                    });
            });
        }

        // Khởi tạo cập nhật lần đầu
        updateVariantFields();
    });
</script>
