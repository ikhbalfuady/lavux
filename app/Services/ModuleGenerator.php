<?php

namespace App\Services;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

/* Interface modules
    ## Legend
    > structure of text

    - {var_name} [{var_type}] : {description}

    # scope strcuture (object)
    - name [string]         : name of scope
    - modules [arrayObject] : modules object

    # modules structure (object)
    - name [string]         : module name, define using PascalCase when the module name more than 2 vocabularies
    - column [arrayObject]  : list columns of module

    # column structure (object)
    - name [string]         : column name of scope
    - type [string]         : type reference of laravel migration type, however we have grouped some custom types
                              which will automate the creation of custom elements of the app
        Avail value :
        - unsignedBigInteger: is default for id, and auto set primary & index
        - string            : same as varchar(255)
        - enum              : enumerable, when using this dont forget to add "enum_list"
        - boolean           : in table DB will be tinyInt
        - double            : for integer & decimal, dont forget add 'length' & if tou want to set specific decimal set 'length2'
        - double            : for integer & decimal, dont forget add 'length' & if tou want to set specific decimal set 'length2'


    - enum_list         : enum list, set value using array value

*/

class ModuleGenerator
{

    /**
     * Generate modules all or by specific scope.
     *
     * @param instance $instCmd
     * @param object $module : object modules
     */
    public static function generate($instCmd, $scope = null) {

        $instCmd->info("-- Initialize Generator");

        $outputDirectory = base_path('generator/output'); // init output folder
        if (!file_exists($outputDirectory)) mkdir($outputDirectory, 0777, true);

        $scopes = ModuleGenerator::scopeList();
        $list = []; // filtering final scopes
        foreach ($scopes as $scp) {
            $scpFix = str_replace('.json', '', $scp);
            $scopeFile = ModuleGenerator::getScope($scpFix);

            // assign scope as defined in command
            if ($scope && $scope == $scpFix) {
                if ($scopeFile->error) $instCmd->error($scopeFile->error);
                else {
                    $list[] = (object) [
                        "name" => $scpFix,
                        "modules" => $scopeFile->data,
                    ];
                }
                break;
            }

            // assign all scope when scope not defined in command
            if (!$scope) {
                if ($scopeFile->error) $instCmd->error($scopeFile->error);
                $list[] = (object) [
                    "name" => $scpFix,
                    "modules" => $scopeFile->data,
                ];
            }
        }

        // generating files
        foreach ($list as $ls) {
            $instCmd->info("Running Generator : {$ls->name}");
            foreach ($ls->modules as $module) {
                // API
                $instCmd->comment("Generate Module : {$module->name} ");
                ModuleGenerator::generateModel($instCmd, $module, $ls->name);
                ModuleGenerator::generateController($instCmd, $module, $ls->name);
                ModuleGenerator::generateRepository($instCmd, $module, $ls->name);
                // Frontend / UI
                ModuleGenerator::generateIndex($instCmd, $module, $ls->name);
                ModuleGenerator::generateForm($instCmd, $module, $ls->name);
                ModuleGenerator::generateDetail($instCmd, $module, $ls->name);
            }

            ModuleGenerator::generateRequirements($instCmd, $ls);
        }

        if (count($list)) {
            $instCmd->info("-- Successfully Generated!");
            $instCmd->comment("Check generated file in : /generator/output");
        } else {
            $instCmd->warn("No modules or scopes defined");
            $instCmd->warn("Make sure & check there are file .json in : /generator/scopes");
            $instCmd->warn("or you can build the scope in Front End by accessing '{your_front_end_url}/generator' ");
        }
    }

    /* API Generator */

