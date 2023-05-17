<?php
namespace App\Services;
use Exception;
use App\Models\Metas;

class ApplicationSettings
{

    protected $slugNameSetting = 'app-settings';
    protected $configurationList = [
        [ // default value, will overide by : config('lv_settings', [])
            "name" => "company",
            "comment" => "Configuration default of your company information",
            "list" => [ 
                "name" => "PT Solusi Pembantu Usaha",
                "tag_line" => "Component library for Enterprise system",
                "phone" => "012 345 6789",
                "web" => "http://lavux.sopeus.com",
                "email" => "lavux@sopeus.com",
                "logo" => null, // url logo
            ],
        ],
    ];

    public function __construct(
        //
    ){
        $this->configurationList = config('lv_settings', []);
    }

    public function getSlug () {
        return $this->slugNameSetting;
    }

   // databases
    public function store ($type, $module, $config, $value) {
        try {

        
            $input = [
                "type" => $type,
                "slug" => $this->slugNameSetting,
                "name" => $config,
                "value" => $value,
                "remarks" => $module,
                'created_by' => 1,
                'created_at' => H_today(),
                'created_ip' => H_getIpClient(),
            ];

            return Metas::updateOrCreate(
                [  // where nya
                    'slug' => $this->slugNameSetting,
                    'name' => $config,
                    'remarks' => $module,
                ],
                $input // input data nya
            );
        } catch (Exception $e){
            throw new Exception(H_throw($e, '[ApplicationSettings::store] '));
        }
    }

    public function load ($module = null) {
        try {
            $data = Metas::whereSlug($this->slugNameSetting);
            if ($module) $data = $data->whereRemarks($module);
            $data = $data->get();
            return $data;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '[ApplicationSettings::load] '));
        }
    }

    public function get ($module, $configuration, $format = false) {
        try {
            $data = Metas::whereSlug($this->slugNameSetting);
            $data = $data->whereRemarks($module);
            $data = $data->whereName($configuration);
            $data = $data->first();

            if ($format) $data = $this->formater($data);
            return $data;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '[ApplicationSettings::get] '));
        }
    }

    // utilities

    public function getValue ($settings, $name, $remarks) { // $settings : must be formated to 'collection', wrapping that with collect($settings)
        try {
            $res = [
                "has" => false,
                "value" => null,
            ];

            $load = $settings->where('name', $name)->where('remarks', $remarks)->first();
            if ($load) $res = $this->formater($load);
            return (object) $res;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '[ApplicationSettings::getValue] '));
        }
    }

    public function formater ($config) {
        try {
            $res = [
                "has" => false,
                "value" => null,
            ];
            if ($config) {
                $type = $config->type;
                // formating value
                $value = $config->value;
                if ($type == 'array') $value = json_decode($value);
                if ($type == 'integer') $value = (int) $value;
                if ($type == 'double') $value = (float) $value;
                if ($type == 'boolean') {
                    if ($value == 'false') $value = false;
                    else if ($value == 'true') $value = true;
                    else $value = (boolean) $value;
                }
                $res = [
                    "has" => true,
                    "value" => $value,
                    "raw_value" => $config->value,
                    "type" => $type,
                    "id" => $config->id,
                ];
            }

            return (object) $res;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '[ApplicationSettings::formater] '));
        }
    }

    public function formatDefault ($value) {
        try {
            $type = getType($value);
            $res = [
                "has" => false,
                "value" => $value,
                "raw_value" => $value,
                "type" => $type == 'NULL' ? 'string' : $type,
                "id" => null,
            ];

            return (object) $res;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '[ApplicationSettings::formatDefault] '));
        }
    }

    // default
    public function init () {
        try {
            $store = [];
            foreach ($this->configurationList as $k => $set) {
                foreach ($set['list'] as $config => $item) {
                    $value = $item;
                    $type = getType($item);
                    $typeDate = H_isDate($item);
                    if ($type == 'object') $type = 'array';
                    if ($typeDate) $type = 'date';
                    // formating array
                    if ($type == 'array') $value = json_encode($value);
                    $store[] = $this->store($type, $set['name'], $config, $value);
                }
            }

            return $store;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '[ApplicationSettings::init] '));
        }
    }

    public function list ($module = null) {
        try {
            $db_settings = collect($this->load($module));

            $settings = $this->configurationList;
            if ($module) {
                $settings = [];
                $check = H_findArrayByKey($this->configurationList, 'name', $module);
                if ($check) $settings[] = H_findArrayByKey($this->configurationList, 'name', $module);
            }
            foreach ($settings as $k => $set) {
                foreach ($set['list'] as $name => $value) {
                    $fromDb = $this->getValue($db_settings, $name, $set['name']);
                    $value = $fromDb->has ? $fromDb->value : $value;
                    
                    // handle not init
                    if (!$fromDb->has) {
                        $fromDb = $this->formatDefault($value);
                    }
                    $res = [
                        "value" => $value,
                        "raw" => $fromDb,
                    ];
                    $settings[$k]['list'][$name] = $res;
                    // dd($fromDb);
                }
            }

            if ($module && count($settings)) $settings = $settings[0];

            return $settings;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '[ApplicationSettings::list] '));
        }
    }

    public function update ($id, $value, $module = null, $config = null) {
        try {

            if ($id) {
                $check = Metas::selectRaw('id')->whereId($id)->first();
                if (!$check) throw new Exception("Data with id: $id not found");
                $data = Metas::whereId($id)->update(["value" => $value]);
            } else {
                if (!$module) throw new Exception("Configuration setting not yet initialize, 'module' must be provide to payload");
                if (!$config) throw new Exception("Configuration setting not yet initialize, 'config' must be provide to payload");
                $data = $this->store(getType($value), $module, $config, $value);
            }

            
            return $data;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '[ApplicationSettings::update] '));
        }
    }

    public function getConfig ($module, $configuration = null, $valueOnly = false) {
        try {

            $res = null;
            if ($configuration) {
                $checkModule = H_findArrayByKey($this->configurationList, 'name', $module);
                if (!$checkModule) throw new Exception("Modul Setting for '$module' not defined");
                
                $checkConfig = $checkModule['list'];
                if (isset($checkConfig[$configuration])) {
                    $res = $this->get($module, $configuration, true);
                    $defaultValue = $checkConfig[$configuration];

                    if (!$res->has) $res = $this->formatDefault($defaultValue);

                    if ($valueOnly) $res = $res ? $res->value : $res;
                    return $res;
                } else throw new Exception("Configuration '$configuration' on module '$module' not defined");
            } else $res = $this->list($module);

            return $res;
        } catch (Exception $e){
            throw new Exception(H_throw($e, '[ApplicationSettings::getConfig] '));
        }
    }

}
