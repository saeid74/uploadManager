<?php
namespace Saeid\UploadManager\HasFile;

use Saeid\UploadManager\FileAdder\FileAdderFactory;
use Saeid\UploadManager\FileAdder\FileAdder;

trait HasFileTrait
{
    /**
    * Add a file from a request.
    *
    * @param string $key
    *
    * @return \Spatie\MediaLibrary\FileAdder\FileAdder
    */
    public function addFile(string $key)
    {
        return app(FileAdderFactory::class)->createFromRequest($this);
    }


    /**
    * Add a file from a request.
    *
    * @param string $key
    *
    * @return \Spatie\MediaLibrary\FileAdder\FileAdder
    */
    public function addFileFromRequest(string $key)
    {
        return app(FileAdderFactory::class)->createFromRequest($this, $key);
    }


    /**
    * Add a file from a request.
    *
    * @param string $key
    *
    * @return \Spatie\MediaLibrary\FileAdder\FileAdder
    */
    public function addFileCropFromRequest(string $keyFile,string $keyCropData)
    {
        return app(FileAdderFactory::class)->createFromRequest($this, $keyFile, $keyCropData);
    }


    /**
    * Copy a file to the medialibrary.
    *
    * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
    *
    * @return \Spatie\MediaLibrary\FileAdder\FileAdder
    */
    public function copyFile($file)
    {
        return $this->addFile($file)->preservingOriginal();
    }


    /*
    * Determine if there is media in the given collection.
    */
    public function hasFile(string $roleName = 'default'): bool
    {
        return count($this->getFile($roleName)) ? true : false;
    }


    /**
    * Get media collection by its collectionName.
    *
    * @param string $collectionName
    * @param array|callable $filters
    *
    * @return \Illuminate\Support\Collection
    */
    public function getFile(string $roleName = 'default', $filters = []): Collection
    {
        // return app(MediaRepository::class)->getCollection($this, $roleName, $filters);
    }


    public function getFirstFile(string $roleName = 'default', array $filters = []): ?Media
    {
        // $file = $this->getMedia($roleName, $filters);
        // return $file->first();
    }


    /*
    * Get the url of the image for the given conversionName
    * for first media for the given collectionName.
    * If no profile is given, return the source's url.
    */
    public function getFirstFileUrl(string $roleName = 'default', string $conversionName = ''): string
    {
        $file = $this->getFirstFile($roleName);
        if (! $file) {
            return '';
        }
        return $file->getUrl($conversionName);
    }


    /*
    * Get the url of the image for the given conversionName
    * for first media for the given collectionName.
    * If no profile is given, return the source's url.
    */
    public function getFirstFilePath(string $collectionName = 'default', string $conversionName = ''): string
    {
        $media = $this->getFirstFile($collectionName);
        if (! $media) {
            return '';
        }
        return $media->getPath($conversionName);
    }


    /**
    * Update a media collection by deleting and inserting again with new values.
    *
    * @param array $newMediaArray
    * @param string $collectionName
    *
    * @return \Illuminate\Support\Collection
    *
    * @throws \Spatie\MediaLibrary\Exceptions\MediaCannotBeUpdated
    */
    public function updateFile($newFile, string $roleName = 'default'): Collection
    {
        // $this->removeMediaItemsNotPresentInArray($newMediaArray, $collectionName);
        // return collect($newMediaArray)
        // ->map(function (array $newMediaItem) use ($collectionName) {
        //     static $orderColumn = 1;
        //     $mediaClass = config('medialibrary.media_model');
        //     $currentMedia = $mediaClass::findOrFail($newMediaItem['id']);
        //     if ($currentMedia->collection_name !== $collectionName) {
        //         throw MediaCannotBeUpdated::doesNotBelongToCollection($collectionName, $currentMedia);
        //     }
        //     if (array_key_exists('name', $newMediaItem)) {
        //         $currentMedia->name = $newMediaItem['name'];
        //     }
        //     if (array_key_exists('custom_properties', $newMediaItem)) {
        //         $currentMedia->custom_properties = $newMediaItem['custom_properties'];
        //     }
        //     $currentMedia->order_column = $orderColumn++;
        //     $currentMedia->save();
        //     return $currentMedia;
        // });
    }


    public function clearFileRole(string $collectionName = 'default'): self
    {
        // $this->getMedia($collectionName)
        // ->each->delete();
        // event(new CollectionHasBeenCleared($this, $collectionName));
        // if ($this->mediaIsPreloaded()) {
        //     unset($this->media);
        // }
        // return $this;
    }


    /**
    * Delete the associated media with the given id.
    * You may also pass a media object.
    *
    * @param int|\Spatie\MediaLibrary\Models\Media $mediaId
    *
    * @throws \Spatie\MediaLibrary\Exceptions\MediaCannotBeDeleted
    */
    public function deleteFile($mediaId)
    {
        // if ($mediaId instanceof Media) {
        //     $mediaId = $mediaId->id;
        // }
        // $media = $this->media->find($mediaId);
        // if (! $media) {
        //     throw MediaCannotBeDeleted::doesNotBelongToModel($mediaId, $this);
        // }
        // $media->delete();
    }


    /**
    * Cache the media on the object.
    *
    * @param string $collectionName
    *
    * @return mixed
    */
    public function loadFile(string $collectionName)
    {
        // $collection = $this->exists
        // ? $this->media
        // : collect($this->unAttachedMediaLibraryItems)->pluck('media');
        // return $collection
        // ->filter(function (Media $mediaItem) use ($collectionName) {
        //     if ($collectionName == '') {
        //         return true;
        //     }
        //     return $mediaItem->collection_name === $collectionName;
        // })
        // ->sortBy('order_column')
        // ->values();
    }

}
