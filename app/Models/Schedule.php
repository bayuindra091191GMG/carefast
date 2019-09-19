<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 19 Sep 2019 19:05:10 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Schedule
 *
 * @property int $id
 * @property int $project_id
 * @property int $project_employee_id
 * @property int $shift_type
 * @property string $weeks
 * @property string $days
 * @property string $start
 * @property string $finish
 * @property int $status_id
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 *
 * @property \App\Models\Status $status
 * @property \App\Models\ProjectEmployee $project_employee
 * @property \App\Models\Project $projects
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
		'shift_type' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'project_id',
		'project_employee_id',
		'shift_type',
		'weeks',
		'days',
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

	public function project_employee()
	{
		return $this->belongsTo(\App\Models\ProjectEmployee::class);
	}

	public function projects()
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
