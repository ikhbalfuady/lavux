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
 * @property string $module 
 * @property string $name 

 */
class Permissions extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes, StandardModel, Permissions_H; 

    protected $table = 'permissions';
    protected $primaryKey = 'id';

    protected $fillable = [
        'module', 
        'name'
    ];

    public $timestamps = true;
    protected $guarded = ['id'];

    protected $casts = [
        'module'=> 'string', 
        'name'=> 'string'
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

            ]);
        } catch (\Exception $e){
            throw new \Exception(H_throw($e, H_getModelName(get_class()).'::searchRelations]'));
        }
    }

    /* Relations : belongsTo */



    /* Relations : hasMany */



    /* Relations : morphTo */



}        
        