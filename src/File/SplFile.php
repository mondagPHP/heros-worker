<?php
/**
 * This file is part of Heros-Worker.
 *
 * @contact  chenzf@pvc123.com
 */

namespace Framework\File;

use Framework\Exception\FileException;

class SplFile extends \SplFileInfo
{
    /**
     * @param $destination
     * @return SplFile
     */
    public function move($destination): self
    {
        set_error_handler(function ($type, $msg) use (&$error) {
            $error = $msg;
        });
        $path = pathinfo($destination, PATHINFO_DIRNAME);
        if (! is_dir($path) && ! mkdir($path, 0777, true)) {
            restore_error_handler();
            throw new FileException(sprintf('Unable to create the "%s" directory (%s)', $path, strip_tags($error)));
        }
        if (! rename($this->getPathname(), $destination)) {
            restore_error_handler();
            throw new FileException(sprintf('Could not move the file "%s" to "%s" (%s)', $this->getPathname(), $destination, strip_tags($error)));
        }
        restore_error_handler();
        @chmod($destination, 0666 & ~umask());

        return new self($destination);
    }
}
