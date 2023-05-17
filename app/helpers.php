<?php
use Carbon\Carbon;

/* Response Basic */


function H_apiResponse ($data, $msg = 'success', $code = 200) {
    $res = array(
        'message' => $msg,
        'data' => $data
    );
    return response($res, $code);
}

function H_api403 () {
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Headers: *");
    header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
    header("HTTP/1.0 403");
    $result = array(
        "message"=> "You don't have permission to perform this!",
        "data"=> null,
    );
    echo json_encode($result);
    die();
}

function H_apiResError ($e, $skipError = false) {

    $message = 'ERROR';

    if (gettype($e) === 'array') {
        if (isset($e['code'])) $code = $e['code'];
        if (isset($e['message'])) $message = $e['message'];
    } else $message = $e->getMessage();

    if ($skipError) {
        $message = "Data successfuly store with Error, please Screenshoot this and contact the Administrator! <br> Error : <br>";
        return H_apiResponse(null, $message, 200);
    } else return H_apiResponse(null, $message, 400);
}

function H_dataNotFound ($id, $col = 'id', $module = "Data") {
    throw new Exception(H_404Message($id, $col, $module));
}

function H_404Message ($id, $col = 'id', $module = "Data") { // slug is for debuging , with deafault value = [Model::methodName]
    return "$module with $col ($id) not found!.";
}

function H_throw (Exception $e, $slug = null) { // slug is for debuging , with deafault value = [Model::methodName]
    $msg = (env('APP_ENV') == 'local') ? "$slug " : null;
    $msg = $msg . " > " . H_getMessageException($e);
    return $msg;
}

function H_redirect ($path, $host = null) {
    $host = $host ? $host : env('APP_URL');
    header("Location: $host" . $path);
    die();
}

function H_redirectApi ($path, $host = null) {
    $host = $host ? $host : env('API_URL');
    header("Location: $host" . $path);
    die();
}

/* Date */
function H_today ($_time = true) {
    $time = '';
    if ($_time) $time = ' H:i:s';
    return date_format(Carbon::now()->setTimezone(env('APP_TIMEZONE', 'Asia/Jakarta')),"Y-m-d$time"); 
}

function H_startOfMonth()
{
    return date_format(Carbon::now()->startOfMonth(), "Y-m-d");
}

/* Accessor */

/**
 * @params $instance : instance model 
 */
function H_getModuleName ($instance) : string {
    $moduleName = str_replace(
        ['App\Models\\'],
        ['', ''],
        $instance->getMorphClass()
    );
    return $moduleName;
}

/**
 * @param $get_class_fn : set value with get_class()
 */
function H_getModelName ($get_class_fn) : string  {
    $name = str_replace(
        ['App\Models\\'],
        ['', ''],
        $get_class_fn
    );
    return $name;
}

/**
 * @param $get_class_fn : set value with get_class()
 */
function H_getRepositoryName ($get_class_fn) : string  {
    $name = str_replace(
        ['App\Repositories\\'],
        ['', ''],
        $get_class_fn
    );
    return $name;
}

/**
 * @param String $get_class_fn : set value with get_class()
 * @param Boolean $withSubfix : if true, will return with text "Controller", ex : UserController
 */
function H_getControllerName ($get_class_fn, $withSubfix = false) : string {
    $needle = ['App\Http\Controllers\API\\'];
    if (!$withSubfix) $needle[] = 'Controller';
    $name = str_replace(
        $needle,
        ['', ''],
        $get_class_fn
    );
    return $name;
}

function H_checkArrayType ($array) {
    try {
        if (is_array($array)) return 'assoc';
        else if (is_object($array)) return 'object';
        else throw new Exception("Invalid array type");
    } catch (Exception $e) {
        throw new Exception(H_throw($e, '[H_checkArrayType] '));
    }
}

