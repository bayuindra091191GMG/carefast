<?php

/**
 * Created by Reliese Model.
 * Date: Tue, 05 Jan 2021 16:17:36 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class ImeiHistory
 * 
 * @property int $id
 * @property int $employee_id
 * @property string $nuc
 * @property string $imei_old
 * @property string $phone_type_old
 * @property string $imei_new
 * @property string $phone_type_new
 * @property \Carbon\Carbon $date
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 * 
 * @property \App\Models\Employee $employee
 *
 * @package App\Models
 */
class ImeiHistory extends Eloquent
{
	protected $casts = [
		'employee_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $dates = [
		'date'
	];

	protected $fillable = [
		'employee_id',
		'nuc',
		'imei_old',
		'phone_type_old',
		'imei_new',
		'phone_type_new',
		'date',
		'created_by',
		'updated_by'
	];

	public function employee()
	{
		return $this->belongsTo(\App\Models\Employee::class);
	}
}
