<?php

namespace App\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CouponCode extends Model
{
    use HasFactory;
    use DefaultDatetimeFormat;

    // 用常量的方式定义优惠券类型
    const TYPE_FIXED = 'fixed';
    const TYPE_PERCENT = 'percent';

    public static $typeMap = [
        self::TYPE_FIXED   => '固定金额',
        self::TYPE_PERCENT => '比例',
    ];

    protected $fillable = ['name', 'code', 'type', 'value', 'total', 'used', 'min_amount', 'not_before', 'not_after', 'enabled'];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    // 指明两个时间戳字段
    protected $dates = ['not_before', 'not_after'];

    protected $appends = ['description'];

    // 生成测试优惠券码
    public static function findAvailableCode($length = 16)
    {
        do {
            // 生成一个随机的指定长度字符串，并转成大写
            $code = strtoupper(Str::random($length));
            // 如果生成的优惠券码已经存在则重新生成
        } while (self::query()->where('code', $code)->exists());

        return $code;
    }

    // 定义一个访问器，用于获取优惠券类型描述
    public function getDescriptionAttribute()
    {
        $str = '';

        if ($this->min_amount > 0) {
            $str = '满' . str_replace('.00', '', $this->min_amount);
        }
        if ($this->type === self::TYPE_PERCENT) {
            return $str . '优惠' . str_replace('.00', '', $this->value) . '%';
        }

        return $str . '减' . str_replace('.00', '', $this->value);
    }
}