function H_checkProps ($payload, $attribute) : bool {
    try {
        $type = H_checkArrayType($payload);
        $res = false;
        if ($type == 'assoc') {
            if (isset($payload[$attribute]) && $payload[$attribute] !== '') $res =  true;
            if (isset($payload[$attribute]) && $payload[$attribute] === null) $res =  false;
        } else {
            if (isset($payload->{$attribute}) && $payload->{$attribute} !== '') $res =  true;
            if (isset($payload->{$attribute}) && $payload->{$attribute} === null) $res =  false;
        }
        return $res;
    } catch (Exception $e) {
        throw new Exception(H_throw($e, '[H_checkProps] '));
    }
}

function H_hasProps ($payload, $attribute, $default = null) {
    try {
        $type = H_checkArrayType($payload);
        if ($type == 'assoc') {
            $check = H_checkProps($payload, $attribute);
            if ($check) {
                return $payload[$attribute];
            } else return $default;
        } else {
            return H_objHasProps($payload, $attribute, $default = null);
        }   
    } catch (Exception $e) {
        throw new Exception(H_throw($e, '[H_hasProps] '));
    }
}

// aliases H_hasProps, for processing object
function H_objHasProps ($obj, $attribute, $default = null) {
    return ($obj && isset($obj->{$attribute})) ? $obj->{$attribute} : $default;
}

function H_hasPropsJson ($payload, $attribute, $default = null) {
    try {
        $check = H_checkProps($payload, $attribute);
        if ($check) {
            $type = H_checkArrayType($payload);
            if ($type == 'assoc') {
                if (is_array($payload[$attribute])) return $payload[$attribute];
                else return json_decode($payload[$attribute]);
            } else {
                if (is_array($payload->{$attribute})) return $payload->{$attribute};
                else return json_decode($payload->{$attribute});
            }
        } else return $default;
    } catch (Exception $e) {
        throw new Exception(H_throw($e, '[H_hasPropsJson] '));
    }
}

function H_hasPropsArray ($payload, $attribute, $default = null) {
    try {
        $check = H_checkProps($payload, $attribute);
        if ($check) {
            $type = H_checkArrayType($payload);
            if ($type == 'assoc') {
                $res = json_decode($payload[$attribute]);
            } else {
                $res = json_decode($payload->{$attribute});
            }

            if (count($res)) return $res;
            else return $default;
        } else return $default;
    } catch (Exception $e) {
        throw new Exception(H_throw($e, '[H_hasPropsArray] '));
    }
}

function H_keyExist ($payload, $key) {
    return array_key_exists($key, $payload);
}

function H_findArrayByKey ($array, $key, $value, $getIndex = false) {
    try {
        for ($i=0; $i < count($array) ; $i++) {
            $type = H_checkArrayType($array[$i]);
            if ($type == 'assoc') {
                if ($array[$i][$key] === $value) {
                    if ($getIndex) return $i;
                    else return $array[$i];
                }
            } else {
                if ($array[$i]->{$key} === $value) {
                    if ($getIndex) return $i;
                    else return $array[$i];
                }
            }
        }
    } catch (Exception $e) {
        throw new Exception(H_throw($e, '[H_findArrayByKey] '));
    }
}

function H_isDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
    return $d && $d->format($format) === $date;
}

function H_handlePeriodeDate($payload, $attr, $type = 'start', $_time = true) {

    $res = H_today();
    if ($type == 'start') {
        $res = H_hasProps($payload, $attr) ?? H_startOfMonth();
        $time = $_time ? ' 00:00:00' : '';
        $res = H_formatDate($res, "Y-m-d$time");
    } else {
        $res = H_hasProps($payload, $attr) ?? H_today(false);
        $time = $_time ? ' 23:59:59' : '';
        $res = H_formatDate($res, "Y-m-d$time");
    }
    return $res;
}

/* Formating */

function H_formatDate($date, $format = 'Y-m-d H:i:s') {
    return $date ? date($format, strtotime($date)) : null;
}

function H_toNumber ($value) {
    if (strpos($value, '.') !== false) return (float)$value;
    else  return (int)$value;
}

function H_toBoolean ($value) {
    $res = false;
    if ($value === 1) return true;
    elseif ($value === '1') return true;
    elseif ($value === '0') return false;
    elseif ($value === 0) return false;
    elseif ($value === 'true') return true;
    elseif ($value === 'false') return false;
    else return $res;
}

function H_toRoman ($number) {
    $map = [
        'M'  => 1000,
        'CM' => 900,
        'D'  => 500,
        'CD' => 400,
        'C'  => 100,
        'XC' => 90,
        'L'  => 50,
        'XL' => 40,
        'X'  => 10,
        'IX' => 9,
        'V'  => 5,
        'IV' => 4,
        'I'  => 1
    ];
    $returnValue = '';
    while ($number > 0) {
        foreach ($map as $roman => $int) {
            if($number >= $int) {
                $number -= $int;
                $returnValue .= $roman;
                break;
            }
        }
    }
    return $returnValue;
}

function H_timeAgo($date) {

    $today = Carbon::parse(H_today());
    $date = Carbon::parse($date);
    $time = $date->diffForHumans($today);
    return str_replace('before', 'ago', $time);
}

function H_toArrayObject ($data) {
    $data = json_encode($data);
    $data = json_decode($data);
    return $data;
}

function H_toArrayAssoc ($data) {
    $data = json_encode($data);
    $data = json_decode($data, true);
    return $data;
}

function H_escapeStringTable ($data, $additionalChar = null) {
    $hex = '/[^a-z-A-Z_!.-@'.$additionalChar.'\^0-9\x00-\x1f\x05\x0E\x16\x03]/';
    $steril = preg_replace($hex, '',$data);
    $steril = str_replace('-', '',$steril);

    return $steril;
}

function H_escapeString ($data, $additionalChar = null) {
    $steril = preg_replace('/[^A-Za-z0-9\-_ '.$additionalChar.'\x00-\x1f\x05\x0E\x16\x03]/', '', $data);
    return $steril;
}

function H_arrayForJson ($data) {
    // $steril = preg_replace('/[^A-Za-z0-9\,-_ \x00-\x1f\x05\x0E\x16\x03]/', '', $data);
    return $data;
}

function H_arrayForJsonQuery($data) {
    $value = H_arrayForJson($data); // sterilize up first
    $list = explode(',', $value);
    $res = '';
    foreach ($list as $v) {
        if (is_numeric($v)) {
            $v = H_toNumber($v);
            if ($v) $res .= "[$v]";
        } else {
            if ($v) $res .= '["'.$v.'"]'; // value must be wrapped by double quotes (") if valu is string 
        }
    }
    return $res != '' ? $res : null;
}

function H_formatValueType ($val) {
    if (is_numeric($val)) {
        $val = H_toNumber($val);
    }
    return $val;
}

function H_toCamelCase ($string) {
    $string = str_replace('_', ' ', $string);
    $string = ucwords($string);
    $string = str_replace(' ', '', $string);
    
    return $string;
}

function H_cleanStr ($string, $needle = ' ') {
    return str_replace($needle, '', $string);
}

function H_getMessageException ($e) {
    $code = $e->getCode();
    $message = $e->getMessage();
    $res = null;
    if ($code == '23000') {
        $message = substr($message, strpos($message, ':') + 2); // Mengambil pesan error saja
        $message = substr($message, 0, strpos($message, ' ('));
        $message = "[Database] $message";
    }
    return $message;
}

function H_splitUppercaseWithStrip($string) {
    $selector = preg_replace('/([a-z0-9])?([A-Z])/', '$1-$2', $string);
    if (isset($selector[0]) && $selector[0] == '-') $selector = substr($selector, 1); // hapus underscore di awal text
    return $selector;
}

function H_splitUppercaseWithUnderscore($string) {
    $selector = preg_replace('/([a-z0-9])?([A-Z])/', '$1_$2', $string);
    if (isset($selector[0]) && $selector[0] == '_') $selector = substr($selector, 1); // hapus underscore di awal text
    return $selector;
}

function H_splitUppercaseWithSpace($string) {
    $selector = preg_replace('/([a-z0-9])?([A-Z])/', '$1 $2', $string);
    if (isset($selector[0]) && $selector[0] == ' ') $selector = substr($selector, 1); // hapus underscore di awal text
    return $selector;
}

function H_makeEnumName($string) {
    $raw = H_splitUppercaseWithUnderscore($string);
    $raw = explode('_', $raw);
    $res = null;
    foreach ($raw as $val) {
        if ($val) {
            $res .= ucwords($val);
        } 
    }
    return $res;
}

