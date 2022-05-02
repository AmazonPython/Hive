<?php

namespace App\Http\Controllers;

use App\Models\CouponCode;
use Carbon\Carbon;

class CouponCodesController extends Controller
{
    public function show($code)
    {
        // 判断优惠券是否存在
        if (!$record = CouponCode::where('code', $code)->first()) {
            return response()->json(['message' => '优惠券不存在'], 404);
        }

        // 如果优惠券没有启用，则等同于不存在
        if (!$record->enabled) {
            return response()->json(['message' => '优惠券不存在'], 404);
        }

        // 判断优惠券是否用完
        if ($record->total - $record->used <= 0) {
            return response()->json(['msg' => '该优惠券已被兑完'], 403);
        }

        // 判断优惠券是否还未开始
        if ($record->not_before && $record->not_before->gt(Carbon::now())) {
            return response()->json(['msg' => '该优惠券现在还不能使用'], 403);
        }

        // 判断优惠券是否已经过期
        if ($record->not_after && $record->not_after->lt(Carbon::now())) {
            return response()->json(['msg' => '该优惠券已过期'], 403);
        }

        return $record;
    }
}
