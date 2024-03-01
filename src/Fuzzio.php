<?php

namespace Nullform\Fuzzio;

class Fuzzio
{
    /**
     * @var string
     */
    protected $needle;

    /**
     * @var string[]
     */
    protected $haystack = [];

    /**
     * @var FuzzioString[]
     */
    protected $result = [];

    /**
     * @var float[]
     */
    protected $similarity = [];

    /**
     * @var int[]
     */
    protected $distance = [];

    /**
     * @var null|int
     */
    protected $maxLevenshteinDistanceThreshold = null;

    /**
     * @var null|float
     */
    protected $minSimilarityThreshold = null;

    /**
     * @var array
     */
    protected $utf8ToExtendedAsciiMap = [];

    /**
     * @param string $needle Reference string.
     * @param string[]|null $haystack Strings for calculate similarity.
     */
    public function __construct($needle, $haystack = null)
    {
        $this->needle = (string)$needle;

        if (\is_array($haystack) && !empty($haystack)) {
            $this->setHaystack($haystack);
        }
    }

    /**
     * @return string
     */
    public function getNeedle()
    {
        return $this->needle;
    }

    /**
     * @param string[]|null $haystack Strings for calculate similarity.
     * @return $this
     */
    public function setHaystack($haystack)
    {
        $this->dropHaystack();

        $this->haystack = $this->toArrayOfStrings($haystack);

        return $this->calculate();

    }

    /**
     * @return string[]
     */
    public function getHaystack()
    {
        return $this->haystack;
    }

    /**
     * @param string[] $strings
     * @return $this
     */
    public function addToHaystack($strings)
    {
        $strings = $this->toArrayOfStrings($strings);
        $this->haystack = \array_merge($this->haystack, $strings);
        $this->haystack = \array_unique($this->haystack);

        return $this->calculate();
    }

    /**
     * @param string[] $strings
     * @return $this
     */
    public function removeFromHaystack($strings)
    {
        $strings = $this->toArrayOfStrings($strings);
        $prevHaystack = $this->getHaystack();

        $this->dropHaystack();

        if ($strings) {
            $this->haystack = \array_filter($prevHaystack, function ($string) use ($strings) {
                return !\in_array($string, $strings);
            });
        }

        return $this->calculate();
    }

    /**
     * @return bool
     */
    public function hasExactMatch()
    {
        return \in_array($this->needle, $this->haystack);
    }

    /**
     * @param float|null $minSimilarity
     * @param int|null $maxLevenshteinDistance
     * @return FuzzioString[]
     */
    public function get($minSimilarity = null, $maxLevenshteinDistance = null)
    {
        return \array_filter($this->result, function ($string) use ($minSimilarity, $maxLevenshteinDistance) {
            $valid = true;
            if (!\is_null($minSimilarity)) {
                $minSimilarity = (float)$minSimilarity;
                if ($string->getSimilarity() < $minSimilarity) {
                    $valid = false;
                }
            }
            if (!\is_null($maxLevenshteinDistance)) {
                $maxLevenshteinDistance = (int)$maxLevenshteinDistance;
                if ($string->getLevenshteinDistance() && $string->getLevenshteinDistance() > $maxLevenshteinDistance) {
                    $valid = false;
                }
            }
            return $valid;
        });
    }

    /**
     * @return FuzzioString[]
     */
    public function getClosest()
    {
        $result = [];

        if ($this->similarity) {
            $result = \array_filter($this->result, function ($item) {
                return $item->getSimilarity() == $this->getMaxSimilarity();
            });
        }

        return $result;
    }

    /**
     * @return FuzzioString|null
     */
    public function getClosestOne()
    {
        $firstOfMostSimilar = null;

        $mostSimilar = $this->getClosest();
        if ($mostSimilar) {
            $firstOfMostSimilar = $mostSimilar[0];
        }

        return $firstOfMostSimilar;
    }

