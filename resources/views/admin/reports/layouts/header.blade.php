<header class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Báo cáo Tồn kho Chi tiết</h1>
        <p class="text-gray-600 mt-1">Xem và lọc dữ liệu tồn kho trên toàn hệ thống.</p>
    </div>
    <button id="export-btn"
        class="flex-shrink-0 flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg shadow-sm hover:bg-green-700 transition-colors duration-200">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
        </svg>
        <span>Xuất file Excel</span>
    </button>
</header>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('export-btn').addEventListener('click', function() {
        const params = new URLSearchParams(window.location.search).toString();
        const url = `/admin/reports/inventory/export?${params}`;

        fetch(url, {
                method: 'GET'
            })
            .then(response => {
                if (!response.ok) throw new Error('Lỗi khi xuất file');

                // Lưu response để dùng lấy header filename sau
                return response.blob().then(blob => ({
                    blob: blob,
                    response: response
                }));
            })
            .then(({
                blob,
                response
            }) => {
                const downloadUrl = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = downloadUrl;

                // Lấy tên file từ header Content-Disposition
                const disposition = response.headers.get('Content-Disposition');
                let fileName = 'export.xlsx';
                if (disposition && disposition.indexOf('filename=') !== -1) {
                    const fileNameMatch = disposition.match(/filename="?(.+)"?/);
                    if (fileNameMatch.length === 2) fileName = fileNameMatch[1];
                }
                a.download = fileName;

                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(downloadUrl);

                Swal.fire({
                    icon: 'success',
                    title: 'Thành công',
                    text: 'Xuất file Excel thành công!',
                    timer: 2000,
                    showConfirmButton: false
                });
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: 'Xuất file thất bại!'
                });
                console.error(error);
            });
    });
</script>