function H_makeSlug($var) {
    $var = H_splitUppercaseWithSpace($var);
    $var = str_replace(' ', '-', $var);
    return strtolower($var);
}

function H_makeSlugString($var) {
    $var = H_escapeString($var, false);
    $var = str_replace(' ', '-', $var);
    $var = str_replace('--', '-', $var);
    $var = str_replace('---', '-', $var);
    $var = str_replace('----', '-', $var);
    return strtolower($var);
}

function H_columnToLabel ($value) {
    $value = str_replace('_id', '', $value);
    $value = str_replace('_', ' ', $value);
    return ucwords($value);
}

function H_fileSize($bytes, $decimals = 2) {
    $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . @$size[$factor];
}

 
/* Searchable utils */

function H_makeAppendName ($name) {
    return 'get'.H_toCamelCase($name).'Attribute';
}

function H_operatorIdentifier () {
    return ':';
}

function H_getOperatorType ($type) {
    $identifier = H_operatorIdentifier();
    if (
        // integer format
        $type == 'lt' // less than
        || $type == 'lte' // less than equal
        || $type == 'gt' // greater than
        || $type == 'gte' // greater than equal
        // date format
        || $type == 'ltd' // less than equal
        || $type == 'lted' // less than equal
        || $type == 'gtd' // greater than
        || $type == 'gted' // greater than equal
        || $type == 'start' // start point
        || $type == 'end' // end point
        || $type == 'like' // like query
        || $type == 'is_null' // get value null
        || $type == 'is_not_null' // get value not null
        || $type == 'not_equal' // get value not null
    ) {
        return $identifier.$type;
    } else return null;
}

function H_getOperatorSearch($key, $indexSelector = 1) {
    $res = null;
    $ex = explode(H_operatorIdentifier(), $key);
    if (count($ex) > 1) {
        $selector = H_cleanStr($ex[$indexSelector], ' ');
        $selector = H_cleanStr($selector, '  ');
        if ($selector == '') $res = '=';
        elseif ($selector == '=') $res = '=';
        elseif ($selector == 'not') $res = '!=';
        elseif ($selector == 'lt' || $selector == 'ltd') $res = '<';
        elseif ($selector == 'lte' || $selector == 'lted') $res = '<=';
        elseif ($selector == 'gt' || $selector == 'gtd') $res = '>';
        elseif ($selector == 'gte' || $selector == 'gted') $res = '>=';
        elseif ($selector == 'start') $res = '>=';
        elseif ($selector == 'end') $res = '<=';
        elseif ($selector == 'like') $res = 'like';
        elseif ($selector == 'is_null') $res = 'is_null';
        elseif ($selector == 'is_not_null') $res = 'is_not_null';
        else  $res = "=";
    } else $res = "="; 

    if (count($ex) == 1) {
        $selector = H_cleanStr($ex[0], ' ');
        $selector = H_cleanStr($selector, '  ');
        if ($selector == 'is_null') $res = 'is_null';
        elseif ($selector == 'is_not_null') $res = 'is_not_null';
        else  $res = "=";
    }

    return $res;
}

function H_getOperatorSearchList ($only = [], $exclude = []) {
    $list = [
        [ 'id' => '=',  'name' => 'equal (=)'],
        [ 'id' => 'not',  'name' => 'not equal (!=)'],
        [ 'id' => 'like', 'name' => 'contains'],
        [ 'id' => 'lt', 'name' => 'less than (<)'],
        [ 'id' => 'lte', 'name' => 'less than equal (<=)'],
        [ 'id' => 'gt', 'name' => 'greater than (>)'],
        [ 'id' => 'gte', 'name' => 'greater than equal (>=)'],
        // date format
        [ 'id' => 'ltd', 'name' => 'less than equal date (<)'],
        [ 'id' => 'lted', 'name' => 'less than equal date (<=)'],
        [ 'id' => 'gtd', 'name' => 'greater than date (>)'],
        [ 'id' => 'gted', 'name' => 'greater than equal date (>=)'],
        [ 'id' => 'start', 'name' => 'start date'],
        [ 'id' => 'end', 'name' => 'end end'],
        [ 'id' => 'is_null', 'name' => 'is null'],
        [ 'id' => 'is_not_null', 'name' => 'not null'],
    ];

    // if only filled, exclude will not accepted
    $res = [];
    if (count($only)) {
        foreach ($only as $val) {
            $check = H_findArrayByKey($list, 'id', $val);
            if ($check) $res[] = $check;
        }
    } else {
        foreach ($list as $val) {
            if (count($exclude)) {
                foreach ($exclude as $ex) {
                    $check = H_findArrayByKey($list, 'id', $ex);
                    if (!$check) $res[] = $val;
                }
            } else $res[] = $val; 
        } 
    }

    return $res;
}

