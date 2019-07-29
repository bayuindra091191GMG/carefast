<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 29 Jul 2019 15:25:48 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class SalesOrderDetail
 * 
 * @property int $id
 * @property int $header_id
 * @property int $product_id
 * @property string $product_name
 * @property string $product_sku
 * @property float $price
 * @property int $qty
 * @property float $subtotal
 * @property string $description
 * 
 * @property \App\Models\SalesOrderHeader $sales_order_header
 * @property \App\Models\Product $product
 *
 * @package App\Models
 */
class SalesOrderDetail extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'header_id' => 'int',
		'product_id' => 'int',
		'price' => 'float',
		'qty' => 'int',
		'subtotal' => 'float'
	];

	protected $fillable = [
		'header_id',
		'product_id',
		'product_name',
		'product_sku',
		'price',
		'qty',
		'subtotal',
		'description'
	];

	public function sales_order_header()
	{
		return $this->belongsTo(\App\Models\SalesOrderHeader::class, 'header_id');
	}

	public function product()
	{
		return $this->belongsTo(\App\Models\Product::class);
	}
}
