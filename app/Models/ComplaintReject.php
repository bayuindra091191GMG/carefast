<?php

/**
 * Created by Reliese Model.
 * Date: Fri, 30 Apr 2021 13:32:20 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class ComplaintReject
 * 
 * @property int $id
 * @property int $complaint_id
 * @property int $customer_id
 * @property int $employee_id
 * @property string $message
 * @property string $image
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 * 
 * @property \App\Models\Complaint $complaint
 * @property \App\Models\Customer $customer
 * @property \App\Models\Employee $employee
 *
 * @package App\Models
 */
class ComplaintReject extends Eloquent
{
	protected $casts = [
		'complaint_id' => 'int',
		'customer_id' => 'int',
		'employee_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'complaint_id',
		'customer_id',
		'employee_id',
		'message',
		'image',
		'created_by',
		'updated_by'
	];

	public function complaint()
	{
		return $this->belongsTo(\App\Models\Complaint::class);
	}

	public function customer()
	{
		return $this->belongsTo(\App\Models\Customer::class);
	}

	public function employee()
	{
		return $this->belongsTo(\App\Models\Employee::class);
	}
}
