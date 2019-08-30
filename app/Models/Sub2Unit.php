<?php

/**
 * Created by Reliese Model.
 * Date: Fri, 30 Aug 2019 13:25:51 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Sub2Unit
 * 
 * @property int $id
 * @property int $sub_1_unit_id
 * @property string $name
 * @property string $description
 * @property int $status_id
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 * 
 * @property \App\Models\Status $status
 * @property \App\Models\Sub1Unit $sub1_unit
 *
 * @package App\Models
 */
class Sub2Unit extends Eloquent
{
	protected $casts = [
		'sub_1_unit_id' => 'int',
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'sub_1_unit_id',
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

	public function sub1_unit()
	{
		return $this->belongsTo(\App\Models\Sub1Unit::class, 'sub_1_unit_id');
	}
}
