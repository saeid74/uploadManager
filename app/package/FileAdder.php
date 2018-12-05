<?php
namespace App\package;

use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileAdder
{
    protected $role;
    protected $path = null;
    protected $file;
    protected $image = null;
    protected $model = null;
    protected $userId;
    protected $quality = 80;
    protected $cropData = null;
    protected $fileName;
    protected $extension;
    protected $imageRole;
    protected $storeInDB = true;
    protected $businessId = null;
    protected $startPortion;
    protected $waterMarkImage;
    protected $backgroundImage = '#fff';
    protected $spUserTemplateId = null;


    public function __construct()
    {

    }

    public function setModel(Model $model)
    {
        $this->model = $model;
        return $this;
    }

    public function setStoreInDB(bool $storeInDB)
    {
        $this->storeInDB = $storeInDB;
        return $this;
    }

    public function setBackgroundImage(string $backgroundImage)
    {
        $this->backgroundImage = $backgroundImage;
        return $this;
    }

    public function setBusinessId(int $businessId)
    {
        $this->businessId = $businessId;
        return $this;
    }

    public function setImageRole($imageRole)
    {
        $this->imageRole = $imageRole;
        return $this;
    }

    public function setUserId(int $userId)
    {
        $this->userId = $userId;
        return $this;
    }

    public function setPath(string $path = null)
    {
        if (!$path && !$this->path && $model && $model->imagePath ) {
            $this->path = $model->imagePath;
        } elseif($path) {
            $this->path = $path;
        } else {
            // TODO: $this->path = config('path.');
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
        $randName = $this->defaultSanitizer($randName);
        $this->fileName = $randName . '.' . $this->extension;
    }

    private function defaultSanitizer(string $fileName): string
    {
        return str_replace(['#', '/', '\\', ' ', "'"], '-', $fileName);
    }

    private function setDirectroy()
    {
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

    private function uploadPath($path)
    {
        $imageDisk = config( 'uploadmanager.default_filesystem.image' );
        $fileDisk = config( 'uploadmanager.default_filesystem.file' );
        if ($this->image) {
            Storage::disk($this->imageDisk)->makeDirectory($this->destinationDir);
            return Storage::disk($this->imageDisk)->getDriver()->getAdapter()->getPathPrefix() . $path;
        } else {
            Storage::disk($this->fileDisk)->makeDirectory($this->destinationDir);
            return Storage::disk($this->fileDisk)->getDriver()->getAdapter()->getPathPrefix() . $path;
        }
    }

    public function addFile($file): self
    {
        $this->file = $file;
    }

    public function addImage($file, $cropData): self
    {
        $this->file  = $file;
        $this->cropData  = $cropData;
        $this->image = Image::make($file);
        if ($image instanceof UploadedFile) {
            $this->extension = $file->getClientOriginalExtension();
        } else {
            $this->getImageExtension();
        }
    }

    private function cropImage()
    {
        if ($this->cropData && $this->image) {
            $cropData = $this->cropData;
            $this->image = $this->image->crop($cropData[0], $cropData[1], $cropData[2], $cropData[3]);
            if($cropData[2] < 0 or $cropData[3] < 0) {
                $background = Image::canvas($cropData[0], $cropData[1], $this->backgroundImage);
                $background->insert($this->image, 'center');
                $this->image = $background;
            }
        }
        if ($this->wateMark) {
            $watermark = Image::make($this->wateMark);
            $this->image->insert($watermark, 'center');
        }
    }

    private function resizeImage($params)
    {
        $width = array_get($params, 'width', 640);
        $width = ( $width ) ? $width : 640;
        $height = array_get($params, 'height', null);
        if ($height != null) {
            $widthInput = $this->image->width();
            $heightInput = $this->image->height();

            $aspectRatio = $height / $width;

            if ((int)($widthInput * $height / $width) < (int)$heightInput) {
                $widthInput = round($heightInput / $aspectRatio);
            } else {
                $heightInput = round($widthInput * $aspectRatio);
            }
            $background = Image::canvas($widthInput, $heightInput, $this->backgroundImage);
            $background->insert($this->image, 'center');
            $this->image = $background;
        }

        $this->image->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
        });
    }

    public function uploadFile()
    {
        $this->setPath();
        $this->setDirectroy();
        $this->generate_image_name();
        $path = $this->uploadPath($this->destinationDir . '/' . $this->fileName);
        if ($this->image) {
            $this->image->save($path, $this->quality);
        } else {
            $this->file->move($path,$this->file->getClientOriginalName());
        }

        return $path;
    }

    private function storeGallery(string $path, int $parentId = null, string $image_size = null): Gallery
    {
        $mediaClass = config('uploadManager.model');
        $gallery = new $mediaClass();
        $gallery->user_id = $this->find_user_id();
        $gallery->business_id = $this->businessId;
        $gallery->sp_user_template_id = $this->spUserTemplateId;
        $gallery->parent_id = $parentId;
        $gallery->path = $path;
        $gallery->crop_data = $this->cropData;
        $gallery->image_size = $image_size;
        $gallery->save();
        return $gallery;
    }

    private function storeDatabase($path, $parentId = null, $imageSize = null)
    {
        if ($this->model) {
            return $this->model->images()->save(
                $this->storeGallery($path, $parentId, $image_size),
                ['image_role' => $this->role]
            );
        } else {
            if ($this->storeInDB) {
                return $this->storeGallery($path, $parentId, $imageSize);
            }
        }
    }

    public function attachFile()
    {
        $this->uploadFile();
        $this->storeDatabase();
        if ($this->image) {
            foreach ($this->model && $this->model as $key => $value) {
                $this->thumbnailImage($value);
            }
        }
    }
    public function thumbnailImage($param)
    {
        $this->image = Image::make($this->file);
        $this->cropImage();
        $this->uploadFile();
        $this->resizeImage($param);
        $image = $this->storeDatabase();
    }

}
