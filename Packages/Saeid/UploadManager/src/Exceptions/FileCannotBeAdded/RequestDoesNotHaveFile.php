<?php

namespace Saeid\UploadManager\Exceptions\FileCannotBeAdded;

use Saeid\UploadManager\Exceptions\FileCannotBeAdded;

class RequestDoesNotHaveFile extends FileCannotBeAdded
{
    public static function create($key)
    {
        return new static("The current request does not have a file in a key named `{$key}`");
    }
}
