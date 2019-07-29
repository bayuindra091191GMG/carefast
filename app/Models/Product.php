<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 25 Jul 2019 10:59:00 +0700.
 */

namespace App\Models;

use Carbon\Carbon;
use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Product
 * 
 * @property int $id
 * @property int $category_id
 * @property int $brand_id
 * @property string $name
 * @property string $slug
 * @property string $sku
 * @property string $tag
 * @property string $description
 * @property int $in_stock
 * @property int $track_stock
 * @property float $qty
 * @property int $is_taxable
 * @property float $price
 * @property float $cost_price
 * @property float $weight
 * @property float $width
 * @property float $height
 * @property float $length
 * @property string $external_link
 * @property string $meta_title
 * @property string $meta_description
 * @property int $status_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property \App\Models\ProductBrand $product_brand
 * @property \App\Models\ProductCategory $product_category
 * @property \App\Models\Status $status
 * @property \Illuminate\Database\Eloquent\Collection $orders
 * @property \Illuminate\Database\Eloquent\Collection $order_return_products
 * @property \Illuminate\Database\Eloquent\Collection $product_images
 * @property \Illuminate\Database\Eloquent\Collection $product_properties
 * @property \Illuminate\Database\Eloquent\Collection $product_reviews
 * @property \Illuminate\Database\Eloquent\Collection $product_user_categories
 * @property \Illuminate\Database\Eloquent\Collection $product_variations
 *
 * @package App\Models
 */
class Product extends Eloquent
{
	protected $casts = [
		'category_id' => 'int',
		'brand_id' => 'int',
		'in_stock' => 'int',
		'track_stock' => 'int',
		'qty' => 'float',
		'is_taxable' => 'int',
		'price' => 'float',
		'cost_price' => 'float',
		'weight' => 'float',
		'width' => 'float',
		'height' => 'float',
		'length' => 'float',
		'status_id' => 'int'
	];

	protected $fillable = [
		'category_id',
		'brand_id',
		'name',
		'slug',
		'sku',
		'tag',
		'description',
		'in_stock',
		'track_stock',
		'qty',
		'is_taxable',
		'price',
		'cost_price',
		'weight',
		'width',
		'height',
		'length',
        'external_link',
		'meta_title',
		'meta_description',
		'status_id'
	];

    protected $appends = [
        'created_at_string',
        'price_string',
        'weight_string'
    ];

    public function getCreatedAtStringAttribute(){
        return Carbon::parse($this->attributes['created_at'])->format('d M Y');
    }

    public function getWeightStringAttribute(){
        if(!empty($this->attributes['weight'])){
            return number_format($this->attributes['weight'], 0, ",", ".");
        }
        else{
            return '0';
        }
    }

    public function getPriceStringAttribute(){
        return number_format($this->attributes['price'], 0, ",", ".");
    }

	public function product_brand()
	{
		return $this->belongsTo(\App\Models\ProductBrand::class, 'brand_id');
	}

	public function product_category()
	{
		return $this->belongsTo(\App\Models\ProductCategory::class, 'category_id');
	}

	public function status()
	{
		return $this->belongsTo(\App\Models\Status::class);
	}

	public function orders()
	{
		return $this->belongsToMany(\App\Models\Order::class, 'order_products')
					->withPivot('id', 'qty', 'price', 'tax_amount', 'product_info')
					->withTimestamps();
	}

	public function order_return_products()
	{
		return $this->hasMany(\App\Models\OrderReturnProduct::class);
	}

	public function product_images()
	{
		return $this->hasMany(\App\Models\ProductImage::class);
	}

	public function product_properties()
	{
		return $this->hasMany(\App\Models\ProductProperty::class);
	}

	public function product_reviews()
	{
		return $this->hasMany(\App\Models\ProductReview::class);
	}

	public function product_user_categories()
	{
		return $this->hasMany(\App\Models\ProductUserCategory::class);
	}

	public function product_variations()
	{
		return $this->hasMany(\App\Models\ProductVariation::class, 'variation_id');
	}

    public function sales_order_details()
    {
        return $this->hasMany(\App\Models\SalesOrderDetail::class, 'product_id');
    }
}
