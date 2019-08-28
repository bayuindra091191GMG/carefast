<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 28 Aug 2019 17:02:57 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class ProjectObject
 *
 * @property int $id
 * @property int $project_id
 * @property string $object_name
 * @property int $status_id
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 *
 * @property \App\Models\Project $project
 * @property \App\Models\Status $status
 *
 * @package App\Models
 */
class ProjectObject extends Eloquent
{
	protected $casts = [
		'project_id' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'project_id',
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
}
