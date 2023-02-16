<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    private $targetDirectory;
    private $slugger;

    public function __construct($targetDirectory, SluggerInterface $slugger)
    {
      $this->targetDirectory = $targetDirectory;
      $this->slugger = $slugger;
    }

    public function upload(UploadedFile $file)
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = $this->slugger->slug($originalName);
        $name = $safeName.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $name);
        } catch (FileException $e) {
            // return "Unable to upload the chosen file";
        }

        return $name;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

}
