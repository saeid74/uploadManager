<?php
namespace Saeid\UploadManager\FileAdder;


class FileAdder
{
    protected $role;
    protected $path;
    protected $file;
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

    public function setPath(int $path)
    {
        $this->path = $path;
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

    public function cropImage()
    {

    }

    public function addFile($file): self
    {

    }

    public function addImage($file): self
    {

    }

    public function uploadFile()
    {

    }

    public function saveInDb()
    {
        
    }

    public function attachFile()
    {

    }

    public function preservingOriginal(): self
    {
        $this->preserveOriginal = true;
        return $this;
    }

}
