<?php

/**
 * Created by Reliese Model.
 * Date: Tue, 13 Aug 2019 10:26:56 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class FcmTokenAdmin
 * 
 * @property int $id
 * @property int $user_admin_id
 * @property string $token
 * 
 * @property \App\Models\AdminUser $admin_user
 *
 * @package App\Models
 */
class FcmTokenAdmin extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'user_admin_id' => 'int'
	];

	protected $hidden = [
		'token'
	];

	protected $fillable = [
		'user_admin_id',
		'token'
	];

	public function admin_user()
	{
		return $this->belongsTo(\App\Models\AdminUser::class, 'user_admin_id');
	}
}
