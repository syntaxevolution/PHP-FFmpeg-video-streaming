<?php

/**
 * This file is part of the PHP-FFmpeg-video-streaming package.
 *
 * (c) Amin Yazdanpanah <contact@aminyazdanpanah.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SyntaxEvolution\Streaming\Traits;

use SyntaxEvolution\Streaming\AutoRepresentations;
use SyntaxEvolution\Streaming\Exception\InvalidArgumentException;
use SyntaxEvolution\Streaming\Representation;

trait Representations
{
    /** @var array */
    protected $representations = [];

    /**
     * @return $this
     */
    public function addRepresentations()
    {
        $this->checkFormat();
        $reps = is_array(func_get_arg(0)) ? func_get_arg(0) : func_get_args();

        foreach ($reps as $rep) {
            if (!$rep instanceof Representation) {
                throw new InvalidArgumentException('Representations must be instance of Representation object');
            }
        }

        $this->representations = $reps;
        return $this;
    }

    /**
     * @return array
     */
    public function getRepresentations(): array
    {
        return $this->representations;
    }

    /**
     * @param array $side_values
     * @param array|null $k_bitrate_values
     * @return $this
     */
    public function autoGenerateRepresentations(array $side_values = null, array $k_bitrate_values = null)
    {
        $this->checkFormat();
        $this->addRepresentations((new AutoRepresentations($this->getMedia()->probe(), $side_values, $k_bitrate_values))->get());

        return $this;
    }

    /**
     * check whether format is set or nor
     */
    private function checkFormat()
    {
        if (!$this->format) {
            throw new InvalidArgumentException('First you must set the format of the video');
        }
    }
}