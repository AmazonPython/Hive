<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'is_directory', 'level', 'path'];

    protected $casts = ['is_directory' => 'boolean'];

    protected static function boot()
    {
        parent::boot();

        // 监听 category 的创建事件，用于初始化 level 和 path 的字段值
        static::creating(function (Category $category) {
            // 如果创建的是根类目
            if (is_null($category->parent_id)) {
                // 将层级设为 0，path 设为 -
                $category->level = 0;
                $category->path = '-';
            } else {
                // 将层级设为父类目 +1，path 设为父类目的 path 追加父类目 ID，以 '-' 分隔
                $category->level = $category->parent->level + 1;
                $category->path = $category->parent->path . $category->parent_id . '-';
            }
        });
    }

    // 关联父类目
    public function parent()
    {
        return $this->belongsTo(Category::class);
    }

    // 关联到当前类目的商品
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // 关联到商品的属性
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // 定义一个访问器，获取所有祖先类目的 ID
    public function getPathIdsAttribute()
    {
        /*
         * trim($str, '-') 将字符两端的 - 符号去除
         * explode() 将字符串以 - 为分隔切割为数组
         * array_filter 将数组中的空值移除
         */
        return array_filter(explode('-', trim($this->path, '-')));
    }

    // 定义一个访问器，获取所有祖先类目并按层级排序
    public function getAncestorsAttribute()
    {
        return Category::query() // 用上方访问器获取所有祖先类目 ID
            ->where('id', $this->path_ids)
            ->orderBy('level') // 按层级排序
            ->get();
    }

    // 定义一个访问器，获取以 - 为分隔符的所有祖先类目名称和当前类目名称
    public function getFullNameAttribute()
    {
        return $this->ancestors // 获取所有祖先类目
            ->pluck('name') // 取出所有祖先类目的 name 字段作为一个数组
            ->push($this->name) // 将当前类目的 name 字段值加到数组末尾
            ->implode('-'); // 用 - 符号将数组的值组装成一个字符串
    }
}
