<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon; // Đảm bảo đã import Carbon

class FlashSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'start_time',
        'end_time',
        'banner_image_url',
        'status',
    ];

    // Thay thế protected $dates bằng protected $casts để Laravel tự động chuyển đổi cột
    // 'datetime' là cách hiện đại và ưu việt hơn cho việc quản lý các trường ngày/giờ
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * Đồng bộ trạng thái của Flash Sale dựa trên thời gian bắt đầu và kết thúc.
     * Phương thức này sẽ cập nhật trạng thái trong database nếu cần.
     *
     * @return void
     */
    public function syncStatusBasedOnTime()
    {
        $now = Carbon::now(); // Lấy thời gian hiện tại

        // Kiểm tra và cập nhật từ 'scheduled' sang 'active'
        // Nếu chiến dịch đang ở trạng thái 'scheduled' và thời gian bắt đầu đã đến hoặc đã qua
        if ($this->status === 'scheduled' && $this->start_time->lte($now)) {
            $this->status = 'active';
            $this->save(); // Lưu thay đổi vào database
            return; // Đã cập nhật, thoát sớm
        }

        // Kiểm tra và cập nhật từ 'active' sang 'finished'
        // Nếu chiến dịch đang ở trạng thái 'active' và thời gian kết thúc đã qua
        // Sử dụng 'lt' để đảm bảo nó kết thúc hẳn sau thời gian end_time
        if ($this->status === 'active' && $this->end_time->lt($now)) {
            $this->status = 'finished';
            $this->save(); // Lưu thay đổi vào database
            return; // Đã cập nhật, thoát sớm
        }

        // Không làm gì nếu trạng thái là 'finished', 'inactive',
        // hoặc nếu nó vẫn đang 'scheduled' và chưa đến start_time,
        // hoặc nếu nó vẫn đang 'active' và chưa đến end_time.
    }

    public function products()
    {
        return $this->hasMany(FlashSaleProduct::class);
    }

    public function flashSaleProducts()
    {
        return $this->hasMany(FlashSaleProduct::class);
    }

    public function flashSaleTimeSlots()
    {
        return $this->hasMany(FlashSaleTimeSlot::class);
    }

    // Nếu bạn có ProductVariant model, có thể dùng hasManyThrough (tuỳ mục đích)
}
