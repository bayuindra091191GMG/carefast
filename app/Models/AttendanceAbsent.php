<?php

/**
 * Created by Reliese Model.
 * Date: Tue, 16 Feb 2021 13:05:23 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class AttendanceAbsent
 * 
 * @property int $id
 * @property int $employee_id
 * @property int $project_id
 * @property \Carbon\Carbon $date
 * @property \Carbon\Carbon $date_checkout
 * @property int $shift_type
 * @property int $status_id
 * @property int $is_done
 * @property string $image_path
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 * @property float $latitude
 * @property float $longitude
 * @property string $type
 * @property string $description
 * 
 * @property \App\Models\Employee $employee
 * @property \App\Models\Project $project
 *
 * @package App\Models
 */
class AttendanceAbsent extends Eloquent
{
	protected $casts = [
		'employee_id' => 'int',
		'project_id' => 'int',
		'shift_type' => 'int',
		'status_id' => 'int',
		'is_done' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int',
		'latitude' => 'float',
		'longitude' => 'float'
	];

	protected $dates = [
		'date',
		'date_checkout'
	];

	protected $fillable = [
		'employee_id',
		'project_id',
		'date',
		'date_checkout',
		'shift_type',
		'status_id',
		'is_done',
		'image_path',
		'created_by',
		'updated_by',
		'latitude',
		'longitude',
		'type',
		'description'
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
