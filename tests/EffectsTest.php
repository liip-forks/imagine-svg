<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2018 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\ImagineSvg\Tests;

use Contao\ImagineSvg\Effects;
use Contao\ImagineSvg\Imagine;
use Contao\ImagineSvg\UndefinedBox;
use Imagine\Exception\InvalidArgumentException;
use Imagine\Exception\RuntimeException;
use Imagine\Image\Palette\Color\ColorInterface;
use PHPUnit\Framework\TestCase;

class EffectsTest extends TestCase
{
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\ImagineSvg\Effects', new Effects(new \DOMDocument()));
    }

    public function testGamma()
    {
        $effects = new Effects(new \DOMDocument());

        $this->expectException(RuntimeException::class);

        $effects->gamma(1);
    }

    public function testNegative()
    {
        $effects = new Effects(new \DOMDocument());

        $this->expectException(RuntimeException::class);

        $effects->negative();
    }

    public function testGrayscale()
    {
        $effects = new Effects(new \DOMDocument());

        $this->expectException(RuntimeException::class);

        $effects->grayscale();
    }

    public function testColorize()
    {
        $effects = new Effects(new \DOMDocument());

        $this->expectException(RuntimeException::class);

        $effects->colorize($this->createMock(ColorInterface::class));
    }

    public function testSharpen()
    {
        $effects = new Effects(new \DOMDocument());

        $this->expectException(RuntimeException::class);

        $effects->sharpen();
    }

    public function testBlur()
    {
        $dom = (new Imagine())->create(new UndefinedBox())->getDomDocument();
        $effects = new Effects($dom);

        $this->assertSame($effects, $effects->blur(1.5));
        $this->assertTrue($dom->documentElement->hasAttribute('filter'));

        $filter = $dom->getElementsByTagName('filter')[0];
        $filterId = explode(')', explode('#', $dom->documentElement->getAttribute('filter'))[1])[0];

        $this->assertSame($filterId, $filter->getAttribute('id'));
        $this->assertSame('feGaussianBlur', $filter->firstChild->nodeName);
        $this->assertSame('1.5', $filter->firstChild->getAttribute('stdDeviation'));

        $effects->blur(10);

        $this->assertSame('url(#'.$filterId.')', $dom->documentElement->getAttribute('filter'));
        $this->assertSame(2, $filter->childNodes->length);
        $this->assertSame('feGaussianBlur', $filter->lastChild->nodeName);
        $this->assertSame('10', $filter->lastChild->getAttribute('stdDeviation'));

        $this->expectException(InvalidArgumentException::class);

        $effects->blur(0);
    }
}
