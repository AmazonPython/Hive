<?php

namespace App\Exceptions;


use Exception;
use Illuminate\Http\Request;

class InternalException extends Exception
{
    protected $msgForUser;

    // 定义一个构造函数
    public function __construct(String $message, String $msgForUser = '系统内部错误', int $code = 500)
    {
        // 调用父类的构造函数
        parent::__construct($message, $code);
        $this->msgForUser = $msgForUser;
    }

    public function render(Request $request)
    {
        if ($request->expectsJson()) {
            // json() 方法第二个参数就是 HTTP 状态码
            return response()->json(['msg' => $this->msgForUser], $this->code);
        }

        return view('pages.error', ['msg' => $this->msgForUser]);
    }
}
