<?php

/**
 * Created by Reliese Model.
 * Date: Fri, 30 Aug 2019 09:26:34 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Sub1Unit
 * 
 * @property int $id
 * @property int $unit_id
 * @property string $name
 * @property string $description
 * @property int $status_id
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 * 
 * @property \App\Models\Status $status
 * @property \App\Models\Unit $unit
 * @property \Illuminate\Database\Eloquent\Collection $sub_2_units
 *
 * @package App\Models
 */
class Sub1Unit extends Eloquent
{
	protected $casts = [
		'unit_id' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'unit_id',
		'name',
		'description',
		'status_id',
		'created_by',
		'updated_by'
	];

	public function status()
	{
		return $this->belongsTo(\App\Models\Status::class);
	}

	public function unit()
	{
		return $this->belongsTo(\App\Models\Unit::class);
	}

	public function sub_2_units()
	{
		return $this->hasMany(\App\Models\Sub2Unit::class);
	}
}
