<?php

namespace Nullform\Fuzzio;

class FuzzioString
{
    /**
     * @var string
     */
    protected $string;

    /**
     * @var string
     */
    protected $normalizedString;

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
     * @param string $normalizedString
     */
    public function __construct($string, $similarity, $levenshteinDistance, $normalizedString)
    {
        $this->string = (string)$string;
        $this->similarity = (float)$similarity;
        $this->levenshteinDistance = (int)$levenshteinDistance;
        $this->normalizedString = (string)$normalizedString;
    }

    /**
     * Original string.
     *
     * @return string
     */
    public function getString()
    {
        return $this->string;
    }

    /**
     * Normalized string used to calculate similarity.
     *
     * @return string
     * @see Fuzzio::setNormalizer()
     */
    public function getNormalizedString()
    {
        return $this->normalizedString;
    }

    /**
     * From 0 to 100.
     *
     * @return float
     * @see \similar_text()
     */
    public function getSimilarity()
    {
        return $this->similarity;
    }

    /**
     * @return int
     * @see \levenshtein()
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
