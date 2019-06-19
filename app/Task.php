<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
	   public static function boot()
	   {
	       parent::boot();
	       static::creating(function($model)
	       {
	            $user = auth()->user();
	            if($user->hasRole(User::ADMIN_ROLE) == true || $user->hasRole(User::MANAGER_ROLE) == true) {
	              return;
	            }
		        if($user->hasRole(User::ADMIN_ROLE) == false || $user->hasRole(User::MANAGER_ROLE) == false) {
		          $model->creator_id =  $user->id;
		        }

	       });
	   }

	 public function scopeCurrentUser($query)
	 {
	    $user = auth()->user();
        
	    if($user->hasRole(User::ADMIN_ROLE) == true || $user->hasRole(User::MANAGER_ROLE) == true) {
	       return $query;
	    }

	    if($user->hasRole(User::ADMIN_ROLE) == false || $user->hasRole(User::MANAGER_ROLE) == false) {
	      return $query->where('creator_id', $user->id);
	    }

	    return $query;
	    
	 }
}
