<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 17 Jul 2019 13:36:21 +0700.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class AdminUserRole
 * 
 * @property int $id
 * @property string $name
 * @property string $description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property \Illuminate\Database\Eloquent\Collection $admin_users
 * @property \Illuminate\Database\Eloquent\Collection $permission_menu_subs
 * @property \Illuminate\Database\Eloquent\Collection $permission_menus
 * @property \Illuminate\Database\Eloquent\Collection $permission_roles
 *
 * @package App\Models
 */
class AdminUserRole extends Eloquent
{
	protected $fillable = [
		'name',
		'description'
	];

	public function admin_users()
	{
		return $this->hasMany(\App\Models\AdminUser::class, 'role_id');
	}

	public function permission_menu_subs()
	{
		return $this->hasMany(\App\Models\PermissionMenuSub::class, 'role_id');
	}

	public function permission_menus()
	{
		return $this->hasMany(\App\Models\PermissionMenu::class, 'role_id');
	}

	public function permission_roles()
	{
		return $this->hasMany(\App\Models\PermissionRole::class, 'role_id');
	}
}
