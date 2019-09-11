<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 11 Sep 2019 14:57:50 +0700.
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
 * @package App\Models
 */
class CustomerComplaint extends Eloquent
{
	public $incrementing = false;

	protected $casts = [
		'id' => 'int',
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
}
