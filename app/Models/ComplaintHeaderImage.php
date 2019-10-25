<?php

/**
 * Created by Reliese Model.
 * Date: Fri, 25 Oct 2019 16:43:28 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class ComplaintHeaderImage
 * 
 * @property int $id
 * @property int $complaint_id
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
class ComplaintHeaderImage extends Eloquent
{
	protected $casts = [
		'complaint_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'complaint_id',
		'image',
		'created_by',
		'updated_by'
	];

	public function complaint()
	{
		return $this->belongsTo(\App\Models\Complaint::class);
	}
}
