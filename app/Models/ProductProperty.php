<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductProperty extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'value'];

    // 没有自动维护时间戳
    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
