<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 21 Aug 2019 12:15:08 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class ScheduleDetail
 * 
 * @property int $id
 * @property int $schedule_id
 * @property int $unit_id
 * @property int $action_id
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 * 
 * @property \App\Models\Action $action
 * @property \App\Models\Schedule $schedule
 * @property \App\Models\Unit $unit
 * @property \Illuminate\Database\Eloquent\Collection $attendances
 *
 * @package App\Models
 */
class ScheduleDetail extends Eloquent
{
	protected $casts = [
		'schedule_id' => 'int',
		'unit_id' => 'int',
		'action_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'schedule_id',
		'unit_id',
		'action_id',
		'created_by',
		'updated_by'
	];

	public function action()
	{
		return $this->belongsTo(\App\Models\Action::class);
	}

	public function schedule()
	{
		return $this->belongsTo(\App\Models\Schedule::class);
	}

	public function unit()
	{
		return $this->belongsTo(\App\Models\Unit::class);
	}

	public function attendances()
	{
		return $this->hasMany(\App\Models\Attendance::class, 'schedule_id');
	}
}
