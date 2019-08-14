<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 14 Aug 2019 13:31:09 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Action
 * 
 * @property int $id
 * @property int $place_id
 * @property string $description
 * @property int $status_id
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 * 
 * @property \App\Models\Place $place
 * @property \App\Models\Status $status
 *
 * @package App\Models
 */
class Action extends Eloquent
{
	protected $casts = [
		'place_id' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'place_id',
		'description',
		'status_id',
		'created_by',
		'updated_by'
	];

	public function place()
	{
		return $this->belongsTo(\App\Models\Place::class);
	}

	public function status()
	{
		return $this->belongsTo(\App\Models\Status::class);
	}
}
