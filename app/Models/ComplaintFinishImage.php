<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 06 May 2021 13:54:00 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class ComplaintFinishImage
 *
 * @property int $id
 * @property int $complaint_id
 * @property int $complaint_finish_id
 * @property string $image
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 *
 * @property \App\Models\Complaint $complaint
 *
 * @package App\Models
 */
class ComplaintFinishImage extends Eloquent
{
	protected $casts = [
		'complaint_id' => 'int',
		'complaint_finish_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'complaint_id',
		'complaint_finish_id',
		'image',
		'created_by',
		'updated_by'
	];

	public function complaint()
	{
		return $this->belongsTo(\App\Models\Complaint::class);
	}
}
