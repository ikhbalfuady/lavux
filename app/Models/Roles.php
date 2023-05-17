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
 * @property unsignedBigInteger $role_group_id 
 * @property string $name 
 * @property string $slug 

 */
class Roles extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes, StandardModel, Roles_H; 

    protected $table = 'roles';
    protected $primaryKey = 'id';

    protected $fillable = [
        'role_group_id', 
        'name', 
        'slug'
    ];

    public $timestamps = true;
    protected $guarded = ['id'];

    protected $casts = [
        'role_group_id'=> 'integer', 
        'name'=> 'string', 
        'slug'=> 'string'
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
            'RoleGroup' => 'RoleGroups',

            ]);
        } catch (\Exception $e){
            throw new \Exception(H_throw($e, H_getModelName(get_class()).'::searchRelations]'));
        }
    }

    /* Relations : belongsTo */

    public function RoleGroup() {
        return $this->belongsTo(RoleGroups::class, 'role_group_id', 'id')->withTrashed();
    }



    /* Relations : hasMany */



    /* Relations : morphTo */



}        
        