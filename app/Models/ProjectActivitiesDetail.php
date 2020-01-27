<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 27 Jan 2020 17:34:52 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class ProjectActivitiesDetail
 * 
 * @property int $id
 * @property int $activities_header_id
 * @property string $action_id
 * @property int $shift_type
 * @property string $weeks
 * @property string $days
 * @property string $period_type
 * @property \Carbon\Carbon $start
 * @property \Carbon\Carbon $finish
 * @property string $description
 * @property int $updated_by
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $created_at
 * 
 * @property \App\Models\ProjectActivitiesHeader $project_activities_header
 *
 * @package App\Models
 */
class ProjectActivitiesDetail extends Eloquent
{
	protected $casts = [
		'activities_header_id' => 'int',
		'shift_type' => 'int',
		'updated_by' => 'int',
		'created_by' => 'int'
	];

	protected $dates = [
		'start',
		'finish'
	];

	protected $fillable = [
		'activities_header_id',
		'action_id',
		'shift_type',
		'weeks',
		'days',
		'period_type',
		'start',
		'finish',
		'description',
		'updated_by',
		'created_by'
	];

	public function project_activities_header()
	{
		return $this->belongsTo(\App\Models\ProjectActivitiesHeader::class, 'activities_header_id');
	}
}
