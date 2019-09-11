<?php

/**
 * Created by Reliese Model.
 * Date: Fri, 06 Sep 2019 11:03:54 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Project
 *
 * @property int $id
 * @property string $name
 * @property string $latitude
 * @property string $longitude
 * @property int $customer_id
 * @property string $phone
 * @property string $address
 * @property string $description
 * @property \Carbon\Carbon $start_date
 * @property \Carbon\Carbon $finish_date
 * @property int $total_manday
 * @property int $total_mp_onduty
 * @property int $total_mp_off
 * @property int $total_manpower
 * @property int $total_manpower_used
 * @property int $status_id
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 *
 * @property \App\Models\Customer $customer
 * @property \App\Models\Status $status
 * @property \App\Models\AdminUser $admin_user
 * @property \Illuminate\Database\Eloquent\Collection $project_objects
 *
 * @package App\Models
 */
class Project extends Eloquent
{
	protected $casts = [
		'customer_id' => 'int',
		'total_manday' => 'int',
		'total_mp_onduty' => 'int',
		'total_mp_off' => 'int',
		'total_manpower' => 'int',
        'total_manpower_used' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $dates = [
		'start_date',
		'finish_date'
	];

	protected $fillable = [
		'name',
		'latitude',
		'longitude',
		'customer_id',
		'phone',
		'address',
		'description',
		'start_date',
		'finish_date',
		'total_manday',
		'total_mp_onduty',
		'total_mp_off',
		'total_manpower',
        'total_manpower_used',
		'status_id',
		'created_by',
		'updated_by'
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

	public function createdBy()
	{
		return $this->belongsTo(\App\Models\AdminUser::class, 'created_by');
	}

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\AdminUser::class, 'updated_by');
    }

	public function project_objects()
	{
		return $this->hasMany(\App\Models\ProjectObject::class);
	}
}
