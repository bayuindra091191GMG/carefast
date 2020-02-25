<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 05 Sep 2019 23:26:02 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class ProjectObject
 *
 * @property int $id
 * @property int $project_id
 * @property int $place_id
 * @property int $unit_id
 * @property int $sub1_unit_id
 * @property int $sub2_unit_id
 * @property string $place_name
 * @property string $unit_name
 * @property string $sub1_unit_name
 * @property string $sub2_unit_name
 * @property string $object_name
 * @property int $status_id
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 *
 * @property \App\Models\Project $project
 * @property \App\Models\Place $place
 * @property \App\Models\Status $status
 *
 * @package App\Models
 */
class ProjectObject extends Eloquent
{
	protected $casts = [
		'project_id' => 'int',
		'place_id' => 'int',
		'unit_id' => 'int',
		'sub1_unit_id' => 'int',
		'sub2_unit_id' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'project_id',
		'place_id',
		'unit_id',
		'sub1_unit_id',
		'sub2_unit_id',
		'place_name',
		'unit_name',
		'sub1_unit_name',
		'sub2_unit_name',
		'object_name',
		'status_id',
		'created_by',
		'updated_by'
	];

	public function project()
	{
		return $this->belongsTo(\App\Models\Project::class);
	}

	public function status()
	{
		return $this->belongsTo(\App\Models\Status::class);
	}

	public function place()
	{
		return $this->belongsTo(\App\Models\Place::class);
	}
}
