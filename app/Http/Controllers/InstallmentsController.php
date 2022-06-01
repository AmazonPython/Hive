<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use Illuminate\Http\Request;

class InstallmentsController extends Controller
{
    // 获取分期付款列表
    public function index(Request $request)
    {
        $installments = Installment::query()
            ->where('user_id', $request->user()->id)
            ->paginate(10);

        return view('installments.index', compact('installments'));
    }

    // 获取分期付款详情
    public function show(Installment $installment)
    {
        // 校验是否属于当前用户
        $this->authorize('own', $installment);

        // 获取分期付款对应的商品，并按照分期期数排序
        $items = $installment->items()->orderBy('sequence')->get();

        // 下一个未完成的还款计划
        $nextItem = $items->where('sequence', $installment->next_sequence)->first();

        return view('installments.show', compact('installment', 'items', 'nextItem'));
    }
}
