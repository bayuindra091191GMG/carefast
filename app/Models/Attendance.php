<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 21 Aug 2019 12:15:36 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Attendance
 * 
 * @property int $id
 * @property string $image_path
 * @property int $employee_id
 * @property int $schedule_id
 * @property \Carbon\Carbon $date
 * @property int $status_id
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 * 
 * @property \App\Models\Employee $employee
 * @property \App\Models\ScheduleDetail $schedule_detail
 * @property \App\Models\Status $status
 *
 * @package App\Models
 */
class Attendance extends Eloquent
{
	protected $casts = [
		'employee_id' => 'int',
		'schedule_id' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
	    'image_path',
		'employee_id',
		'schedule_id',
		'date',
		'status_id',
		'created_by',
		'updated_by'
	];

	public function employee()
	{
		return $this->belongsTo(\App\Models\Employee::class);
	}

	public function schedule_detail()
	{
		return $this->belongsTo(\App\Models\ScheduleDetail::class, 'schedule_id');
	}

	public function status()
	{
		return $this->belongsTo(\App\Models\Status::class);
	}
}
