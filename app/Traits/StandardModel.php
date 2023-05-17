<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Exception;

/*  Appends & Attributes
    Since the function "get{YouAttr}Attribute()" is also used in StandardRepo, 
    therefore you should customize the function by adding default data arguments 
    with values according to the model structure in this module.

    because there are adjustments regarding the use of "appends" manually, 
    in the query builder that we provide, therefore there are additional rules 
    that you must do so that the results meet your expectations.

    1. Add the first argument to your "getAttribute" function which contains the fetched data value.
    2. You have to add a function from the StandardModel, namely "overideModelAttribute", aiming to update an attribute that has not been set.

    here is full example if you want to implement to get new attribute named "full_name"

    public function getFullNameAttribute ($data = null) {
        try {
            $this->overideModelAttribute($this, $data);

            return "{$this->name} {$this->last_name}";
        } catch (\Exception $e){
            throw new \Exception(H_throw($e, '[Users::getFullNameAttribute]'));
        }
    }
*/

trait StandardModel {

    // get log data
    public function getLogDataAttribute () {
        $created_by = $this->created_by ?? 0;
        $updated_by = $this->updated_by ?? 0;
        $deleted_by = $this->deleted_by ?? 0;
        $data = DB::table($this->table)->select([
            DB::raw("(SELECT username FROM users WHERE id = $created_by LIMIT 1 ) as created_by_name"),
            DB::raw("(SELECT username FROM users WHERE id = $updated_by LIMIT 1 ) as updated_by_name"),
            DB::raw("(SELECT username FROM users WHERE id = $deleted_by LIMIT 1 ) as deleted_by_name"),
            'created_at', 'updated_at', 'deleted_at',
            'created_ip', 'updated_ip', 'deleted_ip',
        ])
        ->where('id', $this->id)
        ->first();
        return [
            "created" => [
                "by" =>  $data ? $data->created_by_name : null,
                "at" =>  $data ? H_formatDate($data->created_at) : null,
                "ip" =>  $data ? $data->created_ip : null,
            ],
            "updated" => [
                "by" =>  $data ? $data->updated_by_name : null,
                "at" =>  $data ? H_formatDate($data->updated_at) : null,
                "ip" =>  $data ? $data->updated_ip : null,
            ],
            "deleted" => [
                "by" =>  $data ? $data->deleted_by_name : null,
                "at" =>  $data ? H_formatDate($data->deleted_at) : null,
                "ip" =>  $data ? $data->deleted_ip : null,
            ],
        ];
    }

    // manual casting handler
    protected function castAttribute($key, $value) : mixed {
        try {
            if ($this->getCastType($key) == 'array' && is_null($value)) {
                return [];
            }
            return parent::castAttribute($key, $value);
        } catch (Exception $e) {
            throw new Exception(H_throw($e, '[StandardModel::filterListBase]'));
        }
    }

    // overide getColumns laravel, to fix column real needed & readable
    public function getColumns ($withLogging = true) : array {
        try {
            $res = [];
            $cols = [ 'id',
                ...$this->fillable,
            ];
            $hidden = $this->hidden;
            foreach ($cols as $col) {  // sterilize hidden
                if (!in_array($col, $hidden)) $res[] = $col;
            }

            if ($withLogging) {
                $res = [
                    ...$res,
                    'created_at',
                    'updated_at',
                    'deleted_at',
                    'created_by',
                    'updated_by',
                    'deleted_by',
                    'created_ip',
                    'updated_ip',
                    'deleted_ip',
                ];
            }

            return $res;
        } catch (Exception $e) {
            throw new Exception(H_throw($e, '[StandardModel::filterListBase]'));
        }
    }

    // Ghost / Default Methods, fo handling error method not found when use searcable
    public function searchRelations() {
        return [];
    }

    /**
     * @param $models : array object [ 'RelationName' => 'ModelName', 'RelationName2' => 'ModelName2' ]
     */
    public function bindSearchRelation ($models = []) : array {
        try {
            $res = [];
            foreach ($models as $relationName => $modelName) {
                $model = "App\Models\\$modelName";
                $model = new $model();
                $res[$relationName] = $model->getColumns();
            }
            return $res;
        } catch (Exception $e) {
            throw new Exception(H_throw($e, '[StandardModel::bindSearchRelation]'));
        }
    }

    /* SEARCH FILTER 
        this method needed for searchable validation & filter list on UI, for automaticaly set column filter 
        # interface / format:
        [
            'id'        => 'work_id', // column name
            'name'      => 'work_id', // label 
            'type'      => 'input', // input, select, date
            'options'   => [], // : for select
            'sources'   => null, // : for select serverside (endpoint / path API)
            'key_value' => null, // : for select & select serverside
            'operator'  => H_getOperatorSearchList(),
            'multiple'  => false // false / true : for select & select serverside
        ]
    */
    public function filterListBase () : array {
        try {
            $res = [];
            foreach ($this->getColumns(false) as $col) {
                $res[] = [
                    'id'        => $col,
                    'name'      => ucwords($col),
                    'type'      => 'input',
                    'options'   => [],
                    'sources'   => null, 
                    'key_value' => null,
                    'key_label' => null,
                    'operator'  => H_getOperatorSearchList(H_filterTableOperatorDefault('all')),
                    'multiple'  => false,
                    'hidden'  => false,
                ];
            }
            return $res;
        } catch (Exception $e) {
            throw new Exception(H_throw($e, '[StandardModel::filterListBase]'));
        }
    }

    public function mergeFilterList($old, $override) : array {
        try {
            foreach($override as $newItem) {
                $found = false;
                foreach($old as &$oldItem) {
                    if($newItem['id'] == $oldItem['id']) {
                        $oldItem = array_replace_recursive($oldItem, $newItem);
                        $found = true;
                        break;
                    }
                }
                if(!$found) {
                    $old[] = $newItem;
                }
            }
            return $old;
        } catch (Exception $e) {
            throw new Exception(H_throw($e, '[StandardModel::mergeFilterList]'));
        }
    }

    public function DB ($table) {
        return DB::table($table);
    }

    public function overideModelAttribute (&$instance, $data) {
        foreach ($data as $key => $val) {
            $this->setAttribute($key,  $val);
        }
    }


}