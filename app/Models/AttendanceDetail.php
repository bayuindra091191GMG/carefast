<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 21 Aug 2019 14:21:19 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class AttendanceDetail
 * 
 * @property int $id
 * @property int $attendance_id
 * @property string $unit
 * @property string $action
 * @property int $status_id
 * @property \Carbon\Carbon $created_at
 * 
 * @property \App\Models\Attendance $attendance
 * @property \App\Models\Status $status
 *
 * @package App\Models
 */
class AttendanceDetail extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'attendance_id' => 'int',
		'status_id' => 'int'
	];

	protected $fillable = [
		'attendance_id',
		'unit',
		'action',
		'status_id',
		'created_at',
	];

	public function attendance()
	{
		return $this->belongsTo(\App\Models\Attendance::class);
	}

	public function status()
	{
		return $this->belongsTo(\App\Models\Status::class);
	}
}
