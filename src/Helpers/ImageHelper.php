<?php

namespace App\Helpers;

use App\Entity\Recipe;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ImageHelper

{
    private $imageDir;
    private $thumbnailsDir;

    public function __construct($imageDir, $thumbnailsDir)
    {
        $this->imageDir = $imageDir;
        $this->thumbnailsDir = $thumbnailsDir;
    }

    public function replaceRecipeImage($image = null, $originalImage = null, Recipe $recipe = null)
    {
        //s'il y a une image
        if ($image) {
            //on supprime l'ancienne
            if (!is_null($originalImage)) {
                $this->removeImage($originalImage);
            }
            //et on enregistre la nouvelle
            $this->registerNewImage($image, $recipe);
        }
        //sinon on garde l'ancienne
        else {
            $recipe->setImage($originalImage);
        }
    }

    public function removeImage($image): void
    {
        unlink($this->imageDir . '/' . $image);
        unlink($this->thumbnailsDir . '/' . $image);
    }

    private function registerNewImage($image, Recipe $recipe): void
    {
        $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
        $newImageName = $safeFilename . '-' . uniqid() . '.' . $image->guessClientExtension();
        try {
            $image->move($this->imageDir, $newImageName);
            $recipe->setImage($newImageName);
        } catch (FileException $e) { }
    }
}
