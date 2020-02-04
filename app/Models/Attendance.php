<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 26 Sep 2019 10:32:26 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Attendance
 *
 * @property int $id
 * @property int $employee_id
 * @property int $schedule_id
 * @property int $place_id
 * @property \Carbon\Carbon $date
 * @property int $status_id
 * @property string $image_path
 * @property int $is_done
 * @property int $assessment_leader
 * @property int $schedule_detail_id
 * @property string $notes
 * @property string $assessment_notes
 * @property int $assessment_score
 * @property string $is_action_checked
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 *
 * @property \App\Models\Employee $employee
 * @property \App\Models\Place $place
 * @property \App\Models\Schedule $schedule
 * @property \App\Models\ScheduleDetail $schedule_detail
 * @property \App\Models\Status $status
 * @property \Illuminate\Database\Eloquent\Collection $attendance_details
 *
 * @package App\Models
 */
class Attendance extends Eloquent
{
	protected $casts = [
		'employee_id' => 'int',
		'schedule_id' => 'int',
		'place_id' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int',
		'assessment_leader' => 'int',
		'schedule_detail_id' => 'int',
		'assessment_score' => 'int',
		'is_done' => 'int'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
		'employee_id',
		'schedule_id',
		'place_id',
		'date',
		'status_id',
		'image_path',
		'is_done',
		'assessment_leader',
		'schedule_detail_id',
		'notes',
		'assessment_notes',
		'assessment_score',
		'is_action_checked',
		'created_by',
		'updated_by'
	];

	public function employee()
	{
		return $this->belongsTo(\App\Models\Employee::class);
	}

	public function place()
	{
		return $this->belongsTo(\App\Models\Place::class);
	}

	public function schedule()
	{
		return $this->belongsTo(\App\Models\Schedule::class);
	}

	public function schedule_detail()
	{
		return $this->belongsTo(\App\Models\ScheduleDetail::class);
	}

	public function status()
	{
		return $this->belongsTo(\App\Models\Status::class);
	}

	public function attendance_details()
	{
		return $this->hasMany(\App\Models\AttendanceDetail::class);
	}
}
