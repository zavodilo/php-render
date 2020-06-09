<?php 

namespace PHPR;

use PHPR\Buffer\Buffer2D;
use PHPR\Math\IVec2;

class Rasterizer
{
    /**
     * Canvas size
     */
    private int $width;
    private int $height;

    /**
     * Constructor
     */
    public function __construct(int $width, int $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public function screenSpaceToBufferSpace()
    {}

    /** 
     * Raster a single line in screen cords
     *
     * @param float           $x1
     * @param float           $y1
     * @param float           $x2
     * @param float           $y2
     * @param array         $pixels 
     */
    public function rasterLineScreenSpace(float $x1, float $y1, float $x2, float $y2, ?array &$pixels, bool $tailless = false)
    {
        $x1 = ($x1 + 1) / 2;
        $x2 = ($x2 + 1) / 2;
        $y1 = ($y1 + 1) / 2;
        $y2 = ($y2 + 1) / 2;

        $this->rasterLine(
            ($x1 * $this->width),
            ($y1 * $this->height),
            ($x2 * $this->width),
            ($y2 * $this->height),
            $pixels,
            $tailless
        );
    }

    /** 
     * Raster a single line in buffer cords
     *
     * @param int           $x1
     * @param int           $y1
     * @param int           $x2
     * @param int           $y2
     * @param array         $pixels 
     */
    public function rasterLine(int $x1, int $y1, int $x2, int $y2, ?array &$pixels, bool $tailless = false)
    {
        // Bresenham Algorithm
        // --

        $dx = abs($x2 - $x1);
        $dy = -abs($y2 - $y1);

        $sx = $x1 < $x2 ? 1 : -1;
        $sy = $y1 < $y2 ? 1 : -1;

        $e = $dx + $dy;

        while (1) 
        {
            if ($tailless && ($x1 === $x2 && $y1 === $y2)) break;

            if ($x1 >= $this->width) break;
            if ($y1 >= $this->height) break;

            $pixels[] = $x1;
            $pixels[] = $y1;

            $e2 = $e * 2;

            if ($e2 >= $dy) {
                if ($x1 === $x2) break;
                $e += $dy;
                $x1 += $sx;
            }
            if ($e2 <= $dx) {
                if ($y1 === $y2) break;
                $e += $dx;
                $y1 += $sy;
            }
        }
    }

    /** 
     * Raster a triangle in buffer cords
     *
     * @param float           $x1
     * @param float           $y1
     * @param float           $x2
     * @param float           $y2
     * @param float           $x3
     * @param float           $y3
     * @param array         $pixels 
     */
    public function rasterTriangleScreenSpace(float $x1, float $y1, float $x2, float $y2, float $x3, float $y3, ?array &$pixels)
    {
        $x1 = ($x1 + 1) / 2;
        $x2 = ($x2 + 1) / 2;
        $x3 = ($x3 + 1) / 2;
        $y1 = ($y1 + 1) / 2;
        $y2 = ($y2 + 1) / 2;
        $y3 = ($y3 + 1) / 2;

        $this->rasterTriangle(
            ($x1 * $this->width),
            ($y1 * $this->height),
            ($x2 * $this->width),
            ($y2 * $this->height),
            ($x3 * $this->width),
            ($y3 * $this->height),
            $pixels
        );
    }

    /** 
     * Raster a triangle in buffer cords
     *
     * @param int           $x1
     * @param int           $y1
     * @param int           $x2
     * @param int           $y2
     * @param int           $x3
     * @param int           $y3
     * @param array         $pixels 
     */
    public function rasterTriangle(int $x1, int $y1, int $x2, int $y2, int $x3, int $y3, ?array &$pixels)
    {
        $tripixels = [];

        $this->rasterLine($x1, $y1, $x2, $y2, $tripixels, true);
        $this->rasterLine($x2, $y2, $x3, $y3, $tripixels, true);
        $this->rasterLine($x3, $y3, $x1, $y1, $tripixels, true);

        $miny = min($y1, $y2, $y3);
        $maxy = max($y1, $y2, $y3);

        // we know the y range from the lines
        $scanlineMax = $scanlineMin = array_fill_keys(range($miny, $maxy), null);

        // find min max x values for each line
        for($i = 0; $i < count($tripixels); $i+=2) 
        {
            $x = $tripixels[$i+0];
            $y = $tripixels[$i+1];

            $currMin = &$scanlineMin[$y];
            $currMax = &$scanlineMax[$y];

            if (is_null($currMin) || $x < $currMin) {
                $currMin = $x;
            }

            if (is_null($currMax) || $x > $currMax) {
                $currMax = $x;
            }
        }

        // now reduce the edge
        foreach($scanlineMin as $y => $min) 
        {
            $max = $scanlineMax[$y];

            // skip if they are the same meaning we 
            // are already covered by the line itslef
            if ($max === $min) continue;

            // reduce one of each side
            $min++;
            $max--;

            // add the filling
            for($i=$min; $i<$max; $i++) {
                $tripixels[] = $i;
                $tripixels[] = $y;
            }
        }

        // append the pixels
        array_push($pixels, ...$tripixels);
    }

    /**
     * Returns the vertex contribution for a pixel cords array
     * I don't konw the correct term for this process, but its target
     * is to calculate how near a pixel is to each vertex to allow interpolaration later
     *
     * @param int           $x1
     * @param int           $y1
     * @param int           $x2
     * @param int           $y2
     * @param int           $x3
     * @param int           $y3
     * @param array                 $pixels
     * @return array
     */
    public function getVertexContributionForPixels(int $x1, int $y1, int $x2, int $y2, int $x3, int $y3, array &$pixels) : array
    {   
        $contribution = [];

        $minx = min($x1, $x2, $x3);
        $maxx = max($x1, $x2, $x3);
        $miny = min($y1, $y2, $y3);
        $maxy = max($y1, $y2, $y3);

        var_dump($minx, $maxx, $miny, $maxy); die;

        $x1 = ($x1 - $minx) / ($maxx - $minx);
        $x2 = ($x2 - $minx) / ($maxx - $minx);
        $x3 = ($x3 - $minx) / ($maxx - $minx);

        $y1 = ($y1 - $miny) / ($maxy - $miny);
        $y2 = ($y2 - $miny) / ($maxy - $miny);
        $y3 = ($y3 - $miny) / ($maxy - $miny);
 
        for($i = 0; $i < count($pixels); $i+=2) 
        {
            $x = $pixels[$i+0];
            $y = $pixels[$i+1];

            // v1

            $contribution[] = $x1;
        }
    }
}