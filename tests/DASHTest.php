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

use SyntaxEvolution\Streaming\DASH;
use SyntaxEvolution\Streaming\Export;
use SyntaxEvolution\Streaming\Format\Video;
use SyntaxEvolution\Streaming\Representation;
use ReflectionClass;

class DASHTest extends TestCase
{
    public function testDASHClass()
    {
        $this->assertInstanceOf(Export::class, $this->getDASH());
    }

    public function testFormat()
    {
        $dash = $this->getDASH();
        $dash->HEVC();

        $this->assertInstanceOf(Video::class, $dash->getFormat());
    }

    public function testAutoRepresentations()
    {
        $dash = $this->getDASH();
        $dash->HEVC()
            ->autoGenerateRepresentations();
        $representations = $dash->getRepresentations();

        $this->assertIsArray($representations);
        $this->assertInstanceOf(Representation::class, current($representations));

        $this->assertEquals('256x144', $representations[0]->getResize());
        $this->assertEquals('426x240', $representations[1]->getResize());
        $this->assertEquals('640x360', $representations[2]->getResize());

        $this->assertEquals(103, $representations[0]->getKiloBitrate());
        $this->assertEquals(138, $representations[1]->getKiloBitrate());
        $this->assertEquals(207, $representations[2]->getKiloBitrate());
    }

    public function testSet()
    {
        $dash = $this->getDASH();
        $dash->setAdaption('test-adaption');

        $this->assertEquals('test-adaption', $dash->getAdaption());
    }

    public function testSave()
    {
        $dash = $this->getDASH();
        $streaming_analytics = $dash->HEVC()
            ->autoGenerateRepresentations()
            ->save($this->srcDir . '/dash/test.mpd');

        $get_path_info = $dash->getPathInfo();

        $this->assertFileExists($this->srcDir . '/dash/test.mpd');
        $this->assertIsArray($get_path_info);
        $this->assertArrayHasKey('dirname', $get_path_info);
        $this->assertArrayHasKey('filename', $get_path_info);
        $this->assertArrayHasKey('metadata', $streaming_analytics);
        $metadata = $streaming_analytics['metadata'];
        $this->assertArrayHasKey('video', $metadata);
        $this->assertArrayHasKey('streams', $metadata);
    }

    private function getDASH()
    {
        return new DASH($this->getVideo());
    }
}
