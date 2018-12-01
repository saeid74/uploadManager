<?php

namespace App\Appended\Classes;

use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Gallery;

class UploadManager3
{
    protected $businessId;
    protected $destinationDir;
    protected $fileName;
    protected $fileThumbName;
    protected $fileMultiThumbName = [];
    protected $startPortion = 'img';
    protected $imageExtension = 'jpg';
    protected $hasMultiThumb = true;
    protected $quality = 70;
    protected $orginalImageQuality = 100;
    protected $random = true;
    protected $imageOperations = null;
    protected $suffix = 'thumbnail';
    protected $image;
    protected $imageThumb;
    protected $orginalImage;
    protected $model = null;
    protected $cropData = null;
    protected $cropOrginalImage = false;
    protected $storeInDB = true;
    protected $spUserTemplateId = null;
    protected $imageRole;
    protected $userId = null;
    protected $orginalImageSize = null; // Change Size Of Orginal Image (width,height) keys store
    public $fullFileName;
    function __construct()
    {
        Image::configure(array('driver' => 'imagick'));
    }



    public function inPath($upload_path, $params = null)
    {
        $config_path = config('image.upload_paths.' . $upload_path);
        if ($params !== null) {
            preg_match_all('/\((.*?)\)/', $config_path, $params_match);
            if (!empty($params_match)) {
                foreach ($params_match[1] as $matched_value) {
                    $config_path = str_replace('(' . $matched_value . ')', $params[$matched_value], $config_path);
                }
            }
        }
        preg_match_all('/{(.*?)}/', $config_path, $match);
        if (!empty($match)) {
            foreach ($match[1] as $matched_value) {
                $function_name = 'find_' . $matched_value;
                $value = $this->{$function_name}();
                $config_path = str_replace('{' . $matched_value . '}', $value, $config_path);
            }
        }

        $this->destinationDir = $config_path;
        return $this;
    }

    public function storeInDB($storeInDB)
    {
        $this->storeInDB = $storeInDB;
        return $this;
    }

    public function setBusinessId($businessId)
    {
        $this->businessId = $businessId;
        return $this;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    public function setSpUserTemplateId($spUserTemplateId)
    {
        $this->spUserTemplateId = $spUserTemplateId;
        return $this;
    }
    public function setHasMultiThumb($hasMultiThumb)
    {
        $this->hasMultiThumb = $hasMultiThumb;
        return $this;
    }

    public function setImageRole($imageRole)
    {
        $this->imageRole = $imageRole;
        return $this;
    }

    public function setQuality($quality)
    {
        $this->quality = $quality;
        return $this;
    }

    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;
        return $this;
    }

    public function setStyle($image_styles)
    {
        if (gettype($image_styles) === 'string') {
            $this->imageOperations = config('image.image_styles.' . $image_styles);
        } elseif($image_styles) {
            $this->hasMultiThumb = false;
            $this->imageOperations = $image_styles;
        }
        return $this;
    }

    public function withName($fileName, $random = true)
    {
        $this->startPortion = $fileName;
        $this->random = $random;
        return $this;
    }

    public function setExtension($extension)
    {
        $this->imageExtension = $extension;
        return $this;
    }

    public function setOrginalImageSize($size = null)
    {
        if (is_array($size) && count($size) === 2) {
            $this->orginalImageSize = ['width' => reset($size), 'height' => end($size)];
        } elseif ($size === 'match') {
            if (is_array($this->imageOperations)) {
                $size = array_get($this->imageOperations, 'resize', null);
                $this->orginalImageSize = $size;
            } else throw new \Exception('first use setStyle function next use this function');
        }
        return $this;
    }

    private function generate_image_name()
    {
        $randName = $this->startPortion;
        if ($this->random === true) {
            $randName .= '_' . uniqid();
        }
        $this->fileName = $randName . '.' . $this->imageExtension;
    }

    private function generate_thumb_name()
    {
        if ($this->imageOperations !== null)
            $size = array_get($this->imageOperations, 'resize', null);
        else
            $size = ['width' => 0, 'height' => 0];
//        throw new \Exception('resize operation not found.');
        $name = str_replace(['#', '/', '\\', ' ','\''], '-', pathinfo($this->fileName, PATHINFO_FILENAME));
        $extension = pathinfo($this->fileName, PATHINFO_EXTENSION);
        $this->fileThumbName = sprintf('%s_%s_%d_%d.%s', $name, $this->suffix, $size['width'], $size['height'], $extension);
    }

    private function generate_multi_thumb_name()
    {
        if ($this->imageOperations !== null)
            $size = array_get($this->imageOperations, 'temp_size', ['M' => ['width' => 0, 'height' => 0]]);
        else
            $size = ['M' => ['width' => 0, 'height' => 0]];

        $name = str_replace(['#', '/', '\\', ' ','\'',"'"], '-', pathinfo($this->fileName, PATHINFO_FILENAME));
        $extension = pathinfo($this->fileName, PATHINFO_EXTENSION);
        foreach ($size as $key => $value) {
            $this->fileMultiThumbName[$key] = sprintf('%s_%s_%s_%d_%d.%s', $name, $this->suffix, $key, $value['width'], $value['height'], $extension);
        }
    }

    /**************************************************************
     * |
     * |   set image and cropData and model in this instance
     * |
     **************************************************************/

    public function addImage($image, $cropData = null, $cropOrginalImage = false, $model = null)
    {
        $this->image = Image::make($image);
        $this->orginalImage = Image::make($image);
        if ($image instanceof UploadedFile) {
            $this->imageExtension = $image->getClientOriginalExtension();
        } else {
            $mimeType = $this->image->mime();
            $this->imageExtension = $this->checkImageMimeType($mimeType);
        }
        $this->cropData = $cropData;
        $this->cropOrginalImage = $cropOrginalImage ? true : false;
        $this->model = $model;
        return $this;
    }


    /**************************************************************
     * |
     * |   helper function for cropping image and resize image
     * |
     * |   based on data in imageConfig.config file
     * |
     **************************************************************/

    private function crop()
    {
        if ($this->cropData !== null) {
            $cropData = $this->cropData;
            $this->image = $this->image->crop($cropData[0], $cropData[1], $cropData[2], $cropData[3]);
            if($cropData[2] < 0 or $cropData[3] < 0) {
                $background = Image::canvas($cropData[0], $cropData[1]);
                $background->insert($this->image, 'center');
                $this->image = $background;
            }
        }

        if (!empty($this->imageOperations)) {
            foreach ($this->imageOperations as $name => $parameters) {
                if($name != 'temp_size')
                    $this->{$name} ($parameters);
            }
        }
    }

    private function cropOrginalImage()
    {
        if ($this->cropData !== null) {
            $cropData = $this->cropData;
            if ($this->cropOrginalImage) {
                $this->orginalImage = $this->orginalImage->crop($cropData[0], $cropData[1], $cropData[2], $cropData[3]);
                $background = Image::canvas($cropData[0], $cropData[1]);
                $background->insert($this->orginalImage, 'center');
                $this->orginalImage = $background;
            }
        }

        if (is_array($this->orginalImageSize)) {
            $this->resizeOrginalImage($this->orginalImageSize);
        }
    }

    /**************************************************************
     * |
     * |   after all operations that applyed on image
     * |
     * |   this function calls
     * |
     **************************************************************/

    private function storeThumbnail($fileThumbName = null, $role = null)
    {
        $fileThumbName = ($fileThumbName) ? $fileThumbName : $this->fileThumbName;
        $role          = ($role) ? $role : $this->imageRole;
        $path = $this->upload_path($this->destinationDir . '/' . $fileThumbName);
        Storage::disk('public')->makeDirectory($this->destinationDir);
        if($role) {
            $size = array_get($this->imageOperations, 'temp_size.'.$role,null);
            $this->thumbnail_size($size);
            $this->imageThumb->save(public_path($path), $this->quality);
        } else {
            $this->image->save(public_path($path), $this->quality);
        }
        return $path;
    }

    public function storeOrginalImage()
    {
        $path = $this->upload_path($this->destinationDir . '/' . $this->fileName);
        Storage::disk('public')->makeDirectory($this->destinationDir);
        $this->orginalImage->save(public_path($path), $this->orginalImageQuality);
        return $path;
    }

