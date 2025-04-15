<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shoppingcart extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function product(){
        return $this->belongsTo(Product::class);
    }

    const STATUS  = [
        'pending'       => 0,
        'in_process'    => 1,
        'success'       => 2,
        'error'         => 3
    ];
    public static function GetCartItems()
    {
        return  Shoppingcart::with('product')
        ->where(['user_id'=>auth()->user()->id])
        ->where('status', '!=', Shoppingcart::STATUS['success'])
        ->paginate(7);
    }
}
