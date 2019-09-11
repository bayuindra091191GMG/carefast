<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 11 Sep 2019 14:58:07 +0700.
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
 * @package App\Models
 */
class CustomerComplaintDetail extends Eloquent
{
	public $incrementing = false;

	protected $casts = [
		'id' => 'int',
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
}
