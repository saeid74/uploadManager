<?php
return [

    /*
     * The filesystems on which to store added files and derived images by default. Choose
     * one or more of the filesystems you've configured in config/filesystems.php.
     */
    'default_filesystem' => [
        'image' => 'media',
        'file' =>  'media'
    ],

    /*
     * The maximum file size of an item in bytes.
     * Adding a larger file will result in an exception.
     */
    'max_file_size' => 1024 * 1024 * 10,

    /*
     * The class names of the models that should be used.
     */
    'gallery_model' => \App\Gallery::class,

    /*
     * The class names of the models that should be used.
     */
    'interface_model' => \App\Uploadable::class,

];
