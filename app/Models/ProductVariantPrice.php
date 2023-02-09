<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantPrice extends Model
{
    protected $guarded = [];

    public function product_variant_one_details()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_one', 'id');
    }

    public function product_variant_two_details()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_two', 'id');
    }

    public function product_variant_three_details()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_three', 'id');
    }
}