    /**************************************************************
     * |
     * |   stores image in database
     * |
     **************************************************************/

    private function storeDatabase($path, $cropData, $parentId)
    {
        if ($this->model != null) {
            return $this->model->images()->save(
                $this->storeGallery($path, $cropData, $parentId),
                ['image_role' => $this->imageRole]
            );
        } else {
            if ($this->storeInDB) {
                return $this->storeGallery($path, $cropData, $parentId);
            }
        }
    }

    /**************************************************************
     * |
     * |   store image in gallery table
     * |
     **************************************************************/

    private function storeGallery($path, $cropData = null, $parentId = null)
    {
        $gallery = new Gallery();
        $gallery->user_id = $this->find_user_id();
        $gallery->business_id = $this->businessId;
        $gallery->sp_user_template_id = $this->spUserTemplateId;
        $gallery->path = $path;
        $gallery->parent_id = $parentId;
        $gallery->crop_data = $cropData;
        $gallery->save();
        return $gallery;
    }

    /**************************************************************
     * |
     * |   helper function for cropping image and storing in table
     * |
     **************************************************************/

    private function croppedSave($parentId = null)
    {
        $this->crop();
        $imageRole = $this->imageRole;
        if($this->hasMultiThumb) {
            $Gallery = [];
            foreach ($this->fileMultiThumbName as $role => $size) {
                $path = $this->storeThumbnail($size, $role);
                $this->setImageRole($role);
                $Gallery[] = $this->storeDatabase($path, $this->cropData, $parentId);
            }
            $this->setImageRole($imageRole);
            if(count($Gallery) > 1) {
                return $Gallery;
            } else {
                return reset($Gallery);
            }
        } else {
            $path = $this->storeThumbnail();
            return $this->storeDatabase($path, $this->cropData, $parentId);
        }
    }

    /**************************************************************
     * |
     * |   store cropped image as an independent image
     * |
     **************************************************************/

    public function justCropSave()
    {
        $this->generate_image_name();
        return $this->croppedSave();
    }

    /**************************************************************
     * |
     * |   crop an image and save it and set it's origional image
     * |
     * |   as its parent
     * |
     **************************************************************/

    public function cropSave($parentId)
    {
        $this->generate_image_name();
        if($this->hasMultiThumb) {
            $this->generate_multi_thumb_name();
        } else {
            $this->generate_thumb_name();
        }
        return $this->croppedSave($parentId);
    }

    /**************************************************************
     * |
     * |   crop image based on another image in database
     * |
     **************************************************************/

    public function cropFromImageId($imageId, $cropData)
    {
        $gallery = \App\Gallery::select('id', 'path')->find($imageId);
        if ($gallery) {
            $this->addImage(public_path($gallery->path), $cropData);
            $this->generate_image_name();
            if($this->hasMultiThumb) {
                $this->generate_multi_thumb_name();
            } else {
                $this->generate_thumb_name();
            }
            return $this->croppedSave($gallery->id);
        }
    }

    public function cropAndSaveFromImageId($imageId, $cropData, $cropOrginalImage = false, $model = null)
    {
        $gallery = \App\Gallery::select('id', 'path')->with('children')->find($imageId);
        if ($gallery && file_exists(public_path($gallery->path))) {
            $this->addImage(public_path($gallery->path), $cropData, $cropOrginalImage, $model);
            $this->generate_image_name();
            if($this->hasMultiThumb) {
                $this->generate_multi_thumb_name();
            } else {
                $this->generate_thumb_name();
            }
            $this->cropOrginalImage();
            $mainPath = $this->storeOrginalImage();
            $this->crop();
            if($this->hasMultiThumb) {
                $thumbPath = [];
                foreach ($this->fileMultiThumbName as $role => $size) {
                    $thumbPath[$role] = $this->storeThumbnail($size, $role);
                }
                $this->RemoveAndSaveInDBThumb($gallery, $thumbPath);
            } else {
                $thumbPath = $this->storeThumbnail();
                $this->RemoveAndSaveInDBThumb($gallery, $thumbPath);
            }
            Storage::delete(static::storage_path($gallery->path));
            $gallery->path = $mainPath;
            $gallery->save();
        }
    }
    private function RemoveAndSaveInDBThumb($gallery, $thumbPath)
    {
        $childs = $gallery->children()->get();
        foreach ($childs as $key => $child) {
            Storage::delete(static::storage_path($child->path));
            $child->roles()->delete();
            $child->delete();
        }
        $imageRole = $this->imageRole;
        if(is_array($thumbPath)) {
            foreach ($thumbPath as $key => $path) {
                $this->setImageRole($key);
                $this->storeDatabase($path, $this->cropData, $gallery->id);
            }
        } else {
            $this->storeDatabase($thumbPath, $this->cropData, $gallery->id);
        }
        $this->setImageRole($imageRole);
    }

    /**************************************************************
     * |
     * |   crop image based on another image in path
     * |
     **************************************************************/

    public function cropFromImagePath()
    {
        $this->generate_image_name();
        if($this->hasMultiThumb) {
            $this->generate_multi_thumb_name();
        } else {
            $this->generate_thumb_name();
        }
        return $this->croppedSave();
    }

    /**************************************************************
     * |
     * |   just crop image file and save it as a file
     * |
     **************************************************************/

    public function imageFileSave()
    {
        $this->generate_image_name();
        if($this->hasMultiThumb) {
            $this->generate_multi_thumb_name();
        } else {
            $this->generate_thumb_name();
        }
        $this->cropOrginalImage();
        $path = $this->storeOrginalImage();
        return $this->storeDatabase($path, $this->cropData, null);
    }

    /**************************************************************
     * |
     * |   store image file and store it in gallery table
     * |
     **************************************************************/

    public function imageSave()
    {
        $path = $this->imageFileSave();
        return $this->storeGallery($path);
    }

    public function imageWithCroppedSaveWithOutDb()
    {
        $this->generate_image_name();
        if($this->hasMultiThumb) {
            $this->generate_multi_thumb_name();
        } else {
            $this->generate_thumb_name();
        }
        $this->cropOrginalImage();
        $path = $this->storeOrginalImage();
        $this->crop();
        if($this->hasMultiThumb) {
            $thumbPath = [];
            foreach ($this->fileMultiThumbName as $role => $size) {
                $thumbPath[$role] = $this->storeThumbnail($size, $role);
            }
        } else {
            $thumbPath = $this->storeThumbnail();
        }
        return ['mainPath' => $path, 'thumbPath' => $thumbPath];
    }

    /**************************************************************
     * |
     * |   store image and its crop file
     * |
     **************************************************************/

    public function imageWithCroppedSave()
    {
        $image = $this->imageFileSave();
        return $this->croppedSave($image->id);
    }

    public static function moveFilesToNewPath($files, $new_path, $user, $model = null, $role = null)
    {
        foreach ($files as $file) {
            $old_path = self::storage_path($file->path);
            $base_name = basename($old_path);
            $new_path = trim($new_path, '/');
            $new_path = self::storage_path($new_path);
            $full_new_base_path = $new_path . '/' . $base_name;
            Storage::move($old_path, $full_new_base_path);
            $file->path = self::upload_path($full_new_base_path);
            $file->user_id = $user->id;
            $file->save();
        }
    }
    public function moveFilesAndStoreInDB($files, $model)
    {
        $this->model = $model;
        $images = [];
        if(!is_array ($files)){
            $files = [$files];
        }
        foreach ($files as $file) {
            $old_path = self::storage_path($file);
            $base_name = basename($old_path);
            $new_path = $this->destinationDir . '/' . $base_name;
            Storage::disk('public')->makeDirectory($this->destinationDir);
            Storage::move($old_path, $new_path);
            $gallery = $this->storeDatabase($new_path, null, null);
            $images[$old_path] = $new_path;

            //Store Thumbnail
            if($this->imageRole) {
                $this>generate_multi_thumb_name();
                foreach ($this->fileMultiThumbName as $role => $size) {
                    $path = $this->storeThumbnail($size, $role);
                    $this->setImageRole($role);
                    $this->storeDatabase($path, null, $gallery->id);
                }
                $this->setImageRole($imageRole);
            }

        }
        return $images;
    }

    public static function removeLastImages($image)
    {
        if ($image) {
            if ($image->roles->count() <= 1) {
                $old_path = $image->path;
                $childrens = $image->children()->get();
                if ($childrens) {
                    foreach ($childrens as $key => $children) {
                        Storage::delete(static::storage_path($children->path));
                        $children->roles()->delete();
                        $children->delete();
                    }
                }
                $delete_file_status = Storage::delete(static::storage_path($old_path));
                $image->roles()->delete();
                $delete_record_status = $image->delete();
                return ['delete_record_status' =>$delete_record_status, 'delete_file_status' => $delete_file_status];
            }
        }
    }


    public static function removeImages($model, $conditions = null)
    {
        if ($model) {
            $model->load(['images' => function ($query) use ($conditions) {
                $query->where('parent_id', null)->when($conditions, function ($queryWhen) use ($conditions, $query)
                {
                    $query->wherePivotIn('image_role', $conditions)->withCount('usages');
                });
            }]);
            if ($model->images->isNotEmpty()) {
                foreach ($model->images as $image) {
                    if ($image->usages_count <= 1) {
                        if ($image->children && $image->children->count() > 0) {
                            foreach ($image->children as $key => $children) {
                                Storage::delete(static::storage_path($children->path));
                                $model->images()->detach($children);
                                $children->delete();
                            }
                        }
                        Storage::delete(static::storage_path($image->path));
                        $model->images()->detach($image);
                        $image->delete();
                    } else {
                        Storage::delete(static::storage_path($image->path));
                        $image->delete();
                    }
                }
            }
        }
    }


    public static function upload_path($path)
    {
        return 'uploads/' . $path;
    }

    public static function storage_path($path)
    {
        if (starts_with($path, 'uploads/')) {
            $path = substr($path, 8);
        }
        return $path;
    }

    public function resize($params)
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
            $background = Image::canvas($widthInput, $heightInput);
            $background->insert($this->image, 'center');
            $this->image = $background;
        }

        $this->image->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
        });
    }

    public function thumbnail_size($params)
    {
        $width  = array_get($params, 'width', 640);
        $height = array_get($params, 'height', null);

        $this->imageThumb = clone $this->image;

        if ($height != null) {
            $widthInput = $this->imageThumb->width();
            $heightInput = $this->imageThumb->height();

            $aspectRatio = $height / $width;

            if ((int)($widthInput * $height / $width) < (int)$heightInput) {
                $widthInput = round($heightInput / $aspectRatio);
            } else {
                $heightInput = round($widthInput * $aspectRatio);
            }
            $background = Image::canvas($widthInput, $heightInput);
            $background->insert($this->imageThumb, 'center');
            $this->imageThumb = $background;
        }

        $this->imageThumb->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
        });
    }

    public function resizeOrginalImage($params)
    {
        $width = array_get($params, 'width', 640);
        $height = array_get($params, 'height', null);

        if ($height != null) {
            $widthInput = $this->orginalImage->width();
            $heightInput = $this->orginalImage->height();

            $aspectRatio = $height / $width;

            if ((int)($widthInput * $height / $width) < (int)$heightInput) {
                $widthInput = round($heightInput / $aspectRatio);
            } else {
                $heightInput = round($widthInput * $aspectRatio);
            }
            $background = Image::canvas($widthInput, $heightInput);
            $background->insert($this->orginalImage, 'center');
            $this->orginalImage = $background;
        }

        $this->orginalImage->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
        });
    }

    private function find_business_name()
    {
        $store_name = camel_case(session('ActiveBusinessName'));
        if ($store_name === null) {
            throw new \Exception('Business name not found.');
        }
        return $store_name;
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

    public function checkImageMimeType($mimeType)
    {
        switch ($mimeType) {
            case 'image/png':
                return 'png';
            case 'image/bmp':
                return 'bmp';
            case 'image/jpeg':
                return 'jpg';
            case 'image/gif':
                return 'gif';
            default:
                return 'jpg';
        }
    }

    /**************************************************************
     * |
     * |   register tinymce and dropzone
     * |
     **************************************************************/

     public function moveAndSaveImagesWithUniqueId($uniqueId, $model)
     {
         $images = glob("uploads/helps/help_51/*");
         return $this->moveFilesAndStoreInDB($images, $model);
     }
}
