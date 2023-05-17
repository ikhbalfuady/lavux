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
 * @property enum $type 
 * @property string $slug 
 * @property string $name 
 * @property text $value 
 * @property text $description 
 * @property text $remarks 

 */
class Metas extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes, StandardModel, Metas_H; 

    protected $table = 'metas';
    protected $primaryKey = 'id';

    protected $fillable = [
        'type', 
        'slug', 
        'name', 
        'value', 
        'description', 
        'remarks'
    ];

    public $timestamps = true;
    protected $guarded = ['id'];

    protected $casts = [
        'type'=> 'string', 
        'slug'=> 'string', 
        'name'=> 'string', 
        'value'=> 'string', 
        'description'=> 'string', 
        'remarks'=> 'string'
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
        