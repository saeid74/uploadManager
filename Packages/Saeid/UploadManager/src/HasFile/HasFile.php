<?php
namespace Saeid\UploadManager\HasFile;


interface HasFile
{
    /**
    * Set the polymorphic relation.
    *
    * @return mixed
    */
    public function file();


    /**
    * Move a file to the medialibrary.
    *
    * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
    *
    * @return \Spatie\MediaLibrary\FileAdder\FileAdder
    */
    public function addFile($file);


    /**
    * Move a file to the medialibrary.
    *
    * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
    *
    * @return \Spatie\MediaLibrary\FileAdder\FileAdder
    */
    public function updateFile($newFile, string $roleName = 'default');


    /**
    * Copy a file to the medialibrary.
    *
    * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
    *
    * @return \Spatie\MediaLibrary\FileAdder\FileAdder
    */
    public function copyFile($file);


    /**
    * Determine if there is media in the given collection.
    *
    * @param $collectionMedia
    *
    * @return bool
    */
    public function hasFile(string $role = '') : bool;


    /**
    * Get media collection by its collectionName.
    *
    * @param string         $collectionName
    * @param array|callable $filters
    *
    * @return \Illuminate\Support\Collection
    */
    public function getFile(string $roleName = 'default', $filtersThumbnail = []);


    /**
    * Remove all media in the given collection.
    *
    * @param string $collectionName
    */
    public function clearFileRole(string $roleName = 'default');


    /**
    * Cache the media on the object.
    *
    * @param string $collectionName
    *
    * @return mixed
    */
    public function loadFile(string $role);


}
