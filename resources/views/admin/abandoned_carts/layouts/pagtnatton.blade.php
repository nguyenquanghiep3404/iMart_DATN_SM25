<div class="card-custom-footer">
    <div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center w-full">
        <p class="text-sm text-gray-700 leading-5">
            Hiển thị từ <span class="font-medium">{{ $abandonedCarts->firstItem() }}</span> đến
            <span class="font-medium">{{ $abandonedCarts->lastItem() }}</span> trên tổng số
            <span class="font-medium">{{ $abandonedCarts->total() }}</span> kết quả
        </p>
        <div>
            {{ $abandonedCarts->links() }}
        </div>
    </div>
</div>
