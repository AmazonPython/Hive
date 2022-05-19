<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Services\CategoryService;

class CategoryTreeComposer
{
    /**
     * @var CategoryService
     */
    protected $categoryService;

    /**
     * 使用 Laravel 的依赖注入系统，自动注入需要的 CategoryService 类
     *
     * @param CategoryService $categoryService
     */
    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * 当渲染指定模板时，Laravel 会自动调用 compose 方法，自动注入数据
     * @param $view
     */
    public function compose(View $view)
    {
        $view->with('categoryTree', $this->categoryService->getCategoryTree());
    }
}
