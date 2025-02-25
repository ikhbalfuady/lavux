<?php
namespace App\Services;
use Illuminate\Support\Facades\Storage;
use Exception;

class Uploader {

    /**
     * Basic upload file
     *
     * @param Object $file : instance of $request->hasFile('file')
     * @param String $fileName : when null, set by default original name
     * @param String $path : path location of file
     * @param String $allowExtension : extension validation, when value is [] , validation aborted
     *
     */
    public static function upload($file, $path = 'public/uploads', $fileName = null , $allowExtension = []) {
        try {
            if ($file) {
                $name =  $file->getClientOriginalName();
                $ext = $file->getClientOriginalExtension();
                $mime = $file->getMimeType();
                $size = $file->getSize();

                if (!$fileName) $fileName = uniqid("FU_");
                if ($fileName == 'original') $fileName = $name;

                $fileName = "{$fileName}.{$ext}";

                if (count($allowExtension)) {
                    $extensions = [];
                    foreach ($allowExtension as $extension) {
                        $extensions[] = strtolower($extension);
                    }
                    if (!in_array(strtolower($ext), $extensions)) throw new Exception("File with extension <b>$ext </b> is denied to upload!");
                }

                Storage::putFileAs($path, $file, $fileName); // upload file

                return (object) [
                    "name" => $fileName,
                    "mime" => $mime,
                    "size" => $size,
                    "size" => H_fileSize($size),
                    "path" => $path,
                    "fix_path" => Storage::url($path),
                    "url" => env('APP_URL') . Storage::url("$path/{$fileName}"),
                ];
            } else {
                throw new Exception("No file uploaded!");
            }

        } catch (Exception $e){
            throw new Exception(H_throw($e, '['.get_class().'::upload]'));
        }
    }

}
