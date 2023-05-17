<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\StandardModel;

/**
 * @property bigIncrements $id 
 * @property string $name 
 * @property string $username 
 * @property string $password 
 * @property string $email 
 * @property dateTime $email_verified_at 
 * @property string $remember_token 
 * @property text $picture 
 * @property unsignedBigInteger $role_id 
 * @property boolean $is_ban 

 */
class Users extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes, StandardModel, Users_H; 

    protected $table = 'users';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'username',
        'email',
        'picture',
        'password',
        'email_verified_at',
        'remember_token',
        'role_id',
        'is_ban'
    ];

    public $timestamps = true;
    protected $guarded = ['id'];

    protected $hidden = [
        'remember_token',
        'password',
    ];

    protected $casts = [
        'name'=> 'string', 
        'username'=> 'string', 
        'password'=> 'string', 
        'email'=> 'string', 
        'email_verified_at'=> 'string', 
        'remember_token'=> 'string', 
        'picture'=> 'string', 
        'role_id'=> 'integer', 
        'is_ban'=> 'boolean'
    ];

    // automatic appends attributes
    protected $appends = [
        'log_data' // log data, from StandardModel
    ];

    /** Search Relations 
    *   for availability searching by relation data, needed in StandardRepo
    */
    public function searchRelations() {
        try {
            return $this->bindSearchRelation([
                'Role' => 'Roles',
            ]);
        } catch (\Exception $e){
            throw new \Exception(H_throw($e, '['.H_getModelName(get_class()).'::searchRelations]'));
        }
    }

    /* Relations */

    public function Role() {
        return $this->belongsTo(Roles::class, 'role_id', 'id')->withTrashed();
    }

    public function getRoleNameAttribute ($data = null) {
        try {
            $this->overideModelAttribute($this, $data);
            $res = $this->DB((new Roles)->getTable())->select('name')->whereId($this->role_id)->first();
            return $res ? $res->name : $res;
        } catch (\Exception $e){
            throw new \Exception(H_throw($e, '['.H_getModelName(get_class()).'::getRoleNameAttribute]'));
        }
    }


}        
        