<?php

/**
 * Created by Reliese Model.
 * Date: Tue, 20 Aug 2019 10:35:38 +0700.
 */

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

/**
 * Class User
 *
 * @property int $id
 * @property int $employee_id
 * @property string $name
 * @property string $position_name
 * @property string $email
 * @property string $password
 * @property string $image_path
 * @property string $email_token
 * @property string $phone
 * @property string $device_id
 * @property string $android_id
 * @property string $first_imei
 * @property string $second_imei
 * @property int $status_id
 * @property string $tax_no
 * @property \Carbon\Carbon $email_verified_at
 * @property string $remember_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property \App\Models\Employee $employee
 * @property \App\Models\Status $status
 * @property \Illuminate\Database\Eloquent\Collection $employees
 * @property \Illuminate\Database\Eloquent\Collection $fcm_token_users
 *
 * @package App\Models
 */
class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    public function findForPassport($username)
    {
        return $this->where('phone', $username)->first();
    }

	protected $casts = [
        'employee_id' => 'int',
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
	    'employee_id',
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
		'remember_token',
		'device_id',
		'android_id',
		'first_imei',
        'second_imei'
	];

	public function status()
	{
		return $this->belongsTo(\App\Models\Status::class);
	}

	public function employee()
	{
		return $this->belongsTo(\App\Models\Employee::class);
	}

	public function fcm_token_users()
	{
		return $this->hasMany(\App\Models\FcmTokenUser::class);
	}
}
