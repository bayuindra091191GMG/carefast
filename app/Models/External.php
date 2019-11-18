<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 18 Nov 2019 11:52:37 +0700.
 */

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use SMartins\PassportMultiauth\HasMultiAuthApiTokens;

/**
 * Class External
 * 
 * @property int $id
 * @property string $email
 * @property string $name
 * @property string $password
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @package App\Models
 */
class External extends Authenticatable
{
    use Notifiable, HasMultiAuthApiTokens;

	protected $hidden = [
		'password'
	];

	protected $fillable = [
		'email',
		'name',
		'password'
	];
}
