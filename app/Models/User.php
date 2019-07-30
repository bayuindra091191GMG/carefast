<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 17 Jul 2019 13:41:11 +0700.
 */

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

/**
 * Class User
 *
 * @property int $id
 * @property string $name
 * @property string $last_name
 * @property string $email
 * @property int $category_id
 * @property string $password
 * @property string $image_path
 * @property string $email_token
 * @property string $phone
 * @property int $status_id
 * @property string $tax_no
 * @property \Carbon\Carbon $email_verified_at
 * @property string $remember_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property \App\Models\Status $status
 * @property \App\Models\UserCategory $user_categories
 * @property \Illuminate\Database\Eloquent\Collection $addresses
 * @property \Illuminate\Database\Eloquent\Collection $fcm_token_apps
 * @property \Illuminate\Database\Eloquent\Collection $orders
 * @property \Illuminate\Database\Eloquent\Collection $product_reviews
 *
 * @package App\Models
 */
class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

	protected $casts = [
		'status_id' => 'int',
		'category_id' => 'int',
		'company_id' => 'int',
	];

	protected $dates = [
		'email_verified_at'
	];

	protected $hidden = [
		'password',
		'email_token',
		'remember_token'
	];

	protected $fillable = [
		'name',
        'category_id',
		'email',
		'password',
		'image_path',
		'email_token',
		'phone',
		'status_id',
		'tax_no',
		'email_verified_at',
		'remember_token',
	];

	public function status()
	{
		return $this->belongsTo(\App\Models\Status::class);
	}

	public function user_categories()
	{
		return $this->belongsTo(\App\Models\UserCategory::class);
	}

	public function addresses()
	{
		return $this->hasMany(\App\Models\Address::class);
	}

	public function fcm_token_apps()
	{
		return $this->hasMany(\App\Models\FcmTokenApp::class);
	}

	public function orders()
	{
		return $this->hasMany(\App\Models\Order::class);
	}

	public function product_reviews()
	{
		return $this->hasMany(\App\Models\ProductReview::class);
	}
}
