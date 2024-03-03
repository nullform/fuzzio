<?php

namespace Nullform\Fuzzio\Test;

use Nullform\Fuzzio\Fuzzio;
use Nullform\Fuzzio\FuzzioString;
use PHPUnit\Framework\TestCase;

class FuzzioTest extends TestCase
{
    private $defaultNeedle = 'test';

    private $defaultHaystack = ['test', 'tested', 'testing', 'тест', 'test1', 'тесты', 'hello', 'world'];

    public function testGetNeedle()
    {
        $fuzzio = new Fuzzio($this->defaultNeedle);

        $this->assertEquals($this->defaultNeedle, $fuzzio->getNeedle());
    }

    public function testGetNormalizedNeedle()
    {
        $fuzzio = new Fuzzio('Test');
        $fuzzio->setNormalizer(function ($string) {
            return strtolower($string);
        });

        $this->assertEquals('test', $fuzzio->getNormalizedNeedle());
    }

    public function testGetHaystack()
    {
        $fuzzio = new Fuzzio($this->defaultNeedle, $this->defaultHaystack);

        $this->assertEquals($this->defaultHaystack, $fuzzio->getHaystack());
    }

    public function testGetNormalizedHaystack()
    {
        $needle = 'TEST';
        $haystack = ['Test1', 'Test2'];

        $fuzzio = new Fuzzio($needle, $haystack);
        $fuzzio->setNormalizer(function ($string) {
            return strtolower($string);
        });

        $this->assertEquals(['test1', 'test2'], $fuzzio->getNormalizedHaystack());
    }

    public function testSetNormalizer()
    {
        $fuzzio = new Fuzzio('Test', ['TEST1', 'TEST123']);
        $normalizer = function ($string) {
            return strtolower($string);
        };
        $fuzzio->setNormalizer($normalizer);

        $fuzzioNorm = new Fuzzio('Test', ['TEST1', 'TEST123'], $normalizer);

        $this->assertTrue($fuzzio->getNormalizedNeedle() === 'test');
        $this->assertTrue($fuzzio->getClosestOne()->getNormalizedString() === 'test1');
        $this->assertTrue($fuzzio->getClosestOne()->getString() === 'TEST1');

        $this->assertTrue($fuzzioNorm->getNormalizedNeedle() === 'test');
        $this->assertTrue($fuzzioNorm->getClosestOne()->getNormalizedString() === 'test1');
        $this->assertTrue($fuzzioNorm->getClosestOne()->getString() === 'TEST1');

        $fuzzioNorm->setNormalizer(null);

        $this->assertTrue($fuzzioNorm->getNormalizedNeedle() === $fuzzioNorm->getNeedle());
        $this->assertTrue($fuzzioNorm->getClosestOne()->getString() === $fuzzioNorm->getClosestOne()->getNormalizedString());
    }

    public function testSetHaystack()
    {
        $fuzzio = new Fuzzio($this->defaultNeedle);

        $fuzzio->setHaystack($this->defaultHaystack);

        $this->assertNotEmpty($fuzzio->get());

        return $fuzzio;
    }

    /**
     * @param Fuzzio $fuzzio
     * @depends testSetHaystack
     */
    public function testAddToHaystack($fuzzio)
    {
        $fuzzio->addToHaystack(['one', 'two']);

        $this->assertCount(\count($this->defaultHaystack) + 2, $fuzzio->get());
    }

    public function testRemoveFromHaystack()
    {
        $needle = 'test';
        $haystack = ['test1', 'test12', 'test123'];

        $fuzzio = new Fuzzio($needle, $haystack);
        $fuzzio->removeFromHaystack([$haystack[0], $haystack[1]]);

        $this->assertCount(
            \count($haystack) - 2,
            $fuzzio->get(),
            'Prev haystack count: ' . \count($haystack) . '. New haystack count: ' . \count($fuzzio->getHaystack())
        );
    }

    public function testHasExactMatch()
    {
        $fuzzio = new Fuzzio('test', ['test', 'test2', 'test3']);

        $this->assertTrue($fuzzio->hasExactMatch());
    }

    public function testGet()
    {
        $fuzzio = new Fuzzio('test', ['test', 'test1', 'test2', 'test12', 'test123']);

        $this->assertCount(5, $fuzzio->get());
        $this->assertCount(4, $fuzzio->get(80));
        $this->assertCount(3, $fuzzio->get(null, 1));
    }

    public function testGetAll()
    {
        $fuzzio = new Fuzzio($this->defaultNeedle, $this->defaultHaystack);

        $this->assertCount(\count($this->defaultHaystack), $fuzzio->get());
    }

    public function testGetClosest()
    {
        $fuzzio = new Fuzzio('тест', ['тестовый', 'тестовая', 'тесты']);
        $closest = $fuzzio->getClosest();
        $closestOne = \current($closest);

        $this->assertCount(
            1,
            $closest,
            'Most similar count: ' . \count($closest) . '. Max similarity: ' . $fuzzio->getMaxSimilarity()
        );

        $this->assertTrue(\is_array($closest));
        $this->assertCount(1, $closest);
        $this->assertNotEmpty($closestOne);
        $this->assertInstanceOf(FuzzioString::class, $closestOne);
        $this->assertEquals('тесты', $closestOne->getString());

        return $fuzzio;
    }

