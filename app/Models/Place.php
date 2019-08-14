<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 14 Aug 2019 11:17:15 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Place
 * 
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $status_id
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 * 
 * @property \Illuminate\Database\Eloquent\Collection $actions
 *
 * @package App\Models
 */
class Place extends Eloquent
{
	protected $casts = [
		'status_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'name',
		'description',
		'status_id',
		'created_by',
		'updated_by'
	];

	public function actions()
	{
		return $this->hasMany(\App\Models\Action::class);
	}
}
