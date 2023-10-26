<?php

namespace hpsynapse\moduser\Models;

//use Laravel\Passport\HasApiTokens;//comment jika tidak menggunakan passport
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

use App\Base\Traits\ModelDataTenant;

class User extends Authenticatable implements MustVerifyEmail
{

    //comment HasApiTokens jika tidak menggunakan passport
    use NotifiableCustom; //HasApiTokens,    
    use ModelDataTenant;
    protected $connection = 'perTenant';

    protected $table = 'moduser_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_idcode','user_group_id', 'name', 'username', 'email', 'phone', 'password', 'auth_password',
        'socialauth_facebook_id', 'socialauth_facebook_token', 'socialauth_facebook_data',
        'socialauth_google_id', 'socialauth_google_token', 'socialauth_google_data', 'level','user_type','ip_address',
        'note', 'role',  'status', 'banned_note', 'system_user','dashboard_type', 'secret_key', 'tenant_id', 'pin'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'auth_password', 'pin'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'ip_address' => 'array',
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
    ];

    public function profile()
    {
        return $this->hasOne('hpsynapse\moduser\Models\UserProfile', 'user_id');
    }

    public function roles()
    {
        return $this->hasMany('hpsynapse\moduser\Models\UserRole', 'user_id');
    }

    public function userGroup()
    {
        return $this->hasOne('hpsynapse\moduser\Models\UserGroup', 'id','user_group_id');
    }

    public function otp()
    {
        return $this->hasOne('hpsynapse\moduser\Models\UserOTP', 'user_id');
    }

    public function mainRole()
    {
        return $this->hasOne('hpsynapse\moduser\Models\UserRole', 'user_id')->has('role')->where('is_main_role', 1);
    }

    // public function mainRole()
    // {
    //     // dd($this->roles()->where('is_main_role',1)->first()->role());
    //     // dd($this->roles()->where('is_main_role',1));
    //     return $this->roleutama()->role();//\hpsynapse\moduser\Models\UserRole::where('is_main_role',1)->where('user_id',$this->user_id);
    // }

    public function userTenant()
    {
        return $this->hasMany('hpsynapse\moduser\Models\UserTenant', 'user_id');
    }

    // public function tenant()
    // {
    //     return $this->hasManyThrough(
    //         'App\Models\Tenant', // table tujuan
    //         'hpsynapse\moduser\Models\UserTenant', // table transaksi
    //         'tenant_id', // Foreign key on table transaksi...
    //         '', // Foreign key on table tujuan...
    //         '', // Local key on main model table...
    //         'user_id' // Local key on table transaksi...
    //     );
    // }

    public function apiToken()
    {
        return $this->hasOne('hpsynapse\moduser\Models\ApiToken', 'user_id');
    }

    /**
     * role_group_is_integrated : true/false, apakah user ini teringrasi dengan
     * data eksternal lain, jika ya maka ini bisa digunakan sebagai penanda apakah
     * user bisa dimanage via fitur manage user atau tidak.
     * 
     * @return Float path file
     */
    public function getRoleGroupIsIntegratedAttribute()
    {
        return UserRoleGroup::where('user_id',$this->id)->whereHas('roleGroup',function($m){
            $m->where('has_model',1)->orWhere('can_selected_on_create',0);
        })->exists();
    }

    
    public function userRoleGroup()
    {
        return $this->hasManyThrough(
            RoleGroup::class, // table tujuan
            UserRoleGroup::class, // table transaksi
            'user_id', // Foreign key on table transaksi (untuk berelasi dengan main model)
            'id', // Foreign key on table tujuan (untuk berelasi dengan table transaksi)
            'id', // Local key on main model table (untuk berelasi dengan table transaksi)
            'role_group_id' // Local key on table transaksi (untuk berelasi dengan table tujuan)
        );
    }
}
