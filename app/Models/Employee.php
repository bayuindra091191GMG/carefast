<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 22 Aug 2019 15:07:46 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Employee
 *
 * @property int $id
 * @property string $code
 * @property int $employee_role_id
 * @property string $first_name
 * @property string $last_name
 * @property string $telephone
 * @property string $phone
 * @property \Carbon\Carbon $dob
 * @property string $nik
 * @property string $address
 * @property string $notes
 * @property string $image_path
 * @property int $status_id
 * @property int $user_id
 * @property int $created_by
 * @property \Carbon\Carbon $created_at
 * @property int $updated_by
 * @property \Carbon\Carbon $updated_at
 *
 * @property \App\Models\AdminUser $admin_user
 * @property \App\Models\EmployeeRole $employee_role
 * @property \App\Models\Status $status
 * @property \App\Models\User $user
 * @property \Illuminate\Database\Eloquent\Collection $attendances
 * @property \Illuminate\Database\Eloquent\Collection $schedules
 *
 * @package App\Models
 */
class Employee extends Eloquent
{
	protected $casts = [
		'employee_role_id' => 'int',
		'status_id' => 'int',
		'user_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $dates = [
		'dob'
	];

	protected $fillable = [
		'code',
		'employee_role_id',
		'first_name',
		'last_name',
		'telephone',
		'phone',
		'dob',
		'nik',
		'address',
		'notes',
		'image_path',
		'status_id',
		'user_id',
		'created_by',
		'updated_by'
	];

	public function admin_user()
	{
		return $this->belongsTo(\App\Models\AdminUser::class, 'updated_by');
	}

	public function employee_role()
	{
		return $this->belongsTo(\App\Models\EmployeeRole::class);
	}

	public function status()
	{
		return $this->belongsTo(\App\Models\Status::class, 'status_id');
	}

	public function user()
	{
		return $this->belongsTo(\App\Models\User::class);
	}

	public function attendances()
	{
		return $this->hasMany(\App\Models\Attendance::class);
	}

	public function schedules()
	{
		return $this->hasMany(\App\Models\Schedule::class);
	}
}
