<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\StandardModel;

/**
 * @property bigIncrements $id 
 * @property unsignedBigInteger $permission_id 
 * @property unsignedBigInteger $role_id 

 */
class RolePermissions extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes, StandardModel, RolePermissions_H; 

    protected $table = 'role_permissions';
    protected $primaryKey = 'id';

    protected $fillable = [
        'permission_id', 
        'role_id'
    ];

    public $timestamps = true;
    protected $guarded = ['id'];

    protected $casts = [
        'permission_id'=> 'integer', 
        'role_id'=> 'integer'
    ];

    // automatic appends attributes
    protected $appends = [
        'log_data' // default log data, from StandardModel
        // 'your_attr'
    ];

    /** Search Relations
    *   for availability searching by relation data, needed in StandardRepo
    */
    public function searchRelations() {
        try {
            return $this->bindSearchRelation([
            'Permission' => 'Permissions',
            'Role' => 'Roles',

            ]);
        } catch (\Exception $e){
            throw new \Exception(H_throw($e, H_getModelName(get_class()).'::searchRelations]'));
        }
    }

    /* Relations : belongsTo */

    public function Permission() {
        return $this->belongsTo(Permissions::class, 'permission_id', 'id')->withTrashed();
    }

    public function Role() {
        return $this->belongsTo(Roles::class, 'role_id', 'id')->withTrashed();
    }



    /* Relations : hasMany */



    /* Relations : morphTo */



}        
        