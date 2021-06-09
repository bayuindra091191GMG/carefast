<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 09 Jun 2021 16:58:09 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class ProjectShift
 * 
 * @property int $id
 * @property int $project_id
 * @property string $project_code
 * @property string $shift_type
 * @property string $start_time
 * @property string $finish_time
 * @property int $created_by
 * @property \Carbon\Carbon $created_at
 * @property int $updated_by
 * @property \Carbon\Carbon $updated_at
 *
 * @package App\Models
 */
class ProjectShift extends Eloquent
{
	protected $casts = [
		'project_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'project_id',
		'project_code',
		'shift_type',
		'start_time',
		'finish_time',
		'created_by',
		'updated_by'
	];
}
