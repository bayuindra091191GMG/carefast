<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 14 Nov 2019 14:58:05 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class ComplaintCategory
 * 
 * @property int $id
 * @property string $description
 * @property int $status_id
 *
 * @package App\Models
 */
class ComplaintCategory extends Eloquent
{
	public $timestamps = false;

	protected $casts = [
		'status_id' => 'int'
	];

	protected $fillable = [
		'description',
		'status_id'
	];
}
