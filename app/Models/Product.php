<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 17 Jul 2019 13:39:33 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Product
 * 
 * @property int $id
 * @property string $type
 * @property string $name
 * @property string $slug
 * @property string $sku
 * @property string $tag
 * @property string $description
 * @property int $status
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
 * @property string $meta_title
 * @property string $meta_description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property \Illuminate\Database\Eloquent\Collection $orders
 * @property \Illuminate\Database\Eloquent\Collection $order_return_products
 * @property \Illuminate\Database\Eloquent\Collection $product_images
 * @property \Illuminate\Database\Eloquent\Collection $product_properties
 * @property \Illuminate\Database\Eloquent\Collection $product_reviews
 * @property \Illuminate\Database\Eloquent\Collection $product_variations
 *
 * @package App\Models
 */
class Product extends Eloquent
{
	protected $casts = [
		'status' => 'int',
		'in_stock' => 'int',
		'track_stock' => 'int',
		'qty' => 'float',
		'is_taxable' => 'int',
		'price' => 'float',
		'cost_price' => 'float',
		'weight' => 'float',
		'width' => 'float',
		'height' => 'float',
		'length' => 'float'
	];

	protected $fillable = [
		'type',
		'name',
		'slug',
		'sku',
		'tag',
		'description',
		'status',
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
		'meta_title',
		'meta_description'
	];

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

	public function product_variations()
	{
		return $this->hasMany(\App\Models\ProductVariation::class, 'variation_id');
	}
}
