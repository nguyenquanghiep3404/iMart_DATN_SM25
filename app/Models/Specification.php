<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
class Specification extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'specification_group_id',
        'name',
        'type',
        'description',
        'order',
    ];

    /**
     * Lấy nhóm mà thông số này thuộc về.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(SpecificationGroup::class, 'specification_group_id');
    }

    /**
     * Lấy các biến thể sản phẩm có sử dụng thông số này.
     * Đây là cách tiếp cận chuẩn để lấy cả giá trị thông số.
     */
    public function productVariants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'product_specification_values')
                    ->withPivot('value') // Lấy thêm cột 'value' từ bảng trung gian
                    ->withTimestamps();
    }
}