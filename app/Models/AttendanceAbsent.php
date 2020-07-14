<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 18 Nov 2019 16:05:52 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class AttendanceAbsent
 *
 * @property int $id
 * @property int $employee_id
 * @property int $project_id
 * @property int $shift_type
 * @property \Carbon\Carbon $date
 * @property \Carbon\Carbon $date_checkout
 * @property int $status_id
 * @property int $is_done
 * @property string $image_path
 * @property int $latitude
 * @property int $longitude
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
class AttendanceAbsent extends Eloquent
{
	protected $casts = [
		'employee_id' => 'int',
		'project_id' => 'int',
		'shift_type' => 'int',
		'status_id' => 'int',
		'is_done' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
		'employee_id',
		'project_id',
		'shift_type',
		'date',
		'date_checkout',
		'is_done',
		'status_id',
        'image_path',
        'latitude',
        'longitude',
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
