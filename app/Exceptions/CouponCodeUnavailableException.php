<?php

namespace App\Exceptions;

use Exception;

class CouponCodeUnavailableException extends Exception
{
    public function __construct($message, int $code = 403)
    {
        parent::__construct($message, $code);
    }

    // 当这个异常被捕获时，会自动调用 render 方法输出异常信息
    public function render()
    {
        // 如果用户通过 API 请求，则返回 JSON
        if (request()->expectsJson()) {
            return response()->json(['message' => $this->message()], $this->code());
        }
        // 否则返回上一页并显示异常信息
        return redirect()->back()->withErrors(['coupon_code' => $this->message()]);
    }
}
