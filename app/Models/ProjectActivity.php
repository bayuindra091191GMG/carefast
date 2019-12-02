<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 02 Dec 2019 15:27:04 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class ProjectActivity
 *
 * @property int $id
 * @property int $project_id
 * @property string $plotting_name
 * @property string $action_id
 * @property int $place_id
 * @property int $shift_type
 * @property string $weeks
 * @property string $days
 * @property string $period_type
 * @property \Carbon\Carbon $start
 * @property \Carbon\Carbon $finish
 * @property string $description
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 *
 * @property \App\Models\Place $place
 * @property \App\Models\Project $project
 *
 * @package App\Models
 */
class ProjectActivity extends Eloquent
{
	protected $casts = [
		'project_id' => 'int',
		'place_id' => 'int',
		'shift_type' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $dates = [
		'start',
		'finish'
	];

	protected $fillable = [
		'project_id',
		'plotting_name',
		'action_id',
		'place_id',
		'shift_type',
		'weeks',
		'days',
		'period_type',
		'start',
		'finish',
		'description',
		'created_by',
		'updated_by'
	];


	public function place()
	{
		return $this->belongsTo(\App\Models\Place::class);
	}

	public function project()
	{
		return $this->belongsTo(\App\Models\Project::class);
	}
}
