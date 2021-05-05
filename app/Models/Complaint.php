<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 03 Oct 2019 16:04:43 +0700.
 */

namespace App\Models;

use Carbon\Carbon;
use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Complaint
 *
 * @property int $id
 * @property string $code
 * @property int $project_id
 * @property int $category_id
 * @property int $customer_id
 * @property int $employee_id
 * @property int $employee_handler_id
 * @property int $employee_handler_role_id
 * @property string $customer_name
 * @property string $subject
 * @property string $location
 * @property int $status_id
 * @property \Carbon\Carbon $date
 * @property \Carbon\Carbon $response_limit_date
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 *
 * @property \App\Models\Employee $employee
 * @property \App\Models\EmployeeRole $employee_role
 * @property \App\Models\Customer $customer
 * @property \App\Models\Project $project
 * @property \App\Models\ComplaintCategory $complaint_categories
 * @property \App\Models\Status $status
 * @property \Illuminate\Database\Eloquent\Collection $complaint_absent_histories
 * @property \Illuminate\Database\Eloquent\Collection $complaint_details
 * @property \Illuminate\Database\Eloquent\Collection $complaint_rejects
 * @property \Illuminate\Database\Eloquent\Collection $complaint_header_images
 *
 * @package App\Models
 */
class Complaint extends Eloquent
{
	protected $casts = [
		'project_id' => 'int',
		'category_id' => 'int',
		'customer_id' => 'int',
		'employee_id' => 'int',
		'employee_handler_id' => 'int',
		'employee_handler_role_id' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $dates = [
		'date',
		'response_limit_date'
	];

	protected $fillable = [
		'code',
		'project_id',
		'category_id',
		'customer_id',
		'employee_id',
		'employee_handler_id',
		'employee_handler_role_id',
		'customer_name',
		'subject',
		'location',
		'date',
		'status_id',
		'response_limit_date',
		'created_by',
		'updated_by'
	];

	protected $appends = [
	    'date_string'
    ];

    public function getDateStringAttribute(){
        return Carbon::parse($this->attributes['date'])->format('d M Y');
    }

	public function employee()
	{
		return $this->belongsTo(\App\Models\Employee::class, 'employee_id');
	}

	public function employee_handler()
	{
		return $this->belongsTo(\App\Models\Employee::class, 'employee_handler_id');
	}

    public function employee_role()
    {
        return $this->belongsTo(\App\Models\EmployeeRole::class, 'employee_handler_role_id');
    }

	public function customer()
	{
		return $this->belongsTo(\App\Models\Customer::class);
	}

	public function complaint_categories()
	{
		return $this->belongsTo(\App\Models\ComplaintCategory::class);
	}

	public function project()
	{
		return $this->belongsTo(\App\Models\Project::class);
	}

	public function status()
	{
		return $this->belongsTo(\App\Models\Status::class);
	}

	public function complaint_absent_histories()
	{
		return $this->hasMany(\App\Models\ComplaintAbsentHistory::class);
	}

	public function complaint_details()
	{
		return $this->hasMany(\App\Models\ComplaintDetail::class);
	}

	public function complaint_rejects()
	{
		return $this->hasMany(\App\Models\ComplaintReject::class);
	}

	public function complaint_header_images()
	{
		return $this->hasMany(\App\Models\ComplaintHeaderImage::class);
	}
}
