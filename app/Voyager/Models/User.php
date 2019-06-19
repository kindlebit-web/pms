<?php

namespace App\Voyager\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use TCG\Voyager\Contracts\User as UserContract;
use TCG\Voyager\Traits\VoyagerUser;
use App\User as UserModel;

class User extends Authenticatable implements UserContract
{
    use VoyagerUser;

    protected $guarded = [];

    public $additional_attributes = ['locale'];

    public function getAvatarAttribute($value)
    {
        return $value ?? config('voyager.user.default_avatar', 'users/default.png');
    }

    public function setCreatedAtAttribute($value)
    {
        $this->attributes['created_at'] = Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public function setSettingsAttribute($value)
    {
        $this->attributes['settings'] = $value->toJson();
    }

    public function getSettingsAttribute($value)
    {
        return collect(json_decode($value));
    }

    public function setLocaleAttribute($value)
    {
        $this->settings = $this->settings->merge(['locale' => $value]);
    }

    public function getLocaleAttribute()
    {
        return $this->settings->get('locale');
    }

    public function scopeCurrentUser($query)
    {
        $user = auth()->user();

        if($user->hasRole(UserModel::ADMIN_ROLE) == false) {
          return $query->where('creator_id', $user->id);
        }

        return $query;
    }

    public function scopeCurrentUserProject($query)
    {
        $user = auth()->user();

        if($user->hasRole(UserModel::ADMIN_ROLE) == true || $user->hasRole(UserModel::MANAGER_ROLE) == true) {
           return $query;
        }

       if($user->hasRole(UserModel::ADMIN_ROLE) == false || $user->hasRole(UserModel::MANAGER_ROLE) == false) {
          return $query->where('creator_id', $user->id);
        }

        return $query;
    }

     public static function boot()
     {
       parent::boot();
       static::creating(function($model)
       {
            $user = auth()->user();
            if($user->hasRole(UserModel::ADMIN_ROLE) == true || $user->hasRole(UserModel::MANAGER_ROLE) == true) {
              return;
            }

            if($user->hasRole(UserModel::ADMIN_ROLE) == false || $user->hasRole(UserModel::MANAGER_ROLE) == false) {
              $model->creator_id =  $user->id;
            }

       });
    }
}
