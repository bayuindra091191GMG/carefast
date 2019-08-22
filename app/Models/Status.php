<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 22 Aug 2019 15:31:21 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Status
 * 
 * @property int $id
 * @property string $description
 * 
 * @property \Illuminate\Database\Eloquent\Collection $actions
 * @property \Illuminate\Database\Eloquent\Collection $admin_users
 * @property \Illuminate\Database\Eloquent\Collection $attendance_details
 * @property \Illuminate\Database\Eloquent\Collection $attendances
 * @property \Illuminate\Database\Eloquent\Collection $banners
 * @property \Illuminate\Database\Eloquent\Collection $customers
 * @property \Illuminate\Database\Eloquent\Collection $employees
 * @property \Illuminate\Database\Eloquent\Collection $places
 * @property \Illuminate\Database\Eloquent\Collection $schedules
 * @property \Illuminate\Database\Eloquent\Collection $units
 * @property \Illuminate\Database\Eloquent\Collection $users
 *
 * @package App\Models
 */
class Status extends Eloquent
{
	public $timestamps = false;

	protected $fillable = [
		'description'
	];

	public function actions()
	{
		return $this->hasMany(\App\Models\Action::class);
	}

	public function admin_users()
	{
		return $this->hasMany(\App\Models\AdminUser::class);
	}

	public function attendance_details()
	{
		return $this->hasMany(\App\Models\AttendanceDetail::class);
	}

	public function attendances()
	{
		return $this->hasMany(\App\Models\Attendance::class);
	}

	public function banners()
	{
		return $this->hasMany(\App\Models\Banner::class);
	}

	public function customers()
	{
		return $this->hasMany(\App\Models\Customer::class);
	}

	public function employees()
	{
		return $this->hasMany(\App\Models\Employee::class);
	}

	public function places()
	{
		return $this->hasMany(\App\Models\Place::class);
	}

	public function schedules()
	{
		return $this->hasMany(\App\Models\Schedule::class);
	}

	public function units()
	{
		return $this->hasMany(\App\Models\Unit::class);
	}

	public function users()
	{
		return $this->hasMany(\App\Models\User::class);
	}
}
