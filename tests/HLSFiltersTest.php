<?php

/**
 * This file is part of the PHP-FFmpeg-video-streaming package.
 *
 * (c) Amin Yazdanpanah <contact@aminyazdanpanah.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\FFMpegStreaming;

use SyntaxEvolution\Streaming\Filters\Filter;
use SyntaxEvolution\Streaming\Filters\HLSFilter;
use SyntaxEvolution\Streaming\HLS;

class HLSFiltersTest extends TestCase
{
    public function testFilterClass()
    {
        $this->assertInstanceOf(Filter::class, $this->getFilter());
    }

    private function getFilter()
    {
        return new HLSFilter($this->getHLS());
    }

    private function getHLS()
    {
        $hls = new HLS($this->getVideo());

        return $hls->X264()
            ->autoGenerateRepresentations()
            ->setHlsAllowCache(false)
            ->setHlsTime(10);
    }
}