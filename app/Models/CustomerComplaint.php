<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 11 Sep 2019 15:57:30 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class CustomerComplaint
 * 
 * @property int $id
 * @property int $project_id
 * @property int $customer_id
 * @property string $customer_name
 * @property string $subject
 * @property \Carbon\Carbon $date
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 * 
 * @property \App\Models\Customer $customer
 * @property \App\Models\Project $project
 * @property \Illuminate\Database\Eloquent\Collection $customer_complaint_details
 *
 * @package App\Models
 */
class CustomerComplaint extends Eloquent
{
	protected $casts = [
		'project_id' => 'int',
		'customer_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
		'project_id',
		'customer_id',
		'customer_name',
		'subject',
		'date',
		'created_by',
		'updated_by'
	];

	public function customer()
	{
		return $this->belongsTo(\App\Models\Customer::class);
	}

	public function project()
	{
		return $this->belongsTo(\App\Models\Project::class);
	}

	public function customer_complaint_details()
	{
		return $this->hasMany(\App\Models\CustomerComplaintDetail::class, 'complaint_id');
	}
}
