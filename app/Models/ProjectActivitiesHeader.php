<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 27 Jan 2020 20:42:37 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class ProjectActivitiesHeader
 * 
 * @property int $id
 * @property int $project_id
 * @property string $plotting_name
 * @property int $place_id
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 * 
 * @property \App\Models\Project $project
 * @property \App\Models\Place $place
 * @property \Illuminate\Database\Eloquent\Collection $project_activities_details
 *
 * @package App\Models
 */
class ProjectActivitiesHeader extends Eloquent
{
	protected $casts = [
		'project_id' => 'int',
		'place_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'project_id',
		'plotting_name',
		'place_id',
		'created_by',
		'updated_by'
	];

	public function project()
	{
		return $this->belongsTo(\App\Models\Project::class);
	}

	public function place()
	{
		return $this->belongsTo(\App\Models\Place::class);
	}

	public function project_activities_details()
	{
		return $this->hasMany(\App\Models\ProjectActivitiesDetail::class, 'activities_header_id');
	}
}
