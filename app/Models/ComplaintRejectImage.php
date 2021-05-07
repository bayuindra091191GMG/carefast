<?php

/**
 * Created by Reliese Model.
 * Date: Fri, 30 Apr 2021 13:32:03 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class ComplaintRejectImage
 *
 * @property int $id
 * @property int $complaint_id
 * @property int $complaint_reject_id
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
class ComplaintRejectImage extends Eloquent
{
	protected $casts = [
		'complaint_id' => 'int',
		'complaint_reject_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'complaint_id',
		'complaint_reject_id',
		'image',
		'created_by',
		'updated_by'
	];

	public function complaint()
	{
		return $this->belongsTo(\App\Models\Complaint::class);
	}
}
