<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 15 Aug 2019 12:28:39 +0700.
 */

namespace App\Models;

use Carbon\Carbon;
use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Employee
 *
 * @property int $id
 * @property int $employee_role_id
 * @property string $code
 * @property string $first_name
 * @property string $last_name
 * @property string $telephone
 * @property string $phone
 * @property \Carbon\Carbon $dob
 * @property string $nik
 * @property string $address
 * @property string $notes
 * @property string $image_path
 * @property int $status_id
 * @property int $created_by
 * @property \Carbon\Carbon $created_at
 * @property int $updated_by
 * @property \Carbon\Carbon $updated_at
 *
 * @property \App\Models\AdminUser $admin_user
 * @property \App\Models\EmployeeRole $employee_role
 * @property \App\Models\Status $status
 *
 * @package App\Models
 */
class Employee extends Eloquent
{
	protected $casts = [
		'employee_role_id' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $dates = [
		'dob'
	];

	protected $fillable = [
		'employee_role_id',
		'code',
		'first_name',
		'last_name',
		'telephone',
		'phone',
		'dob',
		'nik',
		'address',
		'notes',
		'image_path',
		'status_id',
		'created_by',
		'updated_by'
	];

    protected $appends = [
        'dob_string'
    ];

    public function getDobStringAttribute(){
        return Carbon::parse($this->attributes['dob'])->format("d M Y");
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\AdminUser::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\AdminUser::class, 'updated_by');
    }

	public function employee_role()
	{
		return $this->belongsTo(\App\Models\EmployeeRole::class);
	}

	public function status()
	{
		return $this->belongsTo(\App\Models\Status::class);
	}
}
