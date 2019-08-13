<?php

/**
 * Created by Reliese Model.
 * Date: Tue, 13 Aug 2019 10:26:44 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class FcmTokenUser
 * 
 * @property int $id
 * @property int $user_id
 * @property string $token
 * 
 * @property \App\Models\User $user
 *
 * @package App\Models
 */
class FcmTokenUser extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'user_id' => 'int'
	];

	protected $hidden = [
		'token'
	];

	protected $fillable = [
		'user_id',
		'token'
	];

	public function user()
	{
		return $this->belongsTo(\App\Models\User::class);
	}
}
