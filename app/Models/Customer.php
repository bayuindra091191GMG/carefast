<?php

/**
 * Created by Reliese Model.
 * Date: Tue, 13 Aug 2019 10:25:51 +0700.
 */

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use SMartins\PassportMultiauth\HasMultiAuthApiTokens;

/**
 * Class Customer
 *
 * @property int $id
 * @property string $name
 * @property int $category_id
 * @property string $email
 * @property string $password
 * @property string $image_path
 * @property string $email_token
 * @property string $phone
 * @property string $device_id
 * @property int $status_id
 * @property string $tax_no
 * @property \Carbon\Carbon $email_verified_at
 * @property string $remember_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property \App\Models\Status $status
 * @property \App\Models\UserCategory $customer_types
 * @property \Illuminate\Database\Eloquent\Collection $fcm_token_customers
 *
 * @package App\Models
 */
class Customer extends Authenticatable
{
    use Notifiable, HasMultiAuthApiTokens;

    public function findForPassport($username)
    {
        return $this->where('phone', $username)->first();
    }

	protected $casts = [
		'id' => 'int',
		'category_id' => 'int',
		'status_id' => 'int'
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
        'device_id'
	];

	public function status()
	{
		return $this->belongsTo(\App\Models\Status::class);
	}

	public function customer_types()
	{
		return $this->belongsTo(\App\Models\CustomerType::class, 'category_id');
	}

	public function fcm_token_customers()
	{
		return $this->hasMany(\App\Models\FcmTokenCustomer::class);
	}
}
