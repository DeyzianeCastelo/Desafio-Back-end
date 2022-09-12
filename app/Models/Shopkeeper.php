<?php

namespace App\Models;

use App\Models\Transactions\Wallet;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Laravel\Passport\HasApiTokens;

class Shopkeeper extends Model implements AuthenticatableContract, AuthorizableContract
{

    use HasApiTokens,Authenticatable, Authorizable, HasFactory;


    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id','name', 'email','document_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        'password',
    ];

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }
}
