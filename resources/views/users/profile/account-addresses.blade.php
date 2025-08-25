@extends('users.layouts.profile')
@section('content')
<style>
  .modal-content {
    background: #fff !important;
    border-radius: 16px;
    box-shadow: 0 0 32px 0 rgba(0,0,0,0.15);
  }
  .modal-header {
    background: #fff !important;
    border-bottom: 1px solid #eee;
    border-top-left-radius: 16px;
    border-top-right-radius: 16px;
  }
  .modal-title {
    color: #222 !important;
    font-weight: 600;
  }
  .btn-close {
    filter: none !important;
  }
  .modal-backdrop.show {
    opacity: 0.2 !important;
  }
  /* Căn giữa và thấp xuống cho modal */
  .modal-dialog.modal-lg.modal-dialog-centered {
    align-items: flex-start;
    display: flex;
    min-height: calc(100vh - 100px);
    margin-top: 100px;
  }
  /* Địa chỉ đẹp như Cartzilla */
  .address-card {
    display: flex;
    align-items: center;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    padding: 18px 24px;
    margin-bottom: 18px;
    border: 1.5px solid #f1f1f1;
    transition: border 0.2s;
  }
  .address-card.default {
    border: 2px solid #e53935;
    background: #fff5f5;
  }
  .address-icon {
    width: 36px;
    height: 36px;
    background: #f8d7da;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 18px;
    font-size: 1.4rem;
    color: #e53935;
  }
  .address-info {
    flex: 1;
  }
  .address-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 8px;
    text-decoration: none;
    min-width: 70px;
  }
  .address-actions .btn-link {
    text-decoration: none !important;
  }
  .address-actions .btn-link:hover {
    text-decoration: none !important;
    opacity: 0.8;
  }
  .badge-default {
    background: #fff0f0;
    color: #e53935;
    border: 1px solid #e53935;
    border-radius: 16px;
    font-size: 0.95rem;
    padding: 2px 14px;
    margin-left: 8px;
  }
  .btn-add-address {
    background: #e53935;
    color: #fff;
    border-radius: 24px;
    font-weight: 600;
    padding: 10px 28px;
    float: right;
    margin-bottom: 18px;
    margin-top: -8px;
    transition: background 0.2s, color 0.2s;
  }
  .btn-add-address:hover {
    background: #b71c1c;
    color: #fff !important;
  }
  /* Bo góc input nhẹ nhàng */
  .form-control, .form-select {
    border-radius: 10px !important;
  }
  /* Tối giản */
  #addressModal .form-check-input {
    -webkit-appearance: auto !important;
    -moz-appearance: auto !important;
    appearance: auto !important;
    accent-color: blue;
  }
</style>
<div class="col-lg-9">
  <div class="ps-lg-3 ps-xl-0">
    <h1 class="h2 mb-1 mb-sm-2">Sổ địa chỉ nhận hàng</h1>
    <div class="d-flex justify-content-end mb-3">
      <button class="btn btn-add-address" data-bs-toggle="modal" data-bs-target="#addressModal" data-action="add">
        <i class="fas fa-plus"></i>  Thêm địa chỉ chi mới
      </button>
    </div>
    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <!-- Danh sách địa chỉ -->
    <div class="mb-4">
      @if($addresses->isEmpty())
        <div class="alert alert-info">Bạn chưa có địa chỉ nào. Hãy thêm địa chỉ mới!</div>
      @else
        @foreach($addresses as $address)
          <div class="address-card {{ $address->is_default_shipping ? 'default' : '' }}">
            <div class="address-icon">
              <i class="fas fa-home"></i>
            </div>
            <div class="address-info">
              <div>
                <strong>{{ $address->full_name }}</strong>
                <span style="margin-left:8px;">{{ $address->phone_number }}</span>
                @if($address->is_default_shipping)
                  <span class="badge-default">Mặc định</span>
                @endif
              </div>
              <div class="text-muted small">
                {{ $address->full_address ?? ($address->address_line1 . ', ' . ($address->old_ward_code ?? '-') . ', ' . ($address->old_district_code ?? '-') . ', ' . ($address->old_province_code ?? '-')) }}
              </div>
            </div>
            <div class="address-actions">
              <button class="btn btn-link text-danger p-0" data-bs-toggle="modal" data-bs-target="#addressModal" data-action="edit" data-id="{{ $address->id }}" data-full_name="{{ $address->full_name }}" data-phone_number="{{ $address->phone_number }}" data-old_province_code="{{ $address->old_province_code }}" data-old_district_code="{{ $address->old_district_code }}" data-old_ward_code="{{ $address->old_ward_code }}" data-address_line1="{{ $address->address_line1 }}" data-is_default_shipping="{{ $address->is_default_shipping }}">Sửa</button>
              @if(!$address->is_default_shipping)
                <button type="button"
                    class="btn btn-link text-dark p-0 btn-delete-address"
                    data-id="{{ $address->id }}"
                    data-action="{{ route('addresses.destroy', $address) }}">
                    Xoá
                </button>
              @endif
            </div>
          </div>
        @endforeach
      @endif
    </div>
    <!-- Modal Thêm/Sửa địa chỉ đẹp chuẩn Cartzilla -->
    <div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addressModalLabel">Thêm địa chỉ mới</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
          </div>
          <div class="modal-body">
            <form class="row g-3 g-lg-4 needs-validation" id="addressForm" method="POST" action="{{ route('addresses.store') }}" novalidate>
              @csrf
              <div class="col-lg-6">
                <div class="position-relative">
                  <label class="form-label">Họ tên</label>
                  <input type="text" class="form-control @error('full_name') is-invalid @enderror" name="full_name" value="{{ old('full_name') }}" required>
                  <div class="invalid-feedback">@error('full_name'){{ $message }}@else Vui lòng nhập họ tên! @enderror</div>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="position-relative">
                  <label class="form-label">Số điện thoại</label>
                  <input type="text" pattern="0[0-9]{9}" class="form-control @error('phone_number') is-invalid @enderror" name="phone_number" value="{{ old('phone_number') }}" required>
                  <div class="invalid-feedback">@error('phone_number'){{ $message }}@else Vui lòng nhập số điện thoại! @enderror</div>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="position-relative">
                  <label class="form-label">Tỉnh/Thành phố</label>
                  <select class="form-select @error('old_province_code') is-invalid @enderror" id="old_province_code" name="old_province_code" required>
                    <option value="">Chọn tỉnh/thành phố</option>
                  </select>
                  <div class="invalid-feedback">@error('old_province_code'){{ $message }}@else Vui lòng chọn tỉnh/thành phố! @enderror</div>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="position-relative">
                  <label class="form-label">Quận/Huyện</label>
                  <select class="form-select @error('old_district_code') is-invalid @enderror" id="old_district_code" name="old_district_code" required>
                    <option value="">Chọn quận/huyện</option>
                  </select>
                  <div class="invalid-feedback">@error('old_district_code'){{ $message }}@else Vui lòng chọn quận/huyện! @enderror</div>
                </div>
              </div>
              <div class="col-lg-12">
                <div class="position-relative">
                  <label class="form-label">Xã/Phường</label>
                  <select class="form-select @error('old_ward_code') is-invalid @enderror" id="old_ward_code" name="old_ward_code" required>
                    <option value="">Chọn xã/phường</option>
                  </select>
                  <div class="invalid-feedback">@error('old_ward_code'){{ $message }}@else Vui lòng chọn xã/phường! @enderror</div>
                </div>
              </div>
              <div class="col-lg-12">
                <div class="position-relative">
                  <label class="form-label">Địa chỉ cụ thể</label>
                  <input type="text" class="form-control @error('address_line1') is-invalid @enderror" name="address_line1" value="{{ old('address_line1') }}" required>
                  <div class="invalid-feedback">@error('address_line1'){{ $message }}@else Vui lòng nhập địa chỉ cụ thể! @enderror</div>
                </div>
              </div>
              <div class="col-12">
                <div class="form-check mb-0">
                  <input type="checkbox" class="form-check-input" id="is_default_shipping" name="is_default_shipping" value="1" {{ old('is_default_shipping') ? 'checked' : '' }}>
                  <label for="is_default_shipping" class="form-check-label">Đặt làm địa chỉ mặc định</label>
                </div>
              </div>
              <div class="col-12">
                <div class="d-flex gap-3 pt-2 pt-sm-0">
                  <button type="submit" class="btn btn-danger">Xác nhận</button>
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal xác nhận xóa địa chỉ -->
    <div class="modal fade" id="deleteAddressModal" tabindex="-1" aria-labelledby="deleteAddressModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4">
          <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Đóng"></button>
          <img src="https://cdn-icons-png.flaticon.com/512/6861/6861362.png" alt="delete" style="width:90px;margin:0 auto 16px;">
          <h5 class="mb-2 mt-2 text-danger fw-bold" id="deleteAddressModalLabel">Xác nhận xóa địa chỉ</h5>
          <div class="mb-3">Bạn chắc chắn muốn xóa địa chỉ này ra khỏi sổ địa chỉ?</div>
          <form id="deleteAddressForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="d-flex justify-content-center gap-3">
              <button type="button" class="btn btn-outline-danger px-4" data-bs-dismiss="modal">Trở về</button>
              <button type="submit" class="btn btn-danger px-4">Xóa</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const provinceSelect = document.getElementById('old_province_code');
    const districtSelect = document.getElementById('old_district_code');
    const wardSelect = document.getElementById('old_ward_code');
    const addressModal = document.getElementById('addressModal');
    const addressForm = document.getElementById('addressForm');

    // AJAX submit cho form địa chỉ + validate client-side tất cả các trường required
    addressForm.addEventListener('submit', function(e) {
        e.preventDefault();
        // Xóa lỗi cũ
        addressForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        addressForm.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

        let hasError = false;
        // Validate client-side các trường required
        const requiredFields = [
            {name: 'full_name', message: 'Vui lòng nhập họ tên.'},
            {name: 'phone_number', message: 'Vui lòng nhập số điện thoại.'},
            {name: 'old_province_code', message: 'Vui lòng chọn tỉnh/thành phố!'},
            {name: 'old_district_code', message: 'Vui lòng chọn quận/huyện!'},
            {name: 'old_ward_code', message: 'Vui lòng chọn xã/phường!'},
            {name: 'address_line1', message: 'Vui lòng nhập địa chỉ cụ thể!'}
        ];
        requiredFields.forEach(field => {
            const input = addressForm.querySelector(`[name="${field.name}"]`);
            if (input) {
                let value = input.value;
                if (input.tagName === 'INPUT' || input.tagName === 'TEXTAREA') {
                    value = value.trim();
                }
                if (!value) {
                    input.classList.add('is-invalid');
                    const feedback = input.closest('.position-relative')?.querySelector('.invalid-feedback');
                    if (feedback) feedback.textContent = field.message;
                    hasError = true;
                }
            }
        });
        // Validate số điện thoại định dạng
        const phoneInput = addressForm.querySelector('[name="phone_number"]');
        const phoneValue = phoneInput.value.trim();
        const phonePattern = /^0[0-9]{9}$/;
        if (phoneInput && phoneValue && !phonePattern.test(phoneValue)) {
            phoneInput.classList.add('is-invalid');
            const feedback = phoneInput.closest('.position-relative')?.querySelector('.invalid-feedback');
            if (feedback) feedback.textContent = 'Số điện thoại không đúng định dạng.';
            hasError = true;
        }
        if (hasError) return;
        // Nếu không có lỗi, submit AJAX như cũ
        const formData = new FormData(addressForm);
        let url = addressForm.action;
        let method = addressForm.querySelector('input[name="_method"]')?.value || addressForm.method || 'POST';
        fetch(url, {
            method: method === 'GET' ? 'GET' : 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': formData.get('_token'),
            },
            body: formData
        })
        .then(async response => {
            if (response.ok) {
                window.location.reload();
            } else if (response.status === 422) {
                const data = await response.json();
                if (data.errors) {
                    for (const key in data.errors) {
                        const input = addressForm.querySelector(`[name="${key}"]`);
                        if (input) {
                            input.classList.add('is-invalid');
                            const feedback = input.closest('.position-relative')?.querySelector('.invalid-feedback');
                            if (feedback) feedback.textContent = data.errors[key][0];
                        }
                    }
                }
            } else {
                alert('Đã có lỗi xảy ra.');
            }
        })
        .catch(() => alert('Không thể gửi dữ liệu.'));
    });

    // Load tỉnh khi mở modal
    $('#addressModal').on('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const action = button ? button.getAttribute('data-action') : 'add';
        // Reset form
        addressForm.reset();
        // Xóa input _method nếu có
        const methodInput = addressForm.querySelector('input[name="_method"]');
        if (methodInput) methodInput.remove();
        if (action === 'edit') {
            // Đổi title
            addressModal.querySelector('.modal-title').textContent = 'Sửa địa chỉ';
            // Đổi action form
            addressForm.action = '/addresses/' + button.getAttribute('data-id');
            // Thêm method PUT
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = '_method';
            input.value = 'PUT';
            addressForm.appendChild(input);
            // Fill dữ liệu
            addressForm.full_name.value = button.getAttribute('data-full_name') || '';
            addressForm.phone_number.value = button.getAttribute('data-phone_number') || '';
            // Load tỉnh và set selected
            loadOldProvinces(button.getAttribute('data-old_province_code'));
            // Load huyện và set selected
            loadOldDistricts(button.getAttribute('data-old_province_code'), button.getAttribute('data-old_district_code'));
            // Load xã và set selected
            loadOldWards(button.getAttribute('data-old_district_code'), button.getAttribute('data-old_ward_code'));
            addressForm.address_line1.value = button.getAttribute('data-address_line1') || '';
            addressForm.is_default_shipping.checked = button.getAttribute('data-is_default_shipping') == '1';
        } else {
            addressModal.querySelector('.modal-title').textContent = 'Thêm địa chỉ mới';
            addressForm.action = '/addresses';
            loadOldProvinces();
            districtSelect.innerHTML = '<option value="">Chọn quận/huyện</option>';
            wardSelect.innerHTML = '<option value="">Chọn xã/phường</option>';
            districtSelect.disabled = true;
            wardSelect.disabled = true;
        }
    });

    // Khi chọn tỉnh, load huyện
    provinceSelect.addEventListener('change', function () {
        const provinceCode = this.value;
        if (provinceCode) {
            loadOldDistricts(provinceCode);
            districtSelect.disabled = false;
            wardSelect.innerHTML = '<option value="">Chọn xã/phường</option>';
            wardSelect.disabled = true;
        } else {
            districtSelect.innerHTML = '<option value="">Chọn quận/huyện</option>';
            districtSelect.disabled = true;
            wardSelect.innerHTML = '<option value="">Chọn xã/phường</option>';
            wardSelect.disabled = true;
        }
    });

    // Khi chọn huyện, load xã
    districtSelect.addEventListener('change', function () {
        const districtCode = this.value;
        if (districtCode) {
            loadOldWards(districtCode);
            wardSelect.disabled = false;
        } else {
            wardSelect.innerHTML = '<option value="">Chọn xã/phường</option>';
            wardSelect.disabled = true;
        }
    });

    async function loadOldProvinces(selected = null) {
        try {
            const response = await fetch('/api/locations/old/provinces');
            const data = await response.json();
            provinceSelect.innerHTML = '<option value="">Chọn tỉnh/thành phố</option>';
            if (data.success && Array.isArray(data.data)) {
                data.data.forEach(function (province) {
                    const option = document.createElement('option');
                    option.value = province.code;
                    option.textContent = province.name_with_type;
                    if (selected && province.code == selected) option.selected = true;
                    provinceSelect.appendChild(option);
                });
            }
        } catch (e) {
            provinceSelect.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
        }
    }

    async function loadOldDistricts(provinceCode, selected = null) {
        if (!provinceCode) {
            districtSelect.innerHTML = '<option value="">Chọn quận/huyện</option>';
            districtSelect.disabled = true;
            return;
        }
        try {
            districtSelect.innerHTML = '<option value="">Đang tải...</option>';
            const response = await fetch(`/api/locations/old/districts/${provinceCode}`);
            const data = await response.json();
            districtSelect.innerHTML = '<option value="">Chọn quận/huyện</option>';
            if (data.success && Array.isArray(data.data)) {
                data.data.forEach(function (district) {
                    const option = document.createElement('option');
                    option.value = district.code;
                    option.textContent = district.name_with_type;
                    if (selected && district.code == selected) option.selected = true;
                    districtSelect.appendChild(option);
                });
            }
            districtSelect.disabled = false;
        } catch (e) {
            districtSelect.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
        }
    }

    async function loadOldWards(districtCode, selected = null) {
        if (!districtCode) {
            wardSelect.innerHTML = '<option value="">Chọn xã/phường</option>';
            wardSelect.disabled = true;
            return;
        }
        try {
            wardSelect.innerHTML = '<option value="">Đang tải...</option>';
            const response = await fetch(`/api/locations/old/wards/${districtCode}`);
            const data = await response.json();
            wardSelect.innerHTML = '<option value="">Chọn xã/phường</option>';
            if (data.success && Array.isArray(data.data)) {
                data.data.forEach(function (ward) {
                    const option = document.createElement('option');
                    option.value = ward.code;
                    option.textContent = ward.name_with_type;
                    if (selected && ward.code == selected) option.selected = true;
                    wardSelect.appendChild(option);
                });
            }
            wardSelect.disabled = false;
        } catch (e) {
            wardSelect.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
        }
    }

    // Xử lý nút Xóa
    document.querySelectorAll('.btn-delete-address').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var modal = new bootstrap.Modal(document.getElementById('deleteAddressModal'));
            var form = document.getElementById('deleteAddressForm');
            form.action = btn.getAttribute('data-action');
            modal.show();
        });
    });
});
</script>
@endsection