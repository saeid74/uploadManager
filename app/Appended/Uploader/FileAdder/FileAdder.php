<?php
namespace App\Appended\Uploader\FileAdder;

use Spatie\MediaLibrary\Helpers\File;
use Spatie\MediaLibrary\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\File as PendingFile;
use Spatie\MediaLibrary\Filesystem\Filesystem;
use Spatie\MediaLibrary\Jobs\GenerateResponsiveImages;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Spatie\MediaLibrary\MediaCollection\MediaCollection;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Spatie\MediaLibrary\ImageGenerators\FileTypes\Image as ImageGenerator;


use App\Appended\Uploader\Exceptions\FileCannotBeAdded\UnknownType;
use App\Appended\Uploader\Exceptions\FileCannotBeAdded\FileIsTooBig;
use App\Appended\Uploader\Exceptions\FileCannotBeAdded\DiskDoesNotExist;
use App\Appended\Uploader\Exceptions\FileCannotBeAdded\FileDoesNotExist;
use App\Appended\Uploader\Exceptions\FileCannotBeAdded\FileUnacceptableForCollection;
class FileAdder
{
    /** @var \Illuminate\Database\Eloquent\Model subject */
    protected $subject;
    /** @var \Spatie\MediaLibrary\Filesystem\Filesystem */
    protected $filesystem;
    /** @var bool */
    protected $preserveOriginal = false;
    /** @var string|\Symfony\Component\HttpFoundation\File\UploadedFile */
    protected $file;
    /** @var string */
    protected $pathToFile;
    /** @var string */
    protected $fileName;
    /** @var string */
    protected $mediaName;
    /** @var string */
    protected $diskName = '';
    /** @var null|callable */
    protected $fileNameSanitizer;
    /**
     * @param Filesystem $fileSystem
     */
    public function __construct(Filesystem $fileSystem)
    {
        $this->filesystem = $fileSystem;
        $this->fileNameSanitizer = function ($fileName) {
            return $this->defaultSanitizer($fileName);
        };

        $this->diskName = config("media.default_filesystem");
        if (is_null(config("filesystems.disks.{$this->diskName}"))) {
            throw DiskDoesNotExist::create($this->diskName);
        }
    }
    /**
     * @param \Illuminate\Database\Eloquent\Model $subject
     *
     * @return FileAdder
     */
    public function setSubject(Model $subject)
    {
        $this->subject = $subject;
        return $this;
    }
    /*
     * Set the file that needs to be imported.
     *
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @return $this
     */
    public function setFile($file): self
    {
        $this->file = $file;
        if (is_string($file)) {
            $this->pathToFile = $file;
            $this->setFileName(pathinfo($file, PATHINFO_BASENAME));
            $this->mediaName = pathinfo($file, PATHINFO_FILENAME);
            return $this;
        }
        if ($file instanceof UploadedFile) {
            $this->pathToFile = $file->getPath().'/'.$file->getFilename();
            $this->setFileName($file->getClientOriginalName());
            $this->mediaName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            return $this;
        }
        if ($file instanceof SymfonyFile) {
            $this->pathToFile = $file->getPath().'/'.$file->getFilename();
            $this->setFileName(pathinfo($file->getFilename(), PATHINFO_BASENAME));
            $this->mediaName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            return $this;
        }
        throw UnknownType::create();
    }
    public function preservingOriginal(): self
    {
        $this->preserveOriginal = true;
        return $this;
    }
    public function usingName(string $name): self
    {
        return $this->setName($name);
    }
    public function setName(string $name): self
    {
        $this->mediaName = $name;
        return $this;
    }
    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;
        return $this;
    }
    public function toMediaCollection(string $collectionName = 'default'): Media
    {
        if (! is_file($this->pathToFile)) {
            throw FileDoesNotExist::create($this->pathToFile);
        }
        if (filesize($this->pathToFile) > config('media.max_file_size')) {
            throw FileIsTooBig::create($this->pathToFile);
        }
        $mediaClass = config('media.media_model');
        $media = new $mediaClass();
        $media->name = $this->mediaName;
        $this->fileName = ($this->fileNameSanitizer)($this->fileName);
        $media->file_name = $this->fileName;
        $media->collection_name = $collectionName;
        $media->mime_type = File::getMimetype($this->pathToFile);
        $media->size = filesize($this->pathToFile);
        $media->responsive_images = [];
        $this->attachMedia($media);
        return $media;
    }
    public function defaultSanitizer(string $fileName): string
    {
        return str_replace(['#', '/', '\\', ' '], '-', $fileName);
    }
    public function sanitizingFileName(callable $fileNameSanitizer): self
    {
        $this->fileNameSanitizer = $fileNameSanitizer;
        return $this;
    }


// TODO: Crop And Save And Path And save connect To Model IF Has Model

}
