<?php

/**
 * Created by Reliese Model.
 * Date: Tue, 13 Aug 2019 10:26:04 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class User
 * 
 * @property int $id
 * @property string $name
 * @property string $position_name
 * @property string $email
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
 * @property \Illuminate\Database\Eloquent\Collection $fcm_token_users
 *
 * @package App\Models
 */
class User extends Eloquent
{
	protected $casts = [
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
		'position_name',
		'email',
		'password',
		'image_path',
		'email_token',
		'phone',
		'status_id',
		'tax_no',
		'email_verified_at',
		'remember_token'
	];

	public function status()
	{
		return $this->belongsTo(\App\Models\Status::class);
	}

	public function fcm_token_users()
	{
		return $this->hasMany(\App\Models\FcmTokenUser::class);
	}
}
