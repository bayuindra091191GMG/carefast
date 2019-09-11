<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 11 Sep 2019 15:57:37 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class CustomerComplaintDetail
 * 
 * @property int $id
 * @property int $complaint_id
 * @property int $customer_id
 * @property int $employee_id
 * @property string $message
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 * 
 * @property \App\Models\Customer $customer
 * @property \App\Models\CustomerComplaint $customer_complaint
 * @property \App\Models\Employee $employee
 *
 * @package App\Models
 */
class CustomerComplaintDetail extends Eloquent
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
		'created_by',
		'updated_by'
	];

	public function customer()
	{
		return $this->belongsTo(\App\Models\Customer::class);
	}

	public function customer_complaint()
	{
		return $this->belongsTo(\App\Models\CustomerComplaint::class, 'complaint_id');
	}

	public function employee()
	{
		return $this->belongsTo(\App\Models\Employee::class);
	}
}
