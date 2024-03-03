# Fuzzio

The PHP package for calculate similarity and Levenshtein distance between strings. Realizes fuzzy search using similar_text() and levenshtein() functions. Easy to use and safe for multibyte encodings (UTF-8).

## Requirements

- PHP >= 5.6

## Installation

```shell
composer require nullform/fuzzio
```

## Usage examples

```php
use \Nullform\Fuzzio\Fuzzio;
use \Nullform\Fuzzio\FuzzioString;

$needle = 'john'; // Reference string
$haystack = ['jon', 'johns', 'jane', 'janie']; // Array of strings

$fuzzio = new Fuzzio($needle, $haystack);

// Get all strings from $haystack with calculated similarity and Levenshtein distance
$all = $fuzzio->get(); // FuzzioString[]

// Get strings with similarity >= 80% and Levenshtein distance <= 1
$filtered = $fuzzio->get(80, 1); // FuzzioString[]

// Get strings with Levenshtein distance <= 1
$filtered = $fuzzio->get(null, 1); // FuzzioString[]

// With max similarity value
$allClosest = $fuzzio->getClosest(); // FuzzioString[]

// One (first) with max similarity value
$closestOne = $fuzzio->getClosestOne(); // FuzzioString

echo $closestOne; // johns
echo $closestOne->getString(); // johns
echo $closestOne->getSimilarity(); // 88.888888888889
echo $closestOne->getLevenshteinDistance(); // 1

echo $fuzzio->getMaxSimilarity(); // 88.888888888889

// Is there an exact match in the collection
$fuzzio->hasExactMatch(); // false

// Add string to haystack
$fuzzio->addToHaystack(['julie']);

// Remove strings from haystack
$fuzzio->removeFromHaystack(['janie', 'julie']);
```

You can set the similarity threshold and the Levenshtein distance threshold for filtering results:

```php
use \Nullform\Fuzzio\Fuzzio;

$needle = 'john'; // Reference string
$haystack = ['jon', 'johns', 'jane', 'janie']; // Array of strings

$fuzzio = new Fuzzio($needle);

// Set max Levenshtein distance threshold
$fuzzio->setMaxLevenshteinDistanceThreshold(1);

// Set min similarity threshold
$fuzzio->setMinSimilarityThreshold(80);

// Set array of strings to calculate similarity
$fuzzio->setHaystack($haystack);

// Get strings with similarity >= 80% and Levenshtein distance <= 1
$collection = $fuzzio->get();
```

You can set a normalizer for more predictable calculations:

```php
use \Nullform\Fuzzio\Fuzzio;

$needle = 'John'; // Reference string
$haystack = ['Jon ', 'Johns', 'JANE', 'Janie']; // Array of strings

$normalizer = function ($string) {
    return trim(strtolower($string));
};

$fuzzio = new Fuzzio($needle);

// Normalizer for $needle and $haystack
$fuzzio->setNormalizer($normalizer);
// Set array of strings to calculate similarity
$fuzzio->setHaystack($haystack);

// Or like this
$fuzzio = new Fuzzio($needle, $haystack, $normalizer);

echo $fuzzio->getNeedle(); // John
echo $fuzzio->getNormalizedNeedle(); // john

// One with max similarity
$closest = $fuzzio->getClosestOne();

echo $closest; // Johns
echo $closest->getString(); // Johns
echo $closest->getNormalizedString(); // johns
echo $closest->getSimilarity(); // 88.888888888889
echo $closest->getLevenshteinDistance(); // 1
```

## Methods

### Fuzzio

- Fuzzio::**__construct**(*string* $needle, *string[]|null* $haystack = *null*, *callable|null* $normalizer = null)
- Fuzzio::**getNeedle**(): *string*
- Fuzzio::**getNormalizedNeedle**(): *string*
- Fuzzio::**setHaystack**(*string[]* $haystack): *Fuzzio*
- Fuzzio::**getHaystack**(): *string[]*
- Fuzzio::**getNormalizedHaystack**(): *string[]*
- Fuzzio::**addToHaystack**(*string[]* $strings): *Fuzzio*
- Fuzzio::**removeFromHaystack**(*string[]* $strings): *Fuzzio*
- Fuzzio::**hasExactMatch()**: *bool*
- Fuzzio::**get**(*float|null* $minSimilarity = *null*, *int|null* $maxLevenshteinDistance = *null*): *FuzzioString[]*
- Fuzzio::**getClosest**(): *FuzzioString[]*
- Fuzzio::**getClosestOne**(): *FuzzioString*
- Fuzzio::**getMaxLevenshteinDistanceThreshold**(): *int|null*
- Fuzzio::**setMaxLevenshteinDistanceThreshold**(*int* $threshold): *Fuzzio*
- Fuzzio::**getMinSimilarityThreshold**(): *float|null*
- Fuzzio::**setMinSimilarityThreshold**(*float* $threshold): *Fuzzio*
- Fuzzio::**getMaxSimilarity**(): *float|null*
- Fuzzio::**getMinLevenshteinDistance**(): *int|null*
- Fuzzio::**setNormalizer**(*callable|null* $normalizer): *Fuzzio*

### FuzzioString

- FuzzioString::**getString**(): *string*
- FuzzioString::**getNormalizedString**(): *string*
- FuzzioString::**getSimilarity**(): *float*
- FuzzioString::**getLevenshteinDistance**(): *int*
- FuzzioString::**__toString**(): *string*