function H_getValueFromQuery ($value, $indexSelector = 1, $additionalChar = null) {
    $ex = explode(H_operatorIdentifier(), $value, 2);
    $res = isset($ex[$indexSelector]) ? $ex[$indexSelector] : null;
    // $res = H_escapeString($res, $additionalChar); // sterilize
    return $res;
}

function H_getValueForOperator ($key, $val, $indexSelector = 1) {
    try {

        $mode = H_getOperatorMode($key, $indexSelector);
        // integer format
        if ($mode == 'lt' // less than
            || $mode == 'lte' // less than equal
            || $mode == 'gt' // greater than
            || $mode == 'gte' // greater than equal
        ) {
            $val = floatval($val);
        }

        // date format
        if ($mode == 'ltd' // less than equal
            || $mode == 'lted' // less than equal
            || $mode == 'gtd' // greater than
            || $mode == 'gted' // greater than equal
            || $mode == 'start' // start point
            || $mode == 'end' // end point
        ) {
            if ($mode == 'start') $val = date('Y-m-d', strtotime($val)).' 00:00:00';
            if ($mode == 'end') $val = date('Y-m-d', strtotime($val)).' 23:59:59';
            if ($val == false) throw new Exception('Date value not compatible, please makesure the value is valid date format (Y-m-d) or (Y-m-d H:i)');
        } 

        return $val;
    } catch (Exception $e) {
        throw new Exception(H_throw($e, '[H_getValueForOperator] '));
    }
}

function H_extractKeyRelation ($key) {
    $res = null;
    $ex = explode('|', $key);

    if (
        count($ex) > 1 && count($ex) < 3 && // check validation
        strlen($ex[0]) > 0 && strlen($ex[1]) > 0  // check value
    ) {
        $rel = explode('.', $ex[0]);
        // only accept 2 value, [0]Relation * [1]column name
        if (!empty($rel[0]) && !empty($rel[1])) { 
            $model = $rel[0];
            $col = $rel[1];
 
            $res = [
                'model' => $model,
                'column' => $col,
                'value' => $ex[1],
            ];
        }
    }
    
    return $res;
}

function H_filterTableOperatorDefault ($type = null) {
    $type = $type ? strtolower($type) : $type;
    $res = ['='];
    if ($type == 'string' 
    || $type == 'integer' 
    || $type == 'enum' 
    || $type == 'text'
    || $type == 'json'
    ) $res = ['=', 'like'];
    elseif ($type == 'boolean' || $type == 'unsignedBigInteger') $res = ['='];
    elseif ($type == 'decimal' || $type == 'double') $res = ['=', 'not', 'like', 'gte', 'gt', 'lte', 'lt'];
    elseif ($type == 'int' || $type == 'integer') $res = ['=', 'not', 'like', 'gte', 'gt', 'lte', 'lt'];
    elseif ($type == 'date' || $type == 'datetime') $res = ['=', 'not', 'gted', 'gtd', 'lted', 'ltd', 'is_null', 'is_not_null'];
    elseif ($type == 'all') $res = ['=', 'not', 'gte', 'gt', 'lte', 'lt', 'gted', 'gtd', 'lted', 'ltd', 'start', 'end', 'is_null', 'is_not_null'];
    return $res;
}