    /**
     * Generate Model
     *
     * @param instance $instCmd
     * @param object $module : object modules
     */
    public static function generateModel($instCmd, $module, $scope = null) {
        $suffix = 'Model';
        $script = File::get(base_path("/generator/template/api/$suffix.lvt"));

        // preparation replacement
        $moduleName = $module->name;
        $data = [
            "module" => $moduleName,
            "propertyInfo" => '',
            "table" => strtolower(H_splitUppercaseWithUnderscore($moduleName)),
            "searchRelations" => '',
            "belongsTo" => '',
            "hasMany" => '',
            "morphTo" => '',
            "fillable" => '',
            "casts" => '',
        ];

        // baking
        $last = count($module->column) - 1;
        foreach ($module->column as $index => $col) {
            // make propertyInfo
            if ($col->type == 'morph') {
                $data['propertyInfo'] .= ' * @property string $'.$col->name.'_type ' ."\r\n";
                $data['propertyInfo'] .= ' * @property unsignedBigInteger $'.$col->name.'_id ' ."\r\n";
            } else $data['propertyInfo'] .= ' * @property '.$col->type.' $'.$col->name.' ' ."\r\n";

            // make belongsTo & searchRelations
            if (isset($col->belongsTo)) {
                $bt = $col->belongsTo;
                if (isset($bt->model)) {
                    $btName = $bt->name ?? $bt->model;
                    $fk = $bt->foreign_key ?? $col->name;
                    $fk = ($fk == '_self') ? $col->name : $fk;
                    $fk2 = $bt->owner_key ?? 'id';
                    $fk2 = ($fk2 == '_self') ? 'id' : $fk2;

                    $data['belongsTo'] .= '    public function '.$btName.'() {'."\r\n";
                    $data['belongsTo'] .= '        return $this->belongsTo('.$bt->model.'::class, "'.$fk.'", "'.$fk2.'")->withTrashed();'."\r\n";
                    $data['belongsTo'] .= '    }'."\r\n\r\n";

                    // make searchRelations
                    $data['searchRelations'] .= "            '".$bt->name."' => '".$bt->model."',"."\r\n";
                }

            }

            // make hasMany
            if (isset($col->hasMany)) {
                $hm = $col->hasMany;
                if (isset($hm->model)) {
                    $hmName = (isset($hm->name)) ? $hm->name : $hm->model;
                    $fk = (isset($hm->foreign_key)) ? $hm->foreign_key : $col->name;
                    $fk = ($fk == '_self') ? $col->name : $fk;
                    $fk2 = (isset($hm->local_key)) ? $hm->local_key : 'id';

                    $data['hasMany'] .= '    public function '.$hmName.'() {'."\r\n";
                    $data['hasMany'] .= '        return $this->hasMany('.$hm->model.'::class, "'.$fk2.'", "'.$fk.'");'."\r\n";
                    $data['hasMany'] .= '    }'."\r\n\r\n";
                }
            }

            // make morpTo
            if ($col->type == 'morph') {
                $morpName = ModuleGenerator::columnToMorph($col->name);
                $data['morphTo'] .= '    public function '.$morpName.'(): MorphTo {'."\r\n";
                $data['morphTo'] .= '        return $this->morphTo();'."\r\n";
                $data['morphTo'] .= '    }'."\r\n\r\n";
            }

            $type = ModuleGenerator::getCastType($col->type);
            $coma = ModuleGenerator::makeComma($index, $last);

            // make fillable
            if($col->name !== 'id') {
                if ($col->type == 'morph') {
                    $data['casts'] .= '"'.$col->name.'_type"=> "string",'."\r\n";
                    $data['casts'] .= '        "'.$col->name.'_id"=> "integer"'.$coma;
                    $data['fillable'] .= '"'.$col->name.'_type",'."\r\n";
                    $data['fillable'] .= '        "'.$col->name.'_id"'.$coma ;
                } else {
                    $data['casts'] .= '"'.$col->name.'"=> "'.$type.'"'.$coma;
                    $data['fillable'] .= '"'.$col->name.'"'.$coma ;
                }
            }

        }

        // building
        foreach ($data as $key => $value) {
            $script = str_replace(ModuleGenerator::selector($key), $value, $script);
        }

        // executing
        $script = str_replace('"',"'", $script); // make consistence single quote
        $scope = $scope ? "$scope/" : '';
        $generate = ModuleGenerator::makeFile("$moduleName.php", base_path("generator/output/{$scope}{$suffix}/"), $script);
        if ($generate->result) $instCmd->line("API::$suffix .......... [Generated]");
        else $instCmd->error("API::$suffix .......... [Failed]");

        ModuleGenerator::generateModelH($instCmd, $module, $scope);
        ModuleGenerator::generateMigration($instCmd, $module, $scope);

    }

