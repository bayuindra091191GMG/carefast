<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 27 Sep 2021 13:31:43 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class EmployeePlottingSchedule
 * 
 * @property int $id
 * @property int $project_id
 * @property int $project_activity_id
 * @property string $day_employee_id
 * @property int $created_by
 * @property \Carbon\Carbon $created_at
 * @property int $updated_by
 * @property \Carbon\Carbon $updated_at
 * 
 * @property \App\Models\ProjectActivitiesHeader $project_activities_header
 *
 * @package App\Models
 */
class EmployeePlottingSchedule extends Eloquent
{
	protected $casts = [
		'project_id' => 'int',
		'project_activity_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'project_id',
		'project_activity_id',
		'day_employee_id',
		'created_by',
		'updated_by'
	];

	public function project_activities_header()
	{
		return $this->belongsTo(\App\Models\ProjectActivitiesHeader::class, 'project_activity_id');
	}
}
