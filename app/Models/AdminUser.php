<?php

/**
 * Created by Reliese Model.
 * Date: Tue, 13 Aug 2019 10:28:09 +0700.
 */

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Reliese\Database\Eloquent\Model as Eloquent;
use SMartins\PassportMultiauth\HasMultiAuthApiTokens;

/**
 * Class AdminUser
 *
 * @property int $id
 * @property int $is_super_admin
 * @property int $role_id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $password
 * @property string $language
 * @property int $status_id
 * @property int $project_id
 * @property string $fm_id
 * @property string $image_path
 * @property string $remember_token
 * @property \Carbon\Carbon $email_verified_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property \App\Models\Status $status
 * @property \App\Models\AdminUserRole $admin_user_role
 * @property \Illuminate\Database\Eloquent\Collection $banners
 * @property \Illuminate\Database\Eloquent\Collection $faqs
 * @property \Illuminate\Database\Eloquent\Collection $fcm_token_admins
 * @property \Illuminate\Database\Eloquent\Collection $permission_menu_headers
 * @property \Illuminate\Database\Eloquent\Collection $permission_menu_subs
 * @property \Illuminate\Database\Eloquent\Collection $permission_menus
 *
 * @package App\Models
 */
class AdminUser extends Authenticatable
{
    use Notifiable, HasMultiAuthApiTokens;

	protected $casts = [
		'is_super_admin' => 'int',
		'role_id' => 'int',
		'status_id' => 'int',
        'project_id' => 'int'
	];

	protected $dates = [
		'email_verified_at'
	];

	protected $hidden = [
		'password',
		'remember_token'
	];

	protected $fillable = [
		'is_super_admin',
		'role_id',
		'first_name',
		'last_name',
		'email',
		'password',
		'language',
		'status_id',
		'image_path',
		'remember_token',
		'email_verified_at',
        'project_id',
        'fm_id',
	];

	public function status()
	{
		return $this->belongsTo(\App\Models\Status::class);
	}

	public function admin_user_role()
	{
		return $this->belongsTo(\App\Models\AdminUserRole::class, 'role_id');
	}

	public function banners()
	{
		return $this->hasMany(\App\Models\Banner::class, 'updated_by');
	}

	public function faqs()
	{
		return $this->hasMany(\App\Models\Faq::class, 'updated_by');
	}

	public function fcm_token_admins()
	{
		return $this->hasMany(\App\Models\FcmTokenAdmin::class, 'user_admin_id');
	}

	public function permission_menu_headers()
	{
		return $this->hasMany(\App\Models\PermissionMenuHeader::class, 'updated_by');
	}

	public function permission_menu_subs()
	{
		return $this->hasMany(\App\Models\PermissionMenuSub::class, 'updated_by');
	}

	public function permission_menus()
	{
		return $this->hasMany(\App\Models\PermissionMenu::class, 'updated_by');
	}
}
