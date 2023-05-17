<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DeployUiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lavux-deploy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploy frontend file to web, makesure you has run "quasar build" inside folder /ui .';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $base = base_path('/ui/dist/spa');
        $publicPath = base_path('/public');

        $appSpecs = [
            '/assets',
            '/css',
            '/fonts',
            '/icons',
            '/img',
            '/js',
            '/app.html'
        ];

        if (count($appSpecs)) $this->info("Prepare deleting old folder & file app");
        foreach ($appSpecs as $item) {
            $path = $publicPath . $item;
            if (File::exists($path)) {
                if (File::isDirectory($path)) {
                    File::deleteDirectory($path);
                    $this->comment("Folder '$path' has been deleted");
                } else {
                    File::delete($path);
                    $this->comment("File '$path' has been deleted");
                }
            }
        }

        if (!empty($base) && !empty($publicPath)) {
            if (File::isDirectory($base)) {
                File::copyDirectory($base, $publicPath);
                $this->comment("Preparing copy 'ui/dist/spa' to 'public' ");

                $indexFile = $publicPath . '/index.html';
                $appFile = $publicPath . '/app.html';

                if (File::exists($indexFile)) {
                    File::move($indexFile, $appFile);
                    $this->comment("Renaming 'index.html' to 'app.html'");
                } else {
                    $this->warn("'$indexFile' does not exist");
                }

                // genereate route
                $this->comment("preparing generate route for UI");
                $this->generateUiRoute();


                $this->comment("...........");
                $this->info("Deployment successfully, you can access the app right now!");
            } else {
                $this->error("'$base' is not a directory");
            }
        } else {
            $this->error("Please provide both base and target directory paths");
        }

    }

    function generateUiRoute()
    {
        $uiRoutePath = base_path('routes/ui_route.json');
        if (file_exists($uiRoutePath)) {
            $phpFilePath = base_path('routes/ui_route.php');
            $create = fopen($phpFilePath, "w");

            if (!$create) {
                $msg = "Faile make Route UI > Unable write file in : $phpFilePath";
                $this->error($msg);
            }

            $routes = json_decode(File::get($uiRoutePath));
        
            $phpArrayString = '<?php' . PHP_EOL . PHP_EOL;
            $phpArrayString .= "/*". PHP_EOL;
            $phpArrayString .= "|--------------------------------------------------------------------------". PHP_EOL;
            $phpArrayString .= "| Frontend Route". PHP_EOL;
            $phpArrayString .= "|--------------------------------------------------------------------------". PHP_EOL;
            $phpArrayString .= "|". PHP_EOL;
            $phpArrayString .= "|Don't change this file!". PHP_EOL;
            $phpArrayString .= "|if you want to update this file, make sure you following the rules". PHP_EOL;
            $phpArrayString .= "|Generate the ui route in frontend by accesing url '/generator', follow the point '2' of Deploy Apps". PHP_EOL;
            $phpArrayString .= "|". PHP_EOL;
            $phpArrayString .= "*/". PHP_EOL;
            
            $phpArrayString .= 'return [' . PHP_EOL;
            foreach ($routes as $item) {
                $phpArrayString .= "    [" . PHP_EOL;
                $phpArrayString .= "        'name'=> '{$item->name}', 'path'=> '{$item->path}'" . PHP_EOL;
                $phpArrayString .= "    ]," . PHP_EOL;
            }
            $phpArrayString .= '];' . PHP_EOL;


            fwrite($create, $phpArrayString);
            fclose($create);

            $this->comment("...........");
            $this->comment(count($routes) . " routes has generated.");


        } else {
            $this->error("ui_route.json not found in dir : /routes");
            $this->error("make sure you has download the ui_route.json from generator in frontend.");
            $this->error("or you can access url '/generator' for download the file, follow point '2' of Deploy Apps ");
        }

    }
}