    /**
     * @return int|null
     */
    public function getMaxLevenshteinDistanceThreshold()
    {
        return $this->maxLevenshteinDistanceThreshold;
    }

    /**
     * @param int $threshold
     * @return $this
     */
    public function setMaxLevenshteinDistanceThreshold($threshold)
    {
        $this->maxLevenshteinDistanceThreshold = (int)$threshold ?: null;

        return $this->calculate();
    }

    /**
     * @return float|null
     */
    public function getMinSimilarityThreshold()
    {
        return $this->minSimilarityThreshold;
    }

    /**
     * @param float $threshold
     * @return $this
     */
    public function setMinSimilarityThreshold($threshold)
    {
        $this->minSimilarityThreshold = (float)$threshold ?: null;

        return $this->calculate();
    }

    /**
     * @return float|null
     */
    public function getMaxSimilarity()
    {
        return $this->similarity ? \max($this->similarity) : null;
    }

    /**
     * @return int|null
     */
    public function getMinLevenshteinDistance()
    {
        return $this->distance ? \min($this->distance) : null;
    }

    /**
     * @return $this
     */
    protected function calculate()
    {
        $this->result = [];

        foreach ($this->haystack as $string) {
            if (isset($this->similarity[$string]) && isset($this->distance[$string])) {
                $similarityPercent = $this->similarity[$string];
                $distance = $this->distance[$string];
            } else {
                $safeNeedle = $this->utf8ToExtendedAscii($this->needle);
                $safeString = $this->utf8ToExtendedAscii($string);
                \similar_text($safeNeedle, $safeString, $similarityPercent);
                $distance = \levenshtein($safeNeedle, $safeString);
                $this->similarity[$string] = $similarityPercent;
                $this->distance[$string] = $distance;
            }
            $this->result[] = new FuzzioString($string, $similarityPercent, $distance);
        }

        if ($this->getMinSimilarityThreshold()) {
            $this->result = \array_filter($this->result, function ($item) {
                return $item->getSimilarity() >= $this->getMinSimilarityThreshold();
            });
        }
        if ($this->getMaxLevenshteinDistanceThreshold()) {
            $this->result = \array_filter($this->result, function ($item) {
                return $item->getLevenshteinDistance() <= $this->getMaxLevenshteinDistanceThreshold();
            });
        }

        \usort($this->result, function ($a, $b) {
            /** @var FuzzioString $a */
            /** @var FuzzioString $b */
            if ($a->getSimilarity() == $b->getSimilarity()) {
                return 0;
            }
            return $a->getSimilarity() > $b->getSimilarity() ? -1 : 1;
        });

        return $this;
    }

    /**
     * @param array $array
     * @return string[]
     */
    protected function toArrayOfStrings($array)
    {
        $array = (array)$array;

        return \array_unique(
            \array_map(function ($item) {
                return (string)$item;
            }, $array)
        );
    }

    /**
     * @return void
     */
    protected function dropHaystack()
    {
        $this->haystack = $this->similarity = $this->distance = $this->result = [];
    }

    /**
     * @param string $string
     * @return string
     * @see https://www.php.net/manual/en/function.levenshtein.php#113702
     */
    protected function utf8ToExtendedAscii($string)
    {

        // Find all multibyte characters (cf. UTF-8 encoding specs).
        $matches = [];
        if (!\preg_match_all('/[\xC0-\xF7][\x80-\xBF]+/', $string, $matches)) {
            return $string;
        } // Plain ASCII string.

        // Update the encoding map with the characters not already met.
        foreach ($matches[0] as $mbc) {
            if (!isset($this->utf8ToExtendedAsciiMap[$mbc])) {
                $this->utf8ToExtendedAsciiMap[$mbc] = \chr(128 + \count($this->utf8ToExtendedAsciiMap));
            }
        }

        // Finally remap non-ASCII characters.
        return \strtr($string, $this->utf8ToExtendedAsciiMap);
    }
}
