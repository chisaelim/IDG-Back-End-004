<?php

namespace App\Traits;

use Exception;
use Throwable;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Decoders\Base64ImageDecoder;
use Intervention\Image\Decoders\DataUriImageDecoder;

trait UploadMethod
{
    public static function storeImage($image, $folder_name)
    {
        try {
            $image_name = 'IMG-' . uniqid() . '.png';
            $manager = new ImageManager(new Driver());
            $processedImage = $manager->read($image, [
                DataUriImageDecoder::class,
                Base64ImageDecoder::class,
            ]);

            $encoded = $processedImage->toPng();
            // Save image
            Storage::disk('public')->put(
                "$folder_name/$image_name",
                $encoded
            );

            return $image_name;
        } catch (Exception $e) {
            throw $e;
        } catch (Throwable $th) {
            throw $th;
        }
    }

    public static function discardImage($image_name, $folder_name)
    {
        try {
            // Delete from storage/app/public/{folder_name}
            if (Storage::disk('public')->exists("$folder_name/$image_name")) {
                Storage::disk('public')->delete("$folder_name/$image_name");
            }
            return true;
        } catch (Exception $e) {
            throw $e;
        } catch (Throwable $th) {
            throw $th;
        }
    }
}
