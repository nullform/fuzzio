<?php

namespace Nullform\Fuzzio;

class FuzzioString
{
    /**
     * @var string
     */
    protected $string;

    /**
     * @var float
     */
    protected $similarity;

    /**
     * @var int
     */
    protected $levenshteinDistance;

    /**
     * @param string $string
     * @param float $similarity
     * @param int $levenshteinDistance
     */
    public function __construct($string, $similarity, $levenshteinDistance)
    {
        $this->string = (string)$string;
        $this->similarity = (float)$similarity;
        $this->levenshteinDistance = (int)$levenshteinDistance;
    }

    /**
     * @return string
     */
    public function getString()
    {
        return $this->string;
    }

    /**
     * From 0 to 100.
     *
     * @return float
     */
    public function getSimilarity()
    {
        return $this->similarity;
    }

    /**
     * @return int
     */
    public function getLevenshteinDistance()
    {
        return $this->levenshteinDistance;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getString();
    }
}
