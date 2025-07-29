<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('posManager', (registersData, locationsData) => ({
            registers: [],
            locations: locationsData,
            filteredRegisters: [],
            formData: {
                id: null,
                name: '',
                store_location_id: '',
                device_uid: '',
                status: 'active',
            },
            isModalOpen: false,
            isEditMode: false,
            isActive: true,

            init() {
                // Gắn thêm tên cửa hàng vào từng register nếu chưa có
                this.registers = registersData.map(reg => ({
                    ...reg,
                    store_location_name: reg.store_location_name || this
                        .getLocationName(reg.store_location_id)
                }));
                this.filteredRegisters = this.registers;
            },

            openModal(register = null) {
                this.isModalOpen = true;
                if (register) {
                    this.isEditMode = true;
                    this.formData = {
                        ...register
                    };
                    this.isActive = register.status === 'active';
                } else {
                    this.isEditMode = false;
                    this.resetForm();
                }
            },

            closeModal() {
                this.isModalOpen = false;
                this.resetForm();
            },

            resetForm() {
                this.formData = {
                    id: null,
                    name: '',
                    store_location_id: '',
                    device_uid: '',
                    status: 'active',
                };
                this.isActive = true;
            },

            getLocationName(id) {
                const location = this.locations.find(loc => loc.id === id);
                return location ? location.name : 'N/A';
            },

            saveRegister() {
                this.formData.status = this.isActive ? 'active' : 'inactive';

                if (this.formData.device_uid && isNaN(this.formData.device_uid)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi',
                        text: 'Device UID phải là số!'
                    });
                    return; // Dừng lại nếu sai
                }

                fetch(`{{ route('admin.registers.save') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content,
                            'Accept': 'application/json' // Thêm để Laravel trả về JSON lỗi validation
                        },
                        body: JSON.stringify(this.formData)
                    })
                    .then(res => {
                        if (!res.ok) return res.json().then(err => Promise.reject(err));
                        return res.json();
                    })
                    .then(data => {
                        // Nếu API chưa trả về store_location_name thì gán fallback
                        if (!data.data.store_location_name) {
                            data.data.store_location_name = this.getLocationName(data.data
                                .store_location_id);
                        }

                        if (this.isEditMode) {
                            const index = this.registers.findIndex(r => r.id === data.data.id);
                            if (index !== -1) {
                                this.registers[index] = {
                                    ...data.data
                                }; // đảm bảo reactive
                            }
                        } else {
                            this.registers.push(data.data);
                        }

                        this.filteredRegisters = this.registers;

                        Swal.fire({
                            icon: 'success',
                            title: 'Thành công',
                            text: data.message || 'Đã lưu máy POS thành công!'
                        });

                        this.closeModal();
                    })
                    .catch(err => {
                        if (err.errors) {
                            if (err.errors.name) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Lỗi',
                                    text: err.errors.name[
                                        0] // Thông báo lỗi trùng tên máy
                                });
                            } else {
                                Object.values(err.errors).forEach(e => {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Lỗi',
                                        text: e[0]
                                    });
                                });
                            }
                        } else if (err.message) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Lỗi',
                                text: err.message
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Lỗi',
                                text: 'Đã xảy ra lỗi khi lưu!'
                            });
                        }
                    });
            },



            editRegister(register) {
                this.openModal(register);
            },

            deleteRegister(register) {
                Swal.fire({
                    title: 'Xác nhận xoá',
                    text: 'Bạn có chắc chắn muốn xoá máy POS này không?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Xoá',
                    cancelButtonText: 'Hủy',
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    fetch(`/admin/registers/${register.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content
                            }
                        })
                        .then(res => {
                            if (!res.ok) return res.json().then(err => Promise.reject(
                                err));
                            return res.json();
                        })
                        .then(data => {
                            this.registers = this.registers.filter(r => r.id !==
                                register.id);
                            this.filteredRegisters = this.registers;

                            Swal.fire({
                                icon: 'success',
                                title: 'Đã xoá',
                                text: data.message || 'Xoá thành công!'
                            });
                        })
                        .catch(() => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Lỗi',
                                text: 'Không thể xoá máy POS!'
                            });
                        });
                });
            },
        }));
    });
    document.addEventListener('alpine:init', () => {
        Alpine.data('registerManager', () => ({
            deleteRegister(register) {
                Swal.fire({
                    title: 'Bạn có chắc?',
                    text: `Bạn có chắc muốn xóa máy POS: "${register.name}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Xóa',
                    cancelButtonText: 'Hủy',
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    fetch(`{{ route('admin.registers.destroy', ':id') }}`.replace(':id',
                            register.id), {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute(
                                    'content'),
                                'Accept': 'application/json',
                            },
                        })
                        .then(response => response.json())
                        .then(data => {
                            Swal.fire({
                                icon: data.success ? 'success' : 'error',
                                title: data.success ? 'Thành công' : 'Lỗi',
                                text: data.message,
                            });

                            if (data.success) {
                                // Nếu dùng mảng danh sách: this.registers = this.registers.filter(...)
                                location
                                    .reload(); // Hoặc cập nhật UI nếu không muốn reload
                            }
                        })
                        .catch(error => {
                            console.error('Lỗi khi xoá:', error);
                            Swal.fire('Lỗi', 'Có lỗi xảy ra khi xoá máy POS.', 'error');
                        });
                });
            }
        }));
    });
</script>