    /**
     * Generate Model_H, has included in : generateModel
     *
     * @param instance $instCmd
     * @param object $module : object modules
     */
    public static function generateModelH($instCmd, $module, $scope = null) {
        $suffix = 'Model_H';
        $script = File::get(base_path("/generator/template/api/$suffix.lvt"));

        // preparation replacement
        $moduleName = $module->name;
        $data = [
            "module" => $moduleName,
            "enumList" => '',
            "filterList" => '',
        ];

        // baking
        $last = count($module->column) - 1;
        foreach ($module->column as $index => $col) {

            // make Enum
            $enumName = H_makeEnumName($col->name);
            if ($col->type == 'enum')  {
                $enums = isset($col->enum_list) ? json_encode($col->enum_list) : '[]';
                $enums = str_replace('"',"'", $enums);
                $data['enumList'] .= '    public function Enum'.$enumName.'() {'."\r\n";
                $data['enumList'] .= '        return '.$enums.';'."\r\n";
                $data['enumList'] .= '    }'."\r\n";
            }

            // make FilterList
            $multiple = ($col->type == 'json') ? 'true' : 'false';

            $_sources = (isset($col->belongsTo) && isset($col->belongsTo->model)) ? $col->belongsTo->model : null ;
            $sources = $_sources ? "'".strtolower(H_splitUppercaseWithStrip($_sources))."'" : 'null' ;
            $key_value = $_sources ? "'id'" : "null";
            $options = ($col->type == 'enum') ? '$this->Enum'.$enumName.'()' : '[]';
            $type = 'input';
            if ($sources != 'null') $type = 'select';
            if ($options != '[]') $type = 'select';

            // $finalFormat
            if ($options != '[]') $options = "'options'   => $options,";
            else $options = null;

            if ($sources != 'null') $sources = "'sources'   => $sources,";
            else $sources = null;

            if ($key_value != 'null') {
                $kv = $key_value;
                $key_value = "'key_value' => $kv,";
                $key_label = "'key_label' => $kv,";
            }
            else {
                $key_value = null;
                $key_label = null;
            }

            if ($multiple != 'false') $multiple = "'multiple'  => true,";
            else $multiple = null;

            $assigment = '';
            if ($options) $assigment .= "\r\n"."                ".$options;
            if ($sources) $assigment .= "\r\n"."                ".$sources;
            if ($key_value) $assigment .= "\r\n"."                ".$key_value;
            if ($key_label) $assigment .= "\r\n"."                ".$key_label;
            if ($multiple) $assigment .= "\r\n"."                ".$multiple;

            $filter = "
            [
                'id'        => '{$col->name}',
                'type'      => '$type',
                'operator'  => H_getOperatorSearchList(H_filterTableOperatorDefault('$col->type')),$assigment
            ],";

            if ($col->type == 'morph') {
            $data['filterList'] .= "
            [
                'id'        => '{$col->name}_type',
                'operator'  => H_getOperatorSearchList(['=']),
            ],";
            $data['filterList'] .= "
            [
                'id'        => '{$col->name}_id',
                'operator'  => H_getOperatorSearchList(['=']),
            ],";
            } else {
                $data['filterList'] .= $filter;
            }

        }

        // building
        foreach ($data as $key => $value) {
            $script = str_replace(ModuleGenerator::selector($key), $value, $script);
        }

        // executing

        $scope = $scope ? "$scope/" : '';
        $generate = ModuleGenerator::makeFile("{$moduleName}_H.php", base_path("generator/output/{$scope}Model/"), $script);
        if ($generate->result) $instCmd->line("API::$suffix .......... [Generated]");
        else $instCmd->error("API::$suffix .......... [Failed]");

    }

