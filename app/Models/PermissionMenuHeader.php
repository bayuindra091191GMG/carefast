<?php

/**
 * Created by Reliese Model.
 * Date: Mon, 22 Jul 2019 14:57:48 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class PermissionMenuHeader
 * 
 * @property int $id
 * @property int $admin_role_id
 * @property int $menu_header_id
 * @property \Carbon\Carbon $created_at
 * @property int $created_by
 * @property \Carbon\Carbon $updated_at
 * @property int $updated_by
 * 
 * @property \App\Models\MenuHeader $menu_header
 * @property \App\Models\AdminUserRole $admin_user_role
 * @property \App\Models\AdminUser $createdBy
 * @property \App\Models\AdminUser $updatedBy
 *
 * @package App\Models
 */
class PermissionMenuHeader extends Eloquent
{
	protected $casts = [
		'admin_role_id' => 'int',
		'menu_header_id' => 'int',
		'created_by' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'admin_role_id',
		'menu_header_id',
		'created_by',
		'updated_by'
	];

	public function menu_header()
	{
		return $this->belongsTo(\App\Models\MenuHeader::class);
	}

	public function admin_user_role()
	{
		return $this->belongsTo(\App\Models\AdminUserRole::class, 'admin_role_id');
	}

	public function updatedBy()
	{
		return $this->belongsTo(\App\Models\AdminUser::class, 'updated_by');
	}

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\AdminUser::class, 'created_by');
    }
}
