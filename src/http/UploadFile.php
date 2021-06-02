<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\http;

/**
 * Class UploadFile.
 */
class UploadFile extends File
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
    public function __construct($fileName, $uploadName, $uploadMimeType, $uploadErrorCode, $size)
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
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }
}
