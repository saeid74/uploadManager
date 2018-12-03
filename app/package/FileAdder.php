<?php
namespace App\package;

use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileAdder
{
    protected $role;
    protected $path;
    protected $file;
    protected $image;
    protected $model;
    protected $userId;
    protected $quality;
    protected $cropData;
    protected $fileName;
    protected $extension;
    protected $businessId;
    protected $startPortion;
    protected $waterMarkImage;
    protected $spUserTemplateId;


    public function __construct()
    {

    }

    public function setModel(Model $model)
    {
        $this->model = $model;
        return $this;
    }

    public function setCropData($cropData)
    {
        $this->cropData = $cropData;
        return $this;
    }

    public function setBusinessId(int $businessId)
    {
        $this->businessId = $businessId;
        return $this;
    }

    public function setUserId(int $userId)
    {
        $this->userId = $userId;
        return $this;
    }

    public function setPath(string $path = null)
    {
        if (!$path && $model && $model->imagePath ) {
            $this->path = $model->imagePath;
        } else {
            $this->path = $path;
        }
        return $this;
    }

    public function setQuality(int $quality)
    {
        $this->quality = $quality;
        return $this;
    }

    public function setWaterMark(int $wateMark)
    {
        $this->wateMark = $wateMark;
        return $this;
    }

    public function setSpUserTemplateId(int $spUserTemplateId)
    {
        $this->spUserTemplateId = $spUserTemplateId;
        return $this;
    }

    private function find_user_id()
    {
        if($this->userId) {
            return $this->userId;
        } elseif(auth()->check()) {
            return auth()->user()->id;
        } else {
            throw new \Exception('User name not found.');
        }
    }

    public function usingName(string $fileName, $random = true)
    {
        $this->startPortion = $fileName;
        $this->random = $random;
        return $this;
    }

    private function generate_image_name()
    {
        $randName = $this->startPortion;
        if ($this->random) {
            $randName .= '-' . uniqid();
        }
        $this->fileName = $randName . '.' . $this->extension;
    }

    private function defaultSanitizer(string $fileName): string
    {
        return str_replace(['#', '/', '\\', ' ', "'"], '-', $fileName);
    }

    private function setDirectroy()
    {
        if(!$this->path) {
            $this->path = config('');
        }
        preg_match_all('/{(.*?)}/', $this->path, $match);
        if (!empty($match)) {
            foreach ($match[1] as $matched_value) {
                $function_name = 'find_' . $matched_value;
                $value = $this->{$function_name}();
                $this->destinationDir = str_replace('{' . $matched_value . '}', $value, $this->path);
            }
        } else {
            $this->destinationDir = $this->path;
        }
    }

    private function getImageExtension()
    {
        switch ( $this->image->mime() ) {
            case 'image/png':
                $this->extension = 'png';
            case 'image/bmp':
                $this->extension = 'bmp';
            case 'image/jpeg':
                $this->extension = 'jpg';
            case 'image/gif':
                $this->extension = 'gif';
            default:
                $this->extension = '';
        }
    }

    private function cropImage()
    {

    }

    public function addFile($file): self
    {
        $this->file = $file;
    }

    public function addImage($file): self
    {
        $this->file  = $file;
        $this->image = Image::make($file);
        $this->getImageExtension();
    }

    public function uploadFile()
    {
        $this->setPath();
        $this->setDirectroy();
    }

    public function saveInDb()
    {

    }

    public function attachFile()
    {
        $this->uploadFile();
        $this->saveInDb();
    }

    public function preservingOriginal(): self
    {
        $this->preserveOriginal = true;
        return $this;
    }

}
