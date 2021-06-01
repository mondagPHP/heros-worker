<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\http;

use framework\exception\FileException;
use framework\file\FileUtils;

/**
 * Class UploadFile.
 */
class UploadFile extends \SplFileInfo
{
    /**
     * @var string
     */
    protected $uploadName;

    /**
     * @var string
     */
    protected $uploadMimeType;

    /**
     * @var int
     */
    protected $uploadErrorCode;
    
    private $size;

    /**
     * UploadFile constructor.
     * @param mixed $fileName
     * @param mixed $uploadName
     * @param mixed $uploadMimeType
     * @param mixed $uploadErrorCode
     */
    public function __construct($fileName, $uploadName, $uploadMimeType, $uploadErrorCode,$size)
    {
        $this->uploadName = $uploadName;
        $this->uploadMimeType = $uploadMimeType;
        $this->uploadErrorCode = $uploadErrorCode;
        $this->size = $size;
        parent::__construct($fileName);
    }

    public function getUploadName(): string
    {
        return $this->uploadName;
    }

    public function getUploadMineType(): string
    {
        return $this->uploadMimeType;
    }

    /**
     * @return array|string|string[]
     */
    public function getUploadExtension()
    {
        return pathinfo($this->uploadName, PATHINFO_EXTENSION);
    }

    public function getUploadErrorCode(): int
    {
        return $this->uploadErrorCode;
    }

    public function isValid(): bool
    {
        return UPLOAD_ERR_OK === $this->uploadErrorCode;
    }

    /**
     * @param $destination
     * @return \SplFileInfo
     * @throws \framework\exception\FileException
     */
    public function move($destination): \SplFileInfo
    {
        $path = pathinfo($destination, PATHINFO_DIRNAME);
        if (! is_dir($path)) {
            $b = FileUtils::makeFileDirs($path);
            if (false === $b) {
                throw new FileException(sprintf('Unable to create the "%s" directory', $path));
            }
        }
        if (! rename($this->getPathname(), $destination)) {
            throw new FileException(sprintf('Could not move the file "%s" to "%s"', $this->getPathname(), $destination));
        }
        @chmod($destination, 0666 & ~umask());
        return new \SplFileInfo($destination);
    }
    
    
    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }
}
