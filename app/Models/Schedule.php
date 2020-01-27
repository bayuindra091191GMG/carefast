<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 27 Jan 2020 21:16:04 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Schedule
 * 
 * @property int $id
 * @property int $project_id
 * @property int $project_employee_id
 * @property int $project_activity_id
 * @property int $employee_id
 * @property int $place_id
 * @property int $shift_type
 * @property string $weeks
 * @property string $days
 * @property string $period_type
 * @property \Carbon\Carbon $start
 * @property \Carbon\Carbon $finish
 * @property int $status_id
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 * 
 * @property \App\Models\Status $status
 * @property \App\Models\Employee $employee
 * @property \App\Models\Place $place
 * @property \App\Models\ProjectActivitiesHeader $project_activities_header
 * @property \App\Models\ProjectEmployee $project_employee
 * @property \App\Models\Project $project
 * @property \Illuminate\Database\Eloquent\Collection $attendances
 * @property \Illuminate\Database\Eloquent\Collection $schedule_details
 *
 * @package App\Models
 */
class Schedule extends Eloquent
{
	protected $casts = [
		'project_id' => 'int',
		'project_employee_id' => 'int',
		'project_activity_id' => 'int',
		'employee_id' => 'int',
		'place_id' => 'int',
		'shift_type' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $dates = [
		'start',
		'finish'
	];

	protected $fillable = [
		'project_id',
		'project_employee_id',
		'project_activity_id',
		'employee_id',
		'place_id',
		'shift_type',
		'weeks',
		'days',
		'period_type',
		'start',
		'finish',
		'status_id',
		'created_by',
		'updated_by'
	];

	public function status()
	{
		return $this->belongsTo(\App\Models\Status::class);
	}

	public function employee()
	{
		return $this->belongsTo(\App\Models\Employee::class);
	}

	public function place()
	{
		return $this->belongsTo(\App\Models\Place::class);
	}

	public function project_activities_header()
	{
		return $this->belongsTo(\App\Models\ProjectActivitiesHeader::class, 'project_activity_id');
	}

	public function project_employee()
	{
		return $this->belongsTo(\App\Models\ProjectEmployee::class);
	}

	public function project()
	{
		return $this->belongsTo(\App\Models\Project::class);
	}

	public function attendances()
	{
		return $this->hasMany(\App\Models\Attendance::class);
	}

	public function schedule_details()
	{
		return $this->hasMany(\App\Models\ScheduleDetail::class);
	}
}
