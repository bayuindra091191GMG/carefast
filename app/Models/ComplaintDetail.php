<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 03 Oct 2019 16:05:22 +0700.
 */

namespace App\Models;

use Carbon\Carbon;
use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class ComplaintDetail
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
 * @property \App\Models\Complaint $complaint
 * @property \App\Models\Employee $employee
 *
 * @package App\Models
 */
class ComplaintDetail extends Eloquent
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
        'created_at',
		'updated_by',
        'updated_at'
	];

    protected $appends = [
        'created_at_string'
    ];

    public function getCreatedAtStringAttribute(){
        return Carbon::parse($this->attributes['created_at'])->format('d M Y H:i:s');
    }

	public function customer()
	{
		return $this->belongsTo(\App\Models\Customer::class);
	}

	public function complaint()
	{
		return $this->belongsTo(\App\Models\Complaint::class);
	}

	public function employee()
	{
		return $this->belongsTo(\App\Models\Employee::class);
	}
}
