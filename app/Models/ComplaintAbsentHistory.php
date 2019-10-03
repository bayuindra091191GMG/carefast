<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 03 Oct 2019 16:05:43 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class ComplaintAbsentHistory
 * 
 * @property int $id
 * @property int $complaint_id
 * @property int $employee_id
 * @property int $employee_role_id
 * @property \Carbon\Carbon $created_at
 * 
 * @property \App\Models\Complaint $complaint
 * @property \App\Models\Employee $employee
 * @property \App\Models\EmployeeRole $employee_role
 *
 * @package App\Models
 */
class ComplaintAbsentHistory extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'complaint_id' => 'int',
		'employee_id' => 'int',
		'employee_role_id' => 'int'
	];

	protected $fillable = [
		'complaint_id',
		'employee_id',
		'employee_role_id'
	];

	public function complaint()
	{
		return $this->belongsTo(\App\Models\Complaint::class);
	}

	public function employee()
	{
		return $this->belongsTo(\App\Models\Employee::class);
	}

	public function employee_role()
	{
		return $this->belongsTo(\App\Models\EmployeeRole::class);
	}
}
