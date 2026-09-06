<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [

        'name',
        'email',
        'password',
        'role',
        'must_change_password',
        'is_active',

    ];

    protected $hidden = [

        'password',
        'remember_token',

    ];

    protected $casts = [

        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',

    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }

    /*
    |--------------------------------------------------------------------------
    | ROLE HELPERS
    |--------------------------------------------------------------------------
    */

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isChefMagasinier()
    {
        return $this->role === 'chef_magasinier';
    }

    public function isMagasinier()
    {
        return $this->role === 'magasinier';
    }

    public function isVendeur()
    {
        return $this->role === 'vendeur';
    }

    public function isCaissier()
    {
        return $this->role === 'caissier';
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESS CHECK
    |--------------------------------------------------------------------------
    */

    public function hasRole($roles)
    {
        if (is_array($roles)) {

            return in_array($this->role, $roles);
        }

        return $this->role === $roles;
    }
}
