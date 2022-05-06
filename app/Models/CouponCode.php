<?php

namespace App\Models;

use App\Exceptions\CouponCodeUnavailableException;
use Carbon\Carbon;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\User;

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

    // 检测优惠券是否可用
    public function checkAvailable(User $user, $orderAmount = null)
    {
        if (!$this->enabled) {
            throw new CouponCodeUnavailableException('优惠券不存在');
        }

        if ($this->total - $this->used <= 0) {
            throw new CouponCodeUnavailableException('该优惠券已被兑完');
        }

        if ($this->not_before && $this->not_before->gt(Carbon::now())) {
            throw new CouponCodeUnavailableException('该优惠券现在还不能使用');
        }

        if ($this->not_after && $this->not_after->lt(Carbon::now())) {
            throw new CouponCodeUnavailableException('该优惠券已过期');
        }

        if (!is_null($orderAmount) && $orderAmount < $this->min_amount) {
            throw new CouponCodeUnavailableException('订单金额不满足该优惠券最低金额');
        }

        $used = Order::where('user_id', $user->id)
            ->where('coupon_code_id', $this->id)
            ->where(function($query) {
                $query->where(function($query) {
                    $query->whereNull('paid_at')
                        ->where('closed', false);
                })->orWhere(function($query) {
                    $query->whereNotNull('paid_at')
                        ->where('refund_status', '!=', Order::REFUND_STATUS_SUCCESS);
                });
            })
            ->exists();

        if ($used) {
            throw new CouponCodeUnavailableException('你已经使用过这张优惠券了');
        }
    }

    // 计算使用优惠券后的实际金额
    public function getAdjustedPrice($orderAmount)
    {
        // 固定金额
        if ($this->type === self::TYPE_FIXED) {
            // 为了保证系统健壮性，我们需要订单金额最少为 0.01 元
            return max(0.01, $orderAmount - $this->value);
        }

        return number_format($orderAmount * (100 - $this->value) / 100, 2, '.', '');
    }

    // 更改优惠券的使用次数
    public function changeUsed($increase = true)
    {
        // 传入 true，则代表增加一张优惠券，否则是减少一张优惠券
        if ($increase) {
            // 与检查 sku 库存类似，这里需要检查当前优惠券是否已经被兑完
            return $this->where('id', $this->id)->where('used', '<', $this->total)->increment('used');
        } else {
            return $this->decrement('used');
        }
    }
}
