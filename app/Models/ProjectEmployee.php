<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 16 Sep 2019 13:20:58 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class ProjectEmployee
 *
 * @property int $id
 * @property int $project_id
 * @property int $employee_id
 * @property int $employee_roles_id
 * @property string $project_employee_code
 * @property int $status_id
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 *
 * @property \App\Models\Employee $employee
 * @property \App\Models\Project $project
 * @property \App\Models\EmployeeRole $employee_role
 * @property \Illuminate\Database\Eloquent\Collection $schedules
 *
 * @package App\Models
 */
class ProjectEmployee extends Eloquent
{
	protected $casts = [
		'project_id' => 'int',
		'employee_id' => 'int',
		'employee_roles_id' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'project_id',
		'employee_id',
		'employee_roles_id',
		'project_employee_code',
		'status_id',
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

	public function employee_role()
	{
		return $this->belongsTo(\App\Models\EmployeeRole::class, 'employee_roles_id');
	}

	public function schedules()
	{
		return $this->hasMany(\App\Models\Schedule::class);
	}
}
