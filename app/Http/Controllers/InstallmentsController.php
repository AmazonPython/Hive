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
}
