<?php

namespace App\Traits;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Exception;

trait StandardRepo {

    /* Preparation ***********************************************/

    public function validator ($payload, $needle, $messages) {
       return Validator::make($payload, $needle, $messages);
    }

    public function auth() {
        $res = H_toArrayObject(Auth::user());
        return $res;
    }

    public function userId() {
        $userId = $this->auth();
        return $userId ? $userId->id : null;
    }

    public function columns () : array {
        return $this->model->getColumns();
    }

    public function tableName () : string {
        $res = $this->model->getTable();
        return $res;
    }

    public function modelName () : string {
        return H_getModelName($this->model);
    }

    public function validateColumns ($name) : bool {
        try {
            $columns = $this->columns();
            /* APPEND : Relation Column
            append available column in relation so that can be searcable
            and will be append into $columns to pass validation
            */
            if(method_exists($this->model, 'searchRelations')) {
                $searchRelations = $this->model->searchRelations();
                foreach ($searchRelations as $relation => $cols) {
                    foreach ($cols as $col) { $columns[] = $relation.'.'.$col; }
                }
            }

            $valid = false;
            foreach ($columns as $col) {
                $mixCol = $col.'!'; // important / exact search
                // for integer operator
                $gt     = $col.H_getOperatorType('gt'); // greater than
                $gte    = $col.H_getOperatorType('gte'); // greater than equals
                $lt     = $col.H_getOperatorType('lt'); // less than
                $lte    = $col.H_getOperatorType('lte'); // less than equals
                // for date operator
                $gtd     = $col.H_getOperatorType('gtd'); // greater than
                $gted    = $col.H_getOperatorType('gted'); // greater than equals
                $ltd     = $col.H_getOperatorType('ltd'); // less than
                $lted    = $col.H_getOperatorType('lted'); // less than equals
                $start   = $col.H_getOperatorType('start'); // start point
                $end     = $col.H_getOperatorType('end'); // end point
                if (
                    $name == $col
                    || $name == $mixCol
                    || $name == $gt
                    || $name == $gte
                    || $name == $lt
                    || $name == $lte
                    || $name == $gtd
                    || $name == $gted
                    || $name == $ltd
                    || $name == $lted
                    || $name == $start
                    || $name == $end
                ) {
                    $valid = true;
                    break;
                }
                else $valid = false;
            }

            // dd($name, $valid, $columns);
            return $valid;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '[StandardRepo::validateColumns]'));
        }
    }

    public function validateAppends ($name) : bool {
        $name = str_replace(' ', '', $name);
        if(method_exists($this->model, H_makeAppendName($name))) return true;
        return false;
    }

    public function validateWith ($name) : bool {
        $name = str_replace(' ', '', $name);
        if(method_exists($this->model, $name)) return true;
        return false;
    }

    /* Fetching Data ***********************************************/

    public function isExist($id, $relation = [], $withThrow = true) : object {
        try {
            $res = $this->model->whereId($id)->with($relation)->first();
            if ($withThrow && !$res) H_dataNotFound($id, 'id', 'Data');
            return $res;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '[StandardRepo::isExist]'));
        }
    }


    /**  Fetching data by url with custom query
     *
     * @param Object $raw_request : "Illuminate\Http\Request" instance
     * @param Array $relations : <ArrayValue>
     */
    /* USAGE
    # makesure column available to search has defined on model in function "filterList", ex :
    `public function filterList() {
        return [
            [
                'id'        => 'column_name',
                'name'      => 'Label',
                'type'      => 'input', // input, select, date
                'options'   => [],
                'sources'   => null,
                'key_value' => null,
                'operator'  => H_getOperatorSearchList(),
            ]
        ];
    }`

    # Reserve keywords :
    _columns    : define select columns
    _limit      : define limit data | for fetch all data set = 0
    _page       : define page / offset data
    _contains   : define like query more than 1 columns
    _in         : define IN query
    _not_in     : define NOT IN query
    _order      : define order data
    _trash      : define get trash data
    _all        : define get all data (with trashed)
    _raw        : define using Raw Query
    _first      : define to get first data and return object | not work in table
    _appends    : define to get appends data

    # Searching
    syntax                  : ?{colum_name}={value} | ?{colum_name}={operator}:{value}
    - default (equal)       : ?colum_name=value
    - custom operator       : ?colum_name=operator:value

    ## Available Operator
    - is_null               : ?colum_name=is_null
    - is_not_null           : ?colum_name=is_not_null
    - not (not equal)       : ?colum_name=not:value
    - like                  : ?colum_name=like:value

    > greater, less with equal integer
    - greater than          : ?column_name=gt:integerValue
    - greater than equal    : ?column_name=gte:integerValue
    - less than             : ?column_name=lt:integerValue
    - less than equal       : ?column_name=lte:integerValue

    > greater, less with equal date/dateTime format
    - greater than          : ?column_name=gtd:dateValue
    - greater than equal    : ?column_name=gted:dateValue
    - less than             : ?column_name=ltd:dateValue
    - less than equal       : ?column_name=lted:dateValue

    > date start & end point
    - start                 : ?column_name=start:dateValue
    - end                   : ?column_name=end:dateValue

    ## Search Contains
    Searching multiple columns, place the column with coma separator
    USAGE                   : ?_contains=column_one,column_two:value

    ## Search IN & NOT IT
    can do multiple columns
    use "|" for separator columns & value of column
    use "," for separator value
    USAGE                   : ?_in=column:value1,value2
    USAGE (multiple column) : ?_in=age|score:21,22,23,24,25|80,100
    if want use NOT IT, just replace "_in" into "_not_in"

    ## Order
    - ASCENDING             : ?order=column:asc
    - DESCENDING            : ?order=column:desc

    ## Misc
    - deleted               : ?_trash
    - active & deleted      : ?_all
    - select specific column : ?_columns=col1,col2,...

    ## Search by Relation
    searching data with relation with specific column
    * USAGE
    - default               : ?{relationName}.{column}={value}
    - custom operator       : ?{relationName}.{column}={operator}:{value}
    ex ?Role.name=like:Admin

    ! makesure relation has mapped in model, in function searchRelations, ex :
    `public function searchRelations() {
        return [
            'relationName' => (new ModelName())->getColumns(),
        ];
    }`

    */
    public function searchable (Request $raw_request, $relations = []) : mixed {
        try {
            $payload = $raw_request->all();

            /* Properties */
            $isRaw      = H_keyExist($payload, '_raw'); // flag to exec raw query
            $select     = $this->getSelectFromUrl($payload);
            $with       = $this->getWithFromUrl($payload);
            $appends    = $this->getAppendsFromUrl($payload);
            $inQuery    = $this->getInQuery($payload);
            $notInQuery = $this->getInQuery($payload, true);

            if (count($with)) $relations = $with; // relation will be overide by "with"

            $dataType = 'default';
            if (H_keyExist($payload, '_all')) $dataType = 'all';
            if (H_keyExist($payload, '_trash')) $dataType = 'trash';

            /* Setup for using eloquent or raw */
            if ($isRaw) {
                $data = DB::table($this->tableName())->select($select);
                if ($dataType == 'trash') $data = $data->whereNotNull('deleted_at');
                elseif ($dataType == 'default') $data = $data->whereNull('deleted_at');
            } else {
                $data = $this->model->select($select)->with($relations);
                if ($dataType == 'trash') $data = $data->onlyTrashed();
                elseif ($dataType == 'all') $data = $data->withTrashed();
            }

            /* WHERE Condition maker */
            $filterList = $this->model->filterList(); // getting filter list columns default
            foreach ($this->columns() as $k => $col) {
                $column = H_findArrayByKey($filterList, 'id', $col);
                $isJson = ($column && isset($column['multiple'])) ? $column['multiple'] : false;

                $value = H_hasProps($payload, $col);
                if ($value) {
                    if ($isJson){
                        $value = H_arrayForJsonQuery(H_hasProps($payload, $col));
                        $data->whereRaw('JSON_CONTAINS(' .$col. ', ?)', [$value]);
                    } else $data = $this->makeWhereQuery($data, $col, $value);
                }
            }

            /* Searching to multiple columns using like (Contains) */
            $contains = null;
            if (isset($payload['_contains'])) $contains = H_extractContains($payload['_contains']);
            $data = $this->makeWHereContainQuery($data, $filterList, $contains);

            /* Searching IN & NOT IN condition */
            foreach ($inQuery as $var) {
                $data = $data->whereIn($var->column, $var->value);
            }
            foreach ($notInQuery as $var) {
                $data = $data->whereNotIn($var->column, $var->value);
            }

            /* Search Realation maker */
            $searchRelations = $this->model->searchRelations();
            foreach ($searchRelations as $relation => $columns) {
                foreach ($columns as $column) {
                    $needle = $relation."_".$column; // using undescore because key auto replaced by laravel Request
                    $value = H_hasProps($payload, $needle);
                    if ($value) {
                        $queries = H_extractOperatorAndValue(explode('|', $value));
                        foreach ($queries as $q) {
                            $operator = $q->operator;
                            $fixValue = $q->value;
                            $data = $data->whereHas($relation, function ($raw) use ($column, $fixValue, $operator) {

                                // handle is null & not null
                                $nulledOperator = false;
                                if ($operator) {
                                    if ($operator == 'is_null') {
                                        $raw->whereRaw($column .' IS NULL');
                                        $nulledOperator = true;
                                    }
                                    elseif ($operator == 'is_not_null') {
                                        $raw->whereRaw($column .' IS NOT NULL');
                                        $nulledOperator = true;
                                    }
                                }

                                // handle custom statement
                                if ($operator && $fixValue !== null ) {
                                    if (!$nulledOperator) {
                                        if ($operator == 'like') $raw->whereRaw($column .' LIKE ?', ['%'.$fixValue.'%']);
                                        else $raw->where($column, $operator, $fixValue);
                                    }
                                }

                                // default
                                if (empty($operator)) $raw->where($column, $fixValue);



                            });
                        }
                    }
                }

            }

            /* ORDERING Condition */
            $order = isset($payload['_order']) ? H_extractValueParams($payload['_order']) : [];
            if (count($order)) $data = $this->makeOrderQuery($data, $order);

            // dd($data->toSql());
            $data = $this->dynamicList($data, $payload, $appends);

            return $data;

        } catch (Exception $e){
            throw new Exception(H_throw($e, '[StandardRepo::searchable]'));
        }

    }

    public function getInQuery($payload, $notIn = false) {
        try {
            $params = H_hasProps($payload, $notIn ? '_not_in' : '_in');
            $vars = H_extractInQuery($params);
            $columns = [];
            foreach ($vars as $var) {
                if ($this->validateColumns($var->column)) $columns[] = $var;
            }
            return $columns;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '[StandardRepo::getInQuery]'));
        }
    }

    public function getSelectFromUrl ($payload, $attr = '_columns', $default = []) : array {
        try {
            $res = [];
            $columns = count($default) == 0 ? $this->columns() : $default;
            // overide default with data from url
            $data = H_hasProps($payload, $attr);
            if ($data) $columns = explode(',', $data);
            foreach ($columns as $val) {
                if ($this->validateColumns($val)) $res[] = $val;
            }
            return $res;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '[StandardRepo::getSelectFromUrl]'));
        }
    }

    public function getWithFromUrl ($payload, $default = [], $attr = '_with') : array {
        try {
            $res = $default;
            $data = H_hasProps($payload, $attr); // get by custom column
            if ($data) {
                $data = explode(',', $data);
                $res = [];
                foreach ($data as $val) {
                    if ($this->validateWith($val)) $res[] = $val;
                }
            }
            return $res;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '[StandardRepo::getWithFromUrl]'));
        }
    }

    public function getAppendsFromUrl ($payload, $default = [], $attr = '_appends') : array {
        try {
            $res = $default;
            $data = H_hasProps($payload, $attr); // get by custom column
            if ($data) {
                $data = explode(',', $data);
                $res = [];
                foreach ($data as $val) {
                    if ($this->validateAppends($val)) $res[] = $val;
                }
            }
            return $res;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '[StandardRepo::getAppendsFromUrl]'));
        }
    }

    function getPaginationLimit($config) {
        if (array_key_exists('_limit', $config)) {
            $limit = (int)$config['_limit'];
        } else {
            $limit = (int)env('PAGINATION_LIMIT', 5);
        }
        return $limit;
    }

    public function dynamicList (Object $raw_data, Array $config = null, $appends = []) : mixed {
        try {
            $first        = H_keyExist($config, '_first'); // flag to get first data | _denied table flag
            $limit        = $this->getPaginationLimit($config);
            $currentPage  = H_hasProps($config, '_page', 1);
            $tableMode    = H_checkProps($config, '_table');

            if ($first) {
                $data = $raw_data->first();
                if ($data) $data = $this->setAppends(H_toArrayObject($data), $appends);
            } else {
                // setup paging
                $totalData    = $raw_data->count();
                $skip         = ($currentPage - 1) * $limit;
                if ($limit === 0) $data = H_toArrayObject($raw_data->get());
                else $data = H_toArrayObject($raw_data->skip($skip)->limit($limit)->get());
                // setup appends
                foreach ($data as $i => $row) {
                    $row = $this->setAppends($row, $appends);
                }

                if ($tableMode) {
                    $config = [
                        'path' => request()->url(),
                        'pageName' => '_page'
                    ];
                    $paginator = new LengthAwarePaginator($data, $totalData, $limit, $currentPage, $config);
                    $paginator->withQueryString()->render();
                    return $paginator;
                }
            }
            return $data;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '[StandardRepo::dynamicList]'));
        }
    }

    public function paging (Object $raw_data, Array $config = null) : mixed {
        try {
            $limit        = $this->getPaginationLimit($config);
            $currentPage  = H_hasProps($config, '_page', 1);
            $skip         = ($currentPage - 1) * $limit;

            if ($limit === 0) $data = $raw_data->get();
            else $data = $raw_data->skip($skip)->limit($limit)->get();

            return $data;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '[StandardRepo::paging]'));
        }
    }

    public function setAppends($data, $appends) : object {
        try {
            foreach ($appends as $append) {
				$append = str_replace(' ', '', $append);
				$appendFunc = H_makeAppendName($append);
				if(method_exists($this->model, "$appendFunc")) {
					 $data->{$append} = $this->model->{$appendFunc}($data);
				}
            }
            return $data;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '[StandardRepo::setAppends]'));
        }
    }

    // multiple query using "|" for separate condition in same columns
    public function makeWhereQuery ($raw_model, $column, $value) {
        try {
            $queries = H_extractOperatorAndValue(explode('|', $value));
            if (!count($queries)) return $raw_model;
            foreach ($queries as $q) {

                $operator = $q->operator;
                $fixValue = $q->value;

                // handle is null & not null
                $nulledOperator = false;
                if ($operator) {
                    if ($operator == 'is_null') {
                        $raw_model = $raw_model->whereRaw($column .' IS NULL');
                        $nulledOperator = true;
                    }
                    elseif ($operator == 'is_not_null') {

                        $raw_model = $raw_model->whereRaw($column .' IS NOT NULL');
                        $nulledOperator = true;
                    }
                }

                // handle custom statement
                if ($operator && $fixValue !== null ) {
                    if (!$nulledOperator) {
                        if ($operator == 'like') $raw_model = $raw_model->whereRaw($column .' LIKE ?', ['%'.$fixValue.'%']);
                        else $raw_model = $raw_model->where($column, $operator, $fixValue);
                    }
                }

                // default
                if (empty($operator)) $raw_model = $raw_model->where($column, $fixValue);

            }
            return $raw_model;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '[StandardRepo::makeWhereQuery]'));
        }
    }

    public function makeSearchRelationQuery ($raw_model, $column, $value) {
        try {
            $queries = H_extractOperatorAndValue(explode('|', $value));
            if (!count($queries)) return $raw_model;
            foreach ($queries as $q) {
                $usingRelation = $q->using_relation;
                $operator = $q->operator;
                $fixValue = $q->value;

                if ($usingRelation) {
                    $column = $usingRelation['column'];
                    $raw_model = $raw_model->whereHas($usingRelation['model'], function ($q) use ($column, $fixValue, $operator) {
                        // querying
                        $raw = $q;
                        // handle is null & not null
                        $nulledOperator = false;
                        if ($operator) {
                            if ($operator == 'is_null') {
                                $raw->whereRaw($column .' IS NULL');
                                $nulledOperator = true;
                            }
                            if ($operator == 'is_not_null') {
                                $raw->whereRaw($column .' IS NOT NULL');
                                $nulledOperator = true;
                            }
                        }

                        // handle custom statement
                        if ($fixValue !== null ) {
                            if (!$nulledOperator) {
                                if (!$operator) $raw->where($column, $fixValue);
                                else {
                                    if ($operator == 'like') $raw->whereRaw($column .' LIKE ?', ['%'.$fixValue.'%']);
                                    else $raw->where($column, $operator, $fixValue);
                                }
                            }
                        }
                    });
                } else {
                    // handle is null & not null
                    $nulledOperator = false;
                    if ($operator) {
                        if ($operator == 'is_null') {
                            $raw_model = $raw_model->whereRaw($column .' IS NULL');
                            $nulledOperator = true;
                        }
                        elseif ($operator == 'is_not_null') {
                            $raw_model = $raw_model->whereRaw($column .' IS NOT NULL');
                            $nulledOperator = true;
                        }
                    }

                    // handle custom statement
                    if ($operator && $fixValue !== null ) {
                        if (!$nulledOperator) {
                            if ($operator == 'like') $raw_model = $raw_model->whereRaw($column .' LIKE ?', ['%'.$fixValue.'%']);
                            else $raw_model = $raw_model->where($column, $operator, $fixValue);
                        }
                    }

                    // default
                    if (empty($operator)) $raw_model = $raw_model->where($column, $fixValue);
                }

            }
            return $raw_model;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '[StandardRepo::makeWhereQuery]'));
        }
    }

    public function makeWHereContainQuery ($instance, $columns, $contains) {
        try {
            if ($contains) {
                $instance = $instance->where(function($q) use($columns, $contains) {
                    $index = 0;
                    foreach($contains->columns as $r) {
                        if (in_array($r, $this->columns())) {

                            $check = H_findArrayByKey($columns, 'id', $r);
                            $isJson = ($check && isset($check->multiple)) ? true : false;
                            $value = H_formatValueType($contains->value);
                            if ($isJson) {
                                $value = H_arrayForJsonQuery($contains->value);
                                if ($index === 0) $q->whereRaw('JSON_CONTAINS(' .$r. ', ?)', [$value]);
                                else $q->orWhereRaw('JSON_CONTAINS(' .$r. ', ?)', [$value]);
                            }
                            else {
                                if ($index === 0) $q->whereRaw("$r LIKE ?", ['%'.$value.'%']);
                                else $q->orWhereRaw("$r LIKE ?", ['%'.$value.'%']);
                            }

                            $index++;
                        }
                    }
                });
            }
            return $instance;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '[StandardRepo::makeWHereContainQuery]'));
        }
    }

    public function makeOrderQuery ($instance, $order = []) {
        try {
            foreach ($order as $key => $params) {
                if ($this->validateColumns($params->key)) {
                    $key = H_escapeStringTable($params->key);
                    $value = strtoupper(H_escapeString($params->value));
                    if ($value == 'ASC' || $value == 'DESC') $data = $instance->orderBy($key, $value);
                }
            }
            return $instance;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '[StandardRepo::makeOrderQuery]'));
        }
    }

    /* Injector ***********************************************/

    public function setLogHistory (&$model) {
        try {
            if (env('LOG_USER', false)) {
                if ($model->id) $model->updated_by = $this->userId();
                else $model->created_by = $this->userId();
            }

            if (env('LOG_IP', false)) {
                if ($model->id)  $model->updated_ip = H_getIpClient();
                else $model->created_ip = H_getIpClient();
            }
            return $model;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '[StandardRepo::setLogHistory]'));
        }
    }

    public function setter (&$model, $payload, $attr) {
        try {
            if (H_hasProps($payload, $attr)) $model->{$attr} = H_hasProps($payload, $attr);
            return $model;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '[StandardRepo::setter]'));
        }
    }

    /* Utils ***********************************************/

    public function countData ($withTrash = true) : int {
        $total = $this->model->query();
        if ($withTrash) $total = $total->withTrashed();
        $total = $total->count();
        return $total ? $total : 0;
    }

    public function countDataInPeriode ($date_selector = 'created_at', $in_periode = false) : int {
        $today = H_today();
        $year = H_formatDate($today, 'Y');
        $month = H_formatDate($today, 'm');
        $total = $this->model->query();
        if ($in_periode) $total = $total->whereMonth($date_selector, $month)->whereYear($date_selector, $year);
        $total = $total->withTrashed()->count();
        return $total ? $total : 0;
    }

    public function checkDataBy ($column, $value, $softDelete = true) : bool {
        $data = DB::table($this->tableName())->select($column)->where($column, $value);
        if ($softDelete) $data = $data->whereNull('deleted_at');
        $data = $data->first();
        return $data ? true : false;
    }

    public function generateCode( $prefix, $length = 9, $index = 0) {
        try {
            if ($index < 1) $index = 1 + $this->model->query()->withTrashed()->count();
            return $prefix.'-'.str_pad($index, $length, '0', STR_PAD_LEFT);
        } catch (Exception $e) {
            throw new Exception(H_throw($e, '[StandardRepo::generateCode]'));
        }
    }

    public function generateSerial ($prefix, $format = '{prefix}/{year}/{month}/{serial_in_periode}', $length = 5, $index = 0, $date_selector = 'created_at') {
        try {
            $today = H_today();
            $year = H_formatDate($today, 'Y');
            $month = H_formatDate($today, 'm');

            $counter = 1 + $this->countData();
            $counter_in_periode = 1 + $this->countDataInPeriode($date_selector, true);

            if ($index < 1) $index = $index = 1 + $this->model->query()->withTrashed()->count();

            $params = [
                "prefix" => $prefix,
                "counter" => $counter,
                "counter_in_periode" => $counter_in_periode,
                "index" => $index,
                "serial" => H_makeSerial(null, $length, $counter),
                "serial_in_periode" => H_makeSerial(null, $length, $counter_in_periode),
                "year" => $year,
                "year_roman" => H_toRoman($year),
                "month" => $month,
                "month_roman" => H_toRoman($month),
                "month_name" => H_formatDate($today, 'M'),
                "month_full_name" => H_formatDate($today, 'F'),
            ];

            foreach($params as $key => $val) {
                $format = str_replace("{".$key."}", $val, $format);
            }

            return $format;
        } catch (Exception $e) {
            throw new Exception(H_throw($e, '[StandardRepo::generateSerial]'));
        }
    }

    /**
     * Delete & Restore data multiple
     *
     * @param array $ids : array value of id
     * @param string $mode : flaging for : delete | restore
     * @param boolean $permanent : trigger for delete permanent or soft delete (default)
     * @param int $userId : set default action user, if not set, login validation will exec
     */
    public function deleteRestoreBatch ($ids, $mode = 'delete', $permanent = false, $userId = null) : void {
        try {
            if (!$userId) {
                $userId = $this->userId();
                if (!$userId) throw new Exception("You cannot perform this, please login first!");
            }

            $today = H_today();
            $table = $this->tableName();

            $logIp = '';
            if (env('LOG_IP', false)) $logIp = ",deleted_ip = '".H_getIpClient()."'";

            if ($mode == 'delete') {
                $set = "deleted_at = '$today' $logIp";
                if (env('LOG_USER', false)) $set = "$set, deleted_by = '$userId'";
            } else {
                $set = "updated_at = '$today', deleted_at = NULL $logIp";
                if (env('LOG_USER', false)) $set = "$set, deleted_by = NULL, updated_by = '$userId'";
            }

            $id = implode(',', $ids);
            if ($permanent) DB::select("DELETE $table WHERE id IN($id) ");
            else DB::select("UPDATE $table SET $set WHERE id IN($id) ");


        } catch (Exception $e) {
            throw new Exception(H_throw($e, '[StandardRepo::deleteRestoreBatch]'));
        }
    }

    public function DB ($table) {
        return DB::table($table);
    }

}
