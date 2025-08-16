<script>
    window.variantData = @json($variantData);
    window.attributeOrder = @json($attributeOrder);
    const wishlistVariantIds = @json($wishlistVariantIds ?? []);

    document.addEventListener('DOMContentLoaded', function() {
        const variantData = window.variantData;
        const attributeOrder = window.attributeOrder;

        const inputVariantId = document.getElementById('wishlist-variant-id');
        const inputVariantKey = document.getElementById('wishlist-variant-key');
        const inputImage = document.getElementById('wishlist-variant-image');
        const wishlistBtn = document.getElementById('wishlist-submit-btn');
        const radios = document.querySelectorAll('.variants input[type="radio"]');

        // Lấy selection hiện tại
        function getCurrentSelection() {
            const selection = {};
            radios.forEach(radio => {
                if (radio.checked) {
                    const attrName = radio.getAttribute('data-attr-name');
                    const value = radio.value;
                    selection[attrName] = value;
                }
            });
            return selection;
        }

        // Xây variant key
        function buildVariantKey(selection) {
            return attributeOrder.map(attr => selection[attr] || '').join('_');
        }

        // Cập nhật input hidden
        function updateWishlistForm(variantKey, variantInfo) {
            if (!variantInfo) return;
            inputVariantId.value = variantInfo.variant_id;
            inputVariantKey.value = variantKey;
            inputImage.value = variantInfo.image;
        }

        // Cập nhật màu nút yêu thích
        function updateWishlistButton(variantId) {
            if (wishlistVariantIds.includes(Number(variantId))) {
                wishlistBtn.classList.add('text-red-500', 'hover:text-red-600');
                wishlistBtn.classList.remove('text-gray-500');
            } else {
                wishlistBtn.classList.remove('text-red-500', 'hover:text-red-600');
                wishlistBtn.classList.add('text-gray-500');
            }
        }

        // Khi đổi biến thể
        function handleVariantChange() {
            const selection = getCurrentSelection();
            const variantKey = buildVariantKey(selection);
            const variantInfo = variantData[variantKey];
            updateWishlistForm(variantKey, variantInfo);
            if (variantInfo) {
                updateWishlistButton(variantInfo.variant_id);
            }
        }

        // Gắn sự kiện
        radios.forEach(radio => {
            radio.addEventListener('change', handleVariantChange);
        });
        document.querySelectorAll('.option-container').forEach(label => {
            label.addEventListener('click', () => {
                setTimeout(() => handleVariantChange(), 10);
            });
        });

        // Gọi khi trang load
        handleVariantChange();

        // Gửi form AJAX
        document.getElementById('wishlist-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const variantId = Number(inputVariantId.value);
            const variantKey = inputVariantKey.value;
            const image = inputImage.value;
            const productId = this.querySelector('input[name="product_id"]').value;
            const token = this.querySelector('input[name="_token"]').value;

            const postData = {
                product_variant_id: variantId,
                variant_key: variantKey,
                image: image,
                product_id: productId,
                _token: token
            };

            fetch("{{ route('wishlist.add') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": token
                    },
                    body: JSON.stringify(postData)
                })
                .then(response => {
                    if (!response.ok) return response.json().then(err => Promise.reject(err));
                    return response.json();
                })
                .then(data => {
                    toastr.options = {
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-top-right",
                        timeOut: "3000",
                        showDuration: "300",
                        hideDuration: "1000",
                        showMethod: "slideDown",
                        hideMethod: "slideUp"
                    };

                    if (data.success) {
                        toastr.success(data.success);

                        const idx = wishlistVariantIds.indexOf(variantId);
                        if (data.success.includes('xóa')) {
                            if (idx > -1) wishlistVariantIds.splice(idx, 1);
                        } else {
                            if (idx === -1) wishlistVariantIds.push(variantId);
                        }

                        updateWishlistButton(variantId);
                    } else if (data.error) {
                        toastr.error(data.error);
                    }
                })
                .catch(err => {
                    toastr.error(err?.error || 'Có lỗi xảy ra, vui lòng thử lại.');
                    console.error('Lỗi AJAX:', err);
                });
        });
    });
</script>