    /**
     * Generate Model_H, has included in : generateModel
     *
     * @param instance $instCmd
     * @param object $module : object modules
     */
    public static function generateMigration($instCmd, $module, $scope = null) {
        $suffix = 'Migration';
        $script = File::get(base_path("/generator/template/api/$suffix.lvt"));

        // preparation replacement
        $table = strtolower(H_splitUppercaseWithUnderscore($module->name));
        $data = [
            "table" => $table,
            "insert" => '',
            "fulltext" => '',
            "delete" => '',
        ];

        // baking
        $fulltext = [];
        $last = count($module->column) - 1;
        foreach ($module->column as $index => $col) {

            $type = ModuleGenerator::getCastType($col->type);
            $attributes = '';
            if (isset($col->attributes)) {
                foreach ($col->attributes as $value) { $attributes .= '->'.$value.'()'; }
            }

            $spec = '';
            if($col->type == 'double') {
                if (isset($col->length)) $spec .= ",{$col->length}";
                if (isset($col->length2)) $spec .= ",{$col->length2}";
            }

            if ($col->type == 'enum') {
                $enums = isset($col->enum_list) ? json_encode($col->enum_list) : '[]';
                $enums = str_replace('"',"'", $enums);
                $spec .= ', '.$enums;
            }

            $type = "{$col->type}('{$col->name}'$spec)";

            $default = '';
            if (property_exists($col, 'default')) {
                $colDefault = $col->default;
                $types = ['double', 'integer', 'boolean'];
                if (in_array(strtolower($col->type), $types)) $colDefault = $colDefault ? $colDefault : 0;
                if ($colDefault !== NULL) $default = '->default('.ModuleGenerator::getValueOnString($colDefault).')';
            }

            if ($col->type == 'morph') {
                $data['insert'] .= "            \$table->nullableMorphs('{$col->name}');\r\n";
                $data['delete'] .= "            \$table->dropMorphs('{$col->name}');\r\n";
            } else {
                $data['insert'] .= "            if (!Schema::hasColumn(\$tableName, '{$col->name}')) \$table->{$type}{$attributes}{$default};\r\n";
                $data['delete'] .= "            if (!Schema::hasColumn(\$tableName, '{$col->name}')) \$table->dropColumn('{$col->name}');\r\n";
            }

            if (isset($col->attributes) && in_array('fulltext', $col->attributes)) {
               $fulltext[] = $col->name;
            }

        }

        $fulltextScript = '';
        if (count($fulltext)) {
            $fulltextScript = '
            DB::statement("ALTER TABLE
                '.$table.' ADD FULLTEXT
                '.$table.'_fulltext('.implode(',', $fulltext).')
            "); ';
            $data['fulltext'] .= "            {$fulltextScript}\r\n";
        }


        // building
        foreach ($data as $key => $value) {
            $script = str_replace(ModuleGenerator::selector($key), $value, $script);
        }

        // executing
        $scope = $scope ? "$scope/" : '';
        $generate = ModuleGenerator::makeFile("2022_08_14_112406_{$table}_init.php", base_path("generator/output/{$scope}{$suffix}/"), $script);
        if ($generate->result) $instCmd->line("API::$suffix .......... [Generated]");
        else $instCmd->error("API::$suffix .......... [Failed]");

    }

    /**
     * Generate Controller
     *
     * @param instance $instCmd
     * @param object $module : object modules
     */
    public static function generateController($instCmd, $module, $scope = null) {
        $suffix = 'Controller';
        $script = File::get(base_path("/generator/template/api/$suffix.lvt"));

        // preparation replacement
        $moduleName = $module->name;
        $data = [
            "module" => $moduleName,
            "relationSingle" => "",
            "relationCollection" => "",
            "validationField" => "",
            "validationMessage" => "",
        ];

        // baking
        foreach ($module->column as $index => $col) {

            // make "with" relation
            $relation = '';
            if (isset($col->belongsTo)) {
                $relation .= '            ' . "'".$col->belongsTo->name."', //belongsTo" . "\r\n";
            }
            if (isset($col->hasMany)) {
                $relation .= '            ' . "'".$col->hasMany->name."', //belongsTo" . "\r\n";
            }
            if ($col->type == 'morph') {
                $morphName = ModuleGenerator::columnToMorph($col->name);
                $relation .= '            ' . "'".$morphName."', //morphTo" . "\r\n";
            }

            $data['relationSingle'] .= $relation;
            $data['relationCollection'] .= $relation;

            // make validation
            $required = true;
            if (isset($col->attributes)) {
                foreach ($col->attributes as $value) {
                    if ($value == 'nullable') {
                        $required = false;
                        break;
                    }
                }
            }

            if (isset($col->default)) {
                $required = false;
            }

            if ($required && $col->name != 'id') {
                $data['validationField'] .= '                   "'.$col->name.'" => "required",' ."\r\n";
                $data['validationMessage'] .= '                   "'.$col->name.'.required" => "'.$col->name.' is required", ' ."\r\n";
            }
        }

        // building
        foreach ($data as $key => $value) {
            $script = str_replace(ModuleGenerator::selector($key), $value, $script);
        }

        // executing
        $script = str_replace('"',"'", $script); // make consistence single quote
        $scope = $scope ? "$scope/" : '';
        $generate = ModuleGenerator::makeFile($moduleName.$suffix.".php", base_path("generator/output/{$scope}{$suffix}/"), $script);
        if ($generate->result) $instCmd->line("API::$suffix .......... [Generated]");
        else $instCmd->error("API::$suffix .......... [Failed]");

    }

    /**
     * Generate Repository
     *
     * @param instance $instCmd
     * @param object $module : object modules
     */
    public static function generateRepository($instCmd, $module, $scope = null) {
        $suffix = 'Repository';
        $script = File::get(base_path("/generator/template/api/$suffix.lvt"));

        // preparation replacement
        $moduleName = $module->name;
        $data = [
            "module" => $moduleName,
            "fieldSet" => '',
        ];

        // baking
        foreach ($module->column as $index => $col) {

            // make fieldset
            $setter = '$request["'.$col->name.'"];';
            if (isset($col->attributes)){
                foreach ($col->attributes as $value) {
                    if ($value == 'nullable') $setter = 'H_handleRequest($request, "'.$col->name.'");';
                    break;
                }
            }
            if($col->name != 'id') {
                $default = '';
                if (isset($col->default)) $default = ', '.ModuleGenerator::getValueOnString($col->default);
                $getter = 'H_hasProps($payload, "'.$col->name.'"'.$default.');';

                if ($col->type == 'morph') {
                    $data['fieldSet'] .= '            $this->setter($data, $payload, "'.$col->name.'_type");'."\r\n";
                    $data['fieldSet'] .= '            $this->setter($data, $payload, "'.$col->name.'_id");'."\r\n";

                } else $data['fieldSet'] .= '            $data->'.$col->name.' = '.$getter.' ' ."\r\n";
            }

        }

        // building
        foreach ($data as $key => $value) {
            $script = str_replace(ModuleGenerator::selector($key), $value, $script);
        }

        // executing
        $script = str_replace('"',"'", $script); // make consistence single quote
        $scope = $scope ? "$scope/" : '';
        $generate = ModuleGenerator::makeFile($moduleName.$suffix.".php", base_path("generator/output/{$scope}{$suffix}/"), $script);
        if ($generate->result) $instCmd->line("API::$suffix .......... [Generated]");
        else $instCmd->error("API::$suffix .......... [Failed]");

    }

    /**
     * Generate Repository
     *
     * @param instance $instCmd
     * @param object $scopeObj : object of $ls in final list scope
     */
    public static function generateRequirements($instCmd, $scopeObj) {
        $script = File::get(base_path("/generator/template/requirement.lvt"));

        // preparation replacement
        $data = [
            "modules" => '',
            "routeUi" => '',
        ];

        // baking
        foreach ($scopeObj->modules as $index => $module) {

            $routeUi = strtolower(H_splitUppercaseWithStrip($module->name));
            // make list modules
            $data['modules'] .= "   '{$module->name}',\r\n";
            $data['routeUi'] .= "   '{$routeUi}',\r\n";


        }

        // building
        foreach ($data as $key => $value) {
            $script = str_replace(ModuleGenerator::selector($key), $value, $script);
        }

        // executing
        $script = str_replace('"',"'", $script); // make consistence single quote
        $scope = $scopeObj ? "{$scopeObj->name}/" : '';
        $generate = ModuleGenerator::makeFile("{$scopeObj->name}_requirements.md", base_path("generator/output/$scope"), $script);
        if ($generate->result) $instCmd->line("{$scopeObj->name} Requirements .......... [Generated]");
        else $instCmd->error("{$scopeObj->name} Requirements .......... [Failed]");

    }

    /* UI Generator */

    /**
     * Generate Index
     *
     * @param instance $instCmd
     * @param object $module : object modules
     */
    public static function generateIndex($instCmd, $module, $scope = null) {
        $suffix = 'Index';
        $script = File::get(base_path("/generator/template/ui/$suffix.lvt"));

        // preparation replacement
        $slug = strtolower(H_splitUppercaseWithStrip($module->name));
        $fname = strtolower($suffix);

        // executing
        $scope = $scope ? "$scope/" : '';
        $generate = ModuleGenerator::makeFile($fname.".vue", base_path("generator/output/{$scope}UI/$slug/"), $script);
        if ($generate->result) $instCmd->line("UI::$suffix .......... [Generated]");
        else $instCmd->error("UI::$suffix .......... [Failed]");

    }

    /**
     * Generate Index
     *
     * @param instance $instCmd
     * @param object $module : object modules
     */
    public static function generateForm($instCmd, $module, $scope = null) {
        $suffix = 'Form';
        $script = File::get(base_path("/generator/template/ui/$suffix.lvt"));

        // preparation replacement
        $slug = strtolower(H_splitUppercaseWithStrip($module->name));
        $fname = strtolower($suffix);
        $data = [
            "fieldSet" => '',
            "selectSources" => '',
        ];

        // baking
        foreach ($module->column as $index => $col) {
            $label = H_columnToLabel($col->name);

            $url = '';
            if (isset($col->belongsTo) && isset($col->belongsTo->model)) {
                $url =  strtolower(H_splitUppercaseWithStrip($col->belongsTo->model));
            }

            $required = '';
            $validationType = 'required';
            if ($col->type == 'integer' || $col->type == 'double' ) $validationType = 'min-numb-1';
            $defaultValidation = "\$Handler.rules(dataModel.{$col->name}, '$validationType')";
            $defaultValidation = ':rules="'.$defaultValidation.'" ';

            $decimalPlace = '';
            if (isset($col->length2)) $decimalPlace = ':decimal="'.$col->length2.'"';

            if (isset($col->attributes) && !in_array('nullable', $col->attributes)) $required = $defaultValidation;

            $string = '      <lv-input col="4" label="'.$label.'" v-model="dataModel.'.$col->name.'" '.$required.'/>';
            $integer = '      <lv-input mode="number" col="4" label="'.$label.'" v-model="dataModel.'.$col->name.'" '.$required.'/>';
            $decimal = '      <lv-input mode="currency" '.$decimalPlace.' col="4" label="'.$label.'" v-model="dataModel.'.$col->name.'" '.$required.'/>';
            $date = '      <lv-input mode="date" col="4" label="'.$label.'" v-model="dataModel.'.$col->name.'" '.$required.'/>';
            $datetime = '      <lv-input mode="datetime" col="4" label="'.$label.'" v-model="dataModel.'.$col->name.'" '.$required.'/>';
            $selectApi = '      <lv-select col="4" label="'.$label.'" v-model="dataModel.'.$col->name.'" url="'.$url.'" searchable '.$required.'/>';
            $select = '      <lv-select col="4" label="'.$label.'" v-model="dataModel.'.$col->name.'" :options="select.'.$col->name.'" '.$required.'/>';
            $textarea = '      <lv-textarea col="12" label="'.$label.'" v-model="dataModel.'.$col->name.'" '.$required.'/>';
            $toggle = '      <lv-toggle col="4" label="'.$label.'" v-model="dataModel.'.$col->name.'" />';

            if ($col->type == 'string') $data['fieldSet'] .= "\n".$string;
            if ($col->type == 'integer') $data['fieldSet'] .= "\n".$integer;
            if ($col->type == 'double') $data['fieldSet'] .= "\n".$decimal;
            if ($col->type == 'date') $data['fieldSet'] .= "\n".$date;
            if ($col->type == 'datetime' || $col->type == 'dateTime') $data['fieldSet'] .= "\n".$datetime;
            if ($col->type == 'unsignedBigInteger') $data['fieldSet'] .= "\n".$selectApi;
            if ($col->type == 'text' || $col->type == 'longtext') $data['fieldSet'] .= "\n".$textarea;
            if ($col->type == 'json') $data['fieldSet'] .= "\n".$textarea;
            if ($col->type == 'tinyInteger' || $col->type == 'boolean') $data['fieldSet'] .= "\n".$toggle;
            if ($col->type == 'enum') {
                $data['fieldSet'] .= "\n".$select;

                $enumList = isset($col->enum_list) ? json_encode($col->enum_list) : '[]';
                $enumList = str_replace('"',"'", $enumList);
                $data['selectSources'] .= "      ".$col->name.": Handler.toObjectSelect($enumList),"."\n";
            }
            if ($col->type == 'morph') {
                $data['fieldSet'] .= "\n".'      <lv-input col="4" label="'.$label.' type" v-model="dataModel.'.$col->name.'_type" />';
                $data['fieldSet'] .= "\n".'      <lv-input col="4" label="'.$label.' id" v-model="dataModel.'.$col->name.'_id" />';
            }

        }

        // building
        foreach ($data as $key => $value) {
            $script = str_replace(ModuleGenerator::selector($key), $value, $script);
        }

        // executing
        $scope = $scope ? "$scope/" : '';
        $generate = ModuleGenerator::makeFile($fname.".vue", base_path("generator/output/{$scope}UI/$slug/"), $script);
        if ($generate->result) $instCmd->line("UI::$suffix .......... [Generated]");
        else $instCmd->error("UI::$suffix .......... [Failed]");

    }

    /**
     * Generate Index
     *
     * @param instance $instCmd
     * @param object $module : object modules
     */
    public static function generateDetail($instCmd, $module, $scope = null) {
        $suffix = 'Detail';
        $script = File::get(base_path("/generator/template/ui/$suffix.lvt"));

        // preparation replacement
        $slug = strtolower(H_splitUppercaseWithStrip($module->name));
        $fname = strtolower($suffix);

        // executing
        $scope = $scope ? "$scope/" : '';
        $generate = ModuleGenerator::makeFile($fname.".vue", base_path("generator/output/{$scope}UI/$slug/"), $script);
        if ($generate->result) $instCmd->line("UI::$suffix .......... [Generated]");
        else $instCmd->error("UI::$suffix .......... [Failed]");

        ModuleGenerator::generateMeta($instCmd, $module, $scope);
    }

    /**
     * Generate Meta , included in generateDetail
     *
     * @param instance $instCmd
     * @param object $module : object modules
     */
    public static function generateMeta($instCmd, $module, $scope = null) {
        $suffix = 'Meta';
        $script = File::get(base_path("/generator/template/ui/$suffix.lvt"));

        // preparation replacement
        $moduleName = $module->name;
        $slug = strtolower(H_splitUppercaseWithStrip($module->name));
        $fname = strtolower($suffix);
        $data = [
            "module" => H_splitUppercaseWithSpace($moduleName),
            "slug" => $slug,
            "keyLabel" => 'id',
            "searchBy" => '',
            "withRelation" => '',
            "model" => '',
            "columns" => '',
        ];

        // baking
        foreach ($module->column as $index => $col) {
            $label = H_columnToLabel($col->name);

            $defaultValue = 'null';
            if ($col->type == 'integer' || $col->type == 'double') $defaultValue = '0';
            if ($col->type == 'tinyInteger' || $col->type == 'boolean') $defaultValue = 'false';
            if ($col->type == 'date' || $col->type == 'dateTime' || $col->type == 'datetime') $defaultValue = 'Handler.today()';
            if (isset($col->default)) $defaultValue = ModuleGenerator::getValueOnString($col->default);

            $data['searchBy'] = ModuleGenerator::makeSearchByUi($module->column);
            $data['withRelation'] = ModuleGenerator::makeWithRelationUi($module->column);

            if ($col->type == 'morph') {
                $data['model'] .= "    ".$col->name."_type: null,"."\r\n";
                $data['model'] .= "    ".$col->name."_id: null,"."\r\n";
            }
            else $data['model'] .= "    ".$col->name.": $defaultValue,"."\r\n";

            $search = $col->name;
            if (isset($col->belongsTo) && isset($col->belongsTo->name)) {
                $fk = 'id';
                if (isset($col->belongsTo->foreign2)) $fk = $col->belongsTo->foreign2;
                $search = "{$col->belongsTo->name}.$fk";
            }
            if ($col->type != 'bigIncrements') $data['columns'] .= "    { name: '".$col->name."', label: '".$label."', field: '".$col->name."', search: '".$search."', align: 'left', sort: 'asc' },\r\n";
        }

        // building
        foreach ($data as $key => $value) {
            $script = str_replace(ModuleGenerator::selector($key), $value, $script);
        }

        // executing
        $scope = $scope ? "$scope/" : '';
        $generate = ModuleGenerator::makeFile($fname.".js", base_path("generator/output/{$scope}UI/$slug/"), $script);
        if ($generate->result) $instCmd->line("UI::$suffix .......... [Generated]");
        else $instCmd->error("UI::$suffix .......... [Failed]");

    }


    // Utils

    static function columnToMorph ($columnName) {
        return Str::of($columnName)->studly();
    }

    static function makeWithRelationUi ($columns) {
        $relatedColumns = array_filter($columns, function($column) {
            return isset($column->belongsTo) && isset($column->belongsTo->name);
        });

        $relatedNames = array_map(function($column) {
            return "'" . $column->belongsTo->name . "'";
        }, $relatedColumns);

        return implode(', ', $relatedNames);
    }

    static function makeSearchByUi($columns) {
        $count = count($columns);
        $result = [];
        for ($i = 1; $i < $count; $i++) {
            $name = $columns[$i]->name;
            if (strpos($name, '_id') !== false) {
                continue; // skip jika kolom berisi '_id'
            }
            if ($count == 2) {
                $result[] = $name;
                break;
            } else if ($i < 3) {
                $result[] = $name;
            }
        }
        return "'" . implode("','", $result) . "'";
    }

    static function validateColumnUi ($name) {
        $res = false;
        if ( $name == 'created_by') {}
        elseif ( $name == 'updated_by') {}
        elseif ( $name == 'deleted_by') {}
        elseif ( $name == 'id') {}
        elseif ( $name == 'created_ip') {}
        elseif ( $name == 'updated_ip') {}
        elseif ( $name == 'deleted_ip') {}
        elseif ( $name == 'created_at') {}
        elseif ( $name == 'updated_at') {}
        elseif ( $name == 'deleted_at') {}
        else $res = true;
        return $res;
    }

    static function getValueOnString ($val) {

        if ($val === false) $val = "false";
        elseif ($val === true) $val = "true";
        elseif (is_string($val)) $val = "'$val'";
        elseif (is_array($val)) $val = "".json_encode($val)."";
        elseif (is_object($val)) $val = "null";
        return $val;
    }

    static function makeComma ($index, $last) {
        $res = ',
        ';
        if($index == $last) $res = '';
        return $res;
    }

    static function getCastType ($type) {
        $res = 'string';
        if ($type == 'text') $res = 'string';
        if ($type == 'unsignedBigInteger') $res = 'integer';
        if ($type == 'tinyInteger') $res = 'boolean';
        if ($type == 'boolean') $res = 'boolean';
        if ($type == 'enum') $res = 'string';
        if ($type == 'date') $res = 'date';
        if ($type == 'datetime') $res = 'datetime';
        if ($type == 'double') $res = 'real';
        if ($type == 'decimal') $res = 'real';
        if ($type == 'integer') $res = 'integer';
        if ($type == 'json') $res = 'array';
        return $res;
    }

    static function getTag($position = 'start') {
        return $position === 'start' ? '<<' : '>>';
    }

    static function selector($key) {
        return ModuleGenerator::getTag('start') . $key . ModuleGenerator::getTag('end');
    }

    public static function scopeList ($readable = false) {
        $files = scandir(base_path('/generator/scopes/'));
        $res = [];
        foreach ($files as $file) {
            if ($file == '.') {}
            elseif ($file == '..') {}
            else {
                if ($readable) {
                    $res[] = (object) [
                        "name" => str_replace('.json', '', $file),
                        "file_name" => $file,
                    ];
                }
                else $res[] = $file;
            }
        }
        return $res;
    }

    public static function getScope ($name) : object {
        $dir = base_path('generator/scopes/');
        $file = $name.'.json';
        $filePath = $dir . $file;

        if (!file_exists($filePath)) {
            $msg = "Scope file $name not found!";
            return (object) [
                "error" => $msg,
                "data" => null,
            ];
        } else {
            return (object) [
                "error" => null,
                "data" => json_decode(File::get($filePath)),
            ];
        }
    }

    public static function makeFile ($name, $outputDir, $data, $instCmd = null) : object {

        if (!file_exists($outputDir)) mkdir($outputDir, 0777, true); // generate folder module
        $create = fopen($outputDir.$name, "w");

        if (!$create) {
            $msg = "Unable write file in : $outputDir.$name";
            if ($instCmd) $instCmd->error($msg);
            return (object) [ "result" => false, "message" => $msg];
        }

        fwrite($create, $data);
        fclose($create);
        $msg = "Success write file in : $outputDir.$name";
        if ($instCmd) $instCmd->error($msg);
        return (object) [ "result" => true, "message" => $msg];
    }

    public static function getScopeListDetail () {
        $scopes = ModuleGenerator::scopeList();
        $list = [];
        foreach ($scopes as $scp) {
            $scope = str_replace('.json', '', $scp);
            $scopeFile = ModuleGenerator::getScope($scope);

            if ($scopeFile->error) {
                $list[] = (object) [
                    "id" => $scope,
                    "name" => $scope,
                    "error" => $scopeFile->error,
                    "modules" => [],
                ];
            } else {
                $list[] = (object) [
                    "id" => $scope,
                    "name" => $scope,
                    "error" => null,
                    "modules" => $scopeFile->data ?? [],
                ];
            }

        }

        return $list;
    }

    public static function storeScope ($name, $data) {
        $data = json_encode($data);
        return ModuleGenerator::makeFile("$name.json", base_path("generator/scopes/"), $data);
    }

    public static function deleteScope ($name) {
        $fullPath = base_path("generator/scopes/$name.json");
        try {
            if (file_exists($fullPath)) {
                unlink($fullPath);
                return (object) [
                    'result' => true,
                    'message' => "Scope \"$name\" has been deleted."
                ];
            } else {
                return (object) [
                    'result' => false,
                    'message' => "Scope \"$name\" not found!"
                ];
            }
        } catch (\Exception $e) {
            return (object) [
                'result' => false,
                'message' => $e->getMessage()
            ];
        }
    }

}