function H_getOperatorMode ($key, $indexSelector = 1) {
    $ex = explode(H_operatorIdentifier(), $key);
    $res = isset($ex[$indexSelector]) ? $ex[$indexSelector] : null;
    return $res;
}

function H_extractOperatorAndValue ($operatorAndValueRaw = []) {
    $queries = [];
    foreach ($operatorAndValueRaw as $ov) {
        $value = $ov;

        $operator = H_getOperatorSearch($value, 0);
        $operatorMode = H_getOperatorMode($value, 0);

        $additionalChar = null;
        $dateOprList = ['ltd', 'lted', 'gtd', 'gted'. 'start', 'end'];
        if (in_array($operatorMode, $dateOprList)) $additionalChar = ':'; // handle jam

        $fixValue = H_getValueFromQuery($value, 1, $additionalChar);
        $fixValue = H_getValueForOperator($value, $fixValue, 0);

        if ($operator && empty($fixValue))  {
            $fixValue = !$fixValue || $fixValue === '' ? $value : $fixValue;
        }
        $obj = (Object) [
            "raw" => $ov,
            "operator" => $operator,
            "value" => $fixValue,
        ];
        if ( $ov != '') $queries[] = $obj;
    }
    return $queries;
}

function H_extractValueParams ($params, $mainSeparator = "|", $subSeparator = ":") {
    $fixed_params = [];

    $parent_params = explode($mainSeparator, $params);
    foreach ($parent_params as $parent_param) {
        $child_params = explode($subSeparator, $parent_param);

        if (isset($child_params[0]) && isset($child_params[1])) {
            $key = $child_params[0];
            $value = $child_params[1];

            if (!empty($key) && !empty($value)) {
                if (isset($child_params[2])) {
                    $value .= $subSeparator . $child_params[2];
                }
                if (isset($child_params[3])) {
                    $value .= $subSeparator . $child_params[3];
                }

                $fixed_params[] = (Object) [
                    "key" => $key,
                    "value" => $value
                ];
            }
        }
    }

    return $fixed_params;
}

function H_extractContains ($str) {
    if ($str && $str !== '') {
        $ex = explode(':', $str);
        $columns = explode(',', $ex[0]);
        $value = isset($ex[1]) ? $ex[1] : null;

        return (object) [
            'columns' => $columns,
            'value' => $value,
        ];
    }
    return null;
}

function H_extractInQuery($val) {
    $res = [];
    $valArr = explode(':', $val); // menggunakan delimiter ":"
    foreach ($valArr as $i => $param) { // mulai dari elemen kedua
        $columns = explode('|', $param);
        
        if ($i === 0) { // init columns
            foreach ($columns as $col) {
                if ($col) {
                    $res[] = [
                        'column' => $col,
                        'value' => [],
                    ];
                }
            }
           
        }
        
        if ($i === 1) { // init values
            $values = explode('|', $param);
            foreach ($res as $ic => $col) {
                $value = (isset($values[$ic]) && $values[$ic] !== '') ? $values[$ic] : null;
                $valueArr = H_explode(',', $value);
                if ($value !== '') $res[$ic]['value'] = $valueArr;
            }
 
        }
      
    }
    return H_toArrayObject($res);
}

/* Utils */

function H_explode($separator, $val) { // make clean value explode
    $ex = explode($separator, $val);
   
    $ex = array_filter($ex, function($value) {
        return $value !== '';
    });
    return array_values($ex);
}

function H_getIpClient () {
    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = @$_SERVER['REMOTE_ADDR'];

    if(filter_var($client, FILTER_VALIDATE_IP)) $clientIp = $client;
    elseif(filter_var($forward, FILTER_VALIDATE_IP))  $clientIp = $forward;
    else $clientIp = $remote;

    return $clientIp;
}

function H_sumArrayBy ($raw, $column, $column2 = null) {   
    $raw = H_toArrayObject($raw);
    $res = 0;
    foreach ($raw as $key => $row) {
        if ($column2 == null) $res += $row->$column;
        else  $res += $row->$column +  $row->$column2;

    }
    return $res;
}

function H_makeSerial ($prefix = null, $length = 9, $no = 1) {
    return $prefix.str_pad($no, $length, '0', STR_PAD_LEFT);
}
