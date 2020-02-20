<?php

/**
 * This file is part of the PHP-FFmpeg-video-streaming package.
 *
 * (c) Amin Yazdanpanah <contact@aminyazdanpanah.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace SyntaxEvolution\Streaming;


use SyntaxEvolution\Streaming\Exception\InvalidArgumentException;
use SyntaxEvolution\Streaming\Filters\Filter;
use SyntaxEvolution\Streaming\Filters\StreamToFileFilter;

class StreamToFile extends Export
{

    /**
     * @return string
     */
    protected function getPath(): string
    {
        return implode(".", [$this->getFilePath(), $this->path_info["extension"] ?? "mp4"]);
    }

    /**
     * @return Filter
     */
    protected function getFilter(): Filter
    {
        if ($this->uri) {
            throw new InvalidArgumentException("It is not possible to live this file");
        }

        return new StreamToFileFilter($this);
    }
}