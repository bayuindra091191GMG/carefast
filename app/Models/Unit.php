<?php

/**
 * Created by Reliese Model.
 * Date: Fri, 30 Aug 2019 13:26:03 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Unit
 * 
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $status_id
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 * 
 * @property \App\Models\Status $status
 * @property \Illuminate\Database\Eloquent\Collection $schedule_details
 * @property \Illuminate\Database\Eloquent\Collection $sub1_units
 *
 * @package App\Models
 */
class Unit extends Eloquent
{
	protected $casts = [
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'name',
		'description',
		'status_id',
		'created_by',
		'updated_by'
	];

	public function status()
	{
		return $this->belongsTo(\App\Models\Status::class);
	}

	public function schedule_details()
	{
		return $this->hasMany(\App\Models\ScheduleDetail::class);
	}

	public function sub1_units()
	{
		return $this->hasMany(\App\Models\Sub1Unit::class);
	}
}
