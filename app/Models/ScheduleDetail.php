<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 19 Sep 2019 18:47:01 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class ScheduleDetail
 * 
 * @property int $id
 * @property int $schedule_id
 * @property int $project_object_id
 * @property int $action_id
 * @property string $description
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 * 
 * @property \App\Models\Action $action
 * @property \App\Models\ProjectObject $project_object
 * @property \App\Models\Schedule $schedule
 *
 * @package App\Models
 */
class ScheduleDetail extends Eloquent
{
	protected $casts = [
		'schedule_id' => 'int',
		'project_object_id' => 'int',
		'action_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'schedule_id',
		'project_object_id',
		'action_id',
		'description',
		'created_by',
		'updated_by'
	];

	public function action()
	{
		return $this->belongsTo(\App\Models\Action::class);
	}

	public function project_object()
	{
		return $this->belongsTo(\App\Models\ProjectObject::class);
	}

	public function schedule()
	{
		return $this->belongsTo(\App\Models\Schedule::class);
	}
}