    /**
     * @param Fuzzio $fuzzio
     * @depends testGetClosest
     */
    public function testGetClosestOne($fuzzio)
    {
        $mostSimilar = $fuzzio->getClosest();
        $mostSimilarString = \current($mostSimilar);
        $first = $fuzzio->getClosestOne();

        $this->assertEquals($first->getString(), $mostSimilarString->getString());
    }

    public function testGetMaxLevenshteinDistanceThreshold()
    {
        $fuzzio = new Fuzzio($this->defaultNeedle, $this->defaultHaystack);

        $this->assertNull($fuzzio->getMaxLevenshteinDistanceThreshold());
    }

    public function testSetMaxLevenshteinDistanceThreshold()
    {
        $fuzzio = new Fuzzio($this->defaultNeedle, $this->defaultHaystack);
        $fuzzio->setMaxLevenshteinDistanceThreshold(2);

        $this->assertEquals(2, $fuzzio->getMaxLevenshteinDistanceThreshold());
    }

    public function testGetMinSimilarityThreshold()
    {
        $fuzzio = new Fuzzio($this->defaultNeedle, $this->defaultHaystack);

        $this->assertNull($fuzzio->getMinSimilarityThreshold());
    }

    public function testSetMinSimilarityThreshold()
    {
        $fuzzio = new Fuzzio($this->defaultNeedle, $this->defaultHaystack);
        $fuzzio->setMinSimilarityThreshold(45.5);

        $this->assertEquals(45.5, $fuzzio->getMinSimilarityThreshold());
    }

    public function testGetMaxSimilarity()
    {
        $fuzzio = new Fuzzio('test', ['test', 'test1', 'test12']);

        $this->assertTrue($fuzzio->getMaxSimilarity() == 100.0, 'Max similarity: ' . $fuzzio->getMaxSimilarity());
    }

    public function testGetMinLevenshteinDistance()
    {
        $fuzzio = new Fuzzio('test', ['test1', 'test123', 'test1234']);

        $this->assertTrue(
            $fuzzio->getMinLevenshteinDistance() == 1,
            'Min distance: ' . $fuzzio->getMinLevenshteinDistance()
        );
    }

    public function testCommon()
    {
        $needle = 'John';
        $haystack = ['John ', 'Jon', 'Johns', 'JANE', 'Janie'];
        $needleMb = 'иванв';
        $haystackMb = ['иванов', 'ивановы', 'ивановой', 'Иванов', 'иван', 'вано', 'ваня'];

        $fuzzio = new Fuzzio($needle);
        $fuzzio->setNormalizer(function ($string) {
            return trim(strtolower($string));
        });
        $fuzzio->setHaystack($haystack);
        $fuzzioMb = new Fuzzio($needleMb, $haystackMb);

        $mostSimilar = $fuzzio->getClosestOne();
        $mostSimilarMb = $fuzzioMb->getClosestOne();

        echo "\n\nStart common test...\n\n";

        $this->dumpFuzzioToConsole($fuzzio);

        echo "\n";

        $this->dumpFuzzioToConsole($fuzzioMb);

        $this->assertTrue($fuzzio->hasExactMatch());
        $this->assertFalse($fuzzioMb->hasExactMatch());
        $this->assertEquals('John ', $mostSimilar->getString());
        $this->assertEquals('иванов', $mostSimilarMb->getString());
        $this->assertCount(2, $fuzzioMb->get(80, 1));

        $fuzzioMb->setMinSimilarityThreshold(80);

        $this->dumpFuzzioToConsole($fuzzioMb, "After setMinSimilarityThreshold(80)");

        $this->assertCount(3, $fuzzioMb->get());

        $fuzzioMb->setMaxLevenshteinDistanceThreshold(1);

        $this->dumpFuzzioToConsole($fuzzioMb, "After setMaxLevenshteinDistanceThreshold(1)");

        $this->assertCount(2, $fuzzioMb->get());

        $fuzzioMb->setMinSimilarityThreshold(0);
        $fuzzioMb->setMaxLevenshteinDistanceThreshold(0);

        $this->assertCount(\count($haystackMb), $fuzzioMb->get());

    }

    /**
     * @param Fuzzio $fuzzio
     * @param string $title
     */
    private function dumpFuzzioToConsole($fuzzio, $title = '')
    {
        echo "\n";
        echo "Needle: {$fuzzio->getNeedle()}" . ($title ? ". $title:" : ":") . "\n";
        foreach ($fuzzio->get() as $string) {
            echo "- " . $string->getString();
            echo " (similarity: {$string->getSimilarity()}%, distance: {$string->getLevenshteinDistance()}) \n";
        }
    }
}
