<?php
/**
 * This file is part of Heros-Worker.
 *
 * @contact  chenzf@pvc123.com
 */

namespace Framework\Http;

use Framework\File\SplFile;

class UploadFile extends SplFile
{
    /**
     * @var string
     */
    protected string $_uploadName;

    /**
     * @var string
     */
    protected string $_uploadMimeType;

    /**
     * @var int
     */
    protected int $_uploadErrorCode;

    /**
     * UploadFile constructor.
     *
     * @param  string  $fileName
     * @param  string  $uploadName
     * @param  string  $uploadMimeType
     * @param  int  $uploadErrorCode
     */
    public function __construct(string $fileName, string $uploadName, string $uploadMimeType, int $uploadErrorCode)
    {
        $this->_uploadName = $uploadName;
        $this->_uploadMimeType = $uploadMimeType;
        $this->_uploadErrorCode = $uploadErrorCode;
        parent::__construct($fileName);
    }

    /**
     * @return string
     */
    public function getUploadName(): string
    {
        return $this->_uploadName;
    }

    /**
     * @return string
     */
    public function getUploadMineType(): string
    {
        return $this->_uploadMimeType;
    }

    /**
     * @return mixed
     */
    public function getUploadExtension(): mixed
    {
        return pathinfo($this->_uploadName, PATHINFO_EXTENSION);
    }

    /**
     * @return int
     */
    public function getUploadErrorCode(): int
    {
        return $this->_uploadErrorCode;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->_uploadErrorCode === UPLOAD_ERR_OK;
    }
}
