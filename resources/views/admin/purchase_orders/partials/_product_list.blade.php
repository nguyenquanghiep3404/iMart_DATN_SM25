<div class="card">
    <div class="p-6">
         <h3 class="text-lg font-semibold text-gray-800 mb-4">Chi Tiết Phiếu Nhập</h3>
         <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 w-[40%]">Sản Phẩm</th>
                        <th scope="col" class="px-4 py-3 text-center">Tồn Kho</th>
                        <th scope="col" class="px-4 py-3 w-[15%]">Số Lượng Nhập</th>
                        <th scope="col" class="px-4 py-3 w-[20%]">Giá Nhập (VNĐ)</th>
                        <th scope="col" class="px-4 py-3 text-right">Thành Tiền</th>
                        <th scope="col" class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody id="purchase-items-table">
                    <tr id="no-items-row">
                        <td colspan="6" class="text-center py-10 text-gray-500">
                            Chưa có sản phẩm nào được thêm.
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="font-semibold text-gray-900">
                        <th scope="row" colspan="4" class="px-4 py-3 text-base text-right">Tổng cộng</th>
                        <td id="grand-total" class="px-4 py-3 text-base text-right">0 ₫</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
