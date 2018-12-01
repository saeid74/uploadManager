<?php
namespace Saeid\UploadManager\FileAdder;

use Saeid\UploadManager\Exceptions\FileCannotBeAdded\RequestDoesNotHaveFile;

class FileAdderFactory
{

    public static function create(Model $subject, $file)
    {

    }

    public static function createFromRequest(Model $subject, string $key): FileAdder
    {

    }

    public static function createMultipleFromRequest(Model $subject, array $keys = []): Collection
    {

    }

    public static function createAllFromRequest(Model $subject): Collection
    {

    }

}
