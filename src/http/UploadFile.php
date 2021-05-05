<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */

namespace framework\http;

use framework\exception\FileException;

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

    /**
     * UploadFile constructor.
     * @param mixed $fileName
     * @param mixed $uploadName
     * @param mixed $uploadMimeType
     * @param mixed $uploadErrorCode
     */
    public function __construct($fileName, $uploadName, $uploadMimeType, $uploadErrorCode)
    {
        $this->uploadName = $uploadName;
        $this->uploadMimeType = $uploadMimeType;
        $this->uploadErrorCode = $uploadErrorCode;
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
     * @return mixed
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
        if (!is_dir($path) && !mkdir($path, 0777, true)) {
            throw new FileException(sprintf('Unable to create the "%s" directory', $path));
        }
        if (!rename($this->getPathname(), $destination)) {
            throw new FileException(sprintf('Could not move the file "%s" to "%s"', $this->getPathname(), $destination));
        }
        restore_error_handler();
        @chmod($destination, 0666 & ~umask());
        return new \SplFileInfo($destination);
    }
}
