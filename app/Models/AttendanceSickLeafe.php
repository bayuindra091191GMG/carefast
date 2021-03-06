<?php

/**
 * Created by Reliese Model.
 * Date: Sat, 13 Feb 2021 15:49:03 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class AttendanceSickLeafe
 * 
 * @property int $id
 * @property int $employee_id
 * @property int $project_id
 * @property \Carbon\Carbon $date
 * @property string $description
 * @property string $image_path
 * @property int $is_approve
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 * 
 * @property \App\Models\Employee $employee
 * @property \App\Models\Project $project
 *
 * @package App\Models
 */
class AttendanceSickLeafe extends Eloquent
{
	protected $casts = [
		'employee_id' => 'int',
		'project_id' => 'int',
		'is_approve' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
		'employee_id',
		'project_id',
		'date',
		'description',
		'image_path',
		'is_approve',
		'created_by',
		'updated_by'
	];

	public function employee()
	{
		return $this->belongsTo(\App\Models\Employee::class);
	}

	public function project()
	{
		return $this->belongsTo(\App\Models\Project::class);
	}
}
