<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 14 Aug 2019 13:44:22 +0700.
 */

namespace App\Models;

use Carbon\Carbon;
use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Employee
 *
 * @property int $id
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
 * @property \App\Models\Status $status
 *
 * @package App\Models
 */
class Employee extends Eloquent
{
	protected $casts = [
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $dates = [
		'dob'
	];

	protected $fillable = [
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

	public function status()
	{
		return $this->belongsTo(\App\Models\Status::class);
	}
}
