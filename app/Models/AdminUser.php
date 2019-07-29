<?php

/**
 * Created by Reliese Model.
 * Date: Wed, 17 Jul 2019 13:57:15 +0700.
 */

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
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
 * @property int $waste_bank_id
 * @property int $status_id
 * @property string $image_path
 * @property string $remember_token
 * @property \Carbon\Carbon $email_verified_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property \App\Models\Status $status
 * @property \App\Models\AdminUserRole $admin_user_role
 * @property \Illuminate\Database\Eloquent\Collection $faqs
 * @property \Illuminate\Database\Eloquent\Collection $fcm_token_browsers
 * @property \Illuminate\Database\Eloquent\Collection $permission_menu_subs
 * @property \Illuminate\Database\Eloquent\Collection $permission_menus
 * @property \Illuminate\Database\Eloquent\Collection $created_by_sales_order_headers
 * @property \Illuminate\Database\Eloquent\Collection $updated_by_sales_order_headers
 *
 * @package App\Models
 */
class AdminUser extends Authenticatable
{
    use Notifiable, HasMultiAuthApiTokens;

	protected $casts = [
		'is_super_admin' => 'int',
		'role_id' => 'int',
		'waste_bank_id' => 'int',
		'status_id' => 'int'
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
		'waste_bank_id',
		'status_id',
		'image_path',
		'remember_token',
		'email_verified_at'
	];

	public function status()
	{
		return $this->belongsTo(\App\Models\Status::class);
	}

	public function admin_user_role()
	{
		return $this->belongsTo(\App\Models\AdminUserRole::class, 'role_id');
	}

	public function faqs()
	{
		return $this->hasMany(\App\Models\Faq::class, 'updated_by');
	}

	public function fcm_token_browsers()
	{
		return $this->hasMany(\App\Models\FcmTokenBrowser::class, 'user_admin_id');
	}

	public function permission_menu_subs()
	{
		return $this->hasMany(\App\Models\PermissionMenuSub::class, 'updated_by');
	}

	public function permission_menus()
	{
		return $this->hasMany(\App\Models\PermissionMenu::class, 'updated_by');
	}

    public function created_by_sales_order_headers()
    {
        return $this->hasMany(\App\Models\SalesOrderHeader::class, 'created_by');
    }

    public function updated_by_sales_order_headers()
    {
        return $this->hasMany(\App\Models\SalesOrderHeader::class, 'updated_by');
    }
}
