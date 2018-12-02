<?php
namespace App\package;

class FileAdderFactory
{
    
    public static function create(Model $model, $file)
    {
        return app(FileAdder::class)->setModel($subject)->setFile($file);
    }

    public static function createFromRequest(Model $model, string $key): FileAdder
    {
        $files = request()->file($key);
        if (! is_array($files)) {
            return static::create($model, $files);
        }
    }

}
