<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 16 Sep 2019 13:20:47 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Project
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $latitude
 * @property string $longitude
 * @property string $customer_id
 * @property string $phone
 * @property string $address
 * @property string $description
 * @property string $image_path
 * @property \Carbon\Carbon $start_date
 * @property \Carbon\Carbon $finish_date
 * @property int $total_manday
 * @property int $total_mp_onduty
 * @property int $total_mp_off
 * @property int $total_manpower
 * @property int $status_id
 * @property string $City
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 * @property int $total_manpower_used
 *
 * @property \App\Models\Customer $customer
 * @property \App\Models\Status $status
 * @property \App\Models\AdminUser $admin_user
 * @property \Illuminate\Database\Eloquent\Collection $customer_complaints
 * @property \Illuminate\Database\Eloquent\Collection $employees
 * @property \Illuminate\Database\Eloquent\Collection $project_objects
 * @property \Illuminate\Database\Eloquent\Collection $project_employees
 * @property \Illuminate\Database\Eloquent\Collection $schedules
 *
 * @package App\Models
 */
class Project extends Eloquent
{
	protected $casts = [
		'total_manday' => 'int',
		'total_mp_onduty' => 'int',
		'total_mp_off' => 'int',
		'total_manpower' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int',
		'total_manpower_used' => 'int'
	];

	protected $dates = [
		'start_date',
		'finish_date'
	];

	protected $fillable = [
		'name',
		'code',
		'latitude',
		'longitude',
		'customer_id',
		'phone',
		'address',
		'description',
		'image_path',
		'start_date',
		'finish_date',
		'total_manday',
		'total_mp_onduty',
		'total_mp_off',
		'total_manpower',
		'status_id',
		'city',
		'created_by',
		'updated_by',
		'total_manpower_used'
	];

    protected $appends = [
        'total_manpower_string'
    ];

    public function getTotalManpowerStringAttribute(){
        return number_format($this->attributes['total_manpower'], 0, ",", ".");
    }

	public function customer()
	{
		return $this->belongsTo(\App\Models\Customer::class);
	}

	public function status()
	{
		return $this->belongsTo(\App\Models\Status::class);
	}

	public function admin_user()
	{
		return $this->belongsTo(\App\Models\AdminUser::class, 'updated_by');
	}

	public function customer_complaints()
	{
		return $this->hasMany(\App\Models\CustomerComplaint::class);
	}

	public function employees()
	{
		return $this->belongsToMany(\App\Models\Employee::class, 'project_employees')
					->withPivot('id', 'employee_roles_id', 'status_id', 'created_by', 'updated_by')
					->withTimestamps();
	}

	public function project_objects()
	{
		return $this->hasMany(\App\Models\ProjectObject::class);
	}

	public function project_employees()
	{
		return $this->hasMany(\App\Models\ProjectEmployee::class);
	}

	public function schedules()
	{
		return $this->hasMany(\App\Models\Schedule::class);
	}
}
