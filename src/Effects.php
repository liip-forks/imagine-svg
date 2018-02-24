<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2018 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\ImagineSvg;

use Imagine\Effects\EffectsInterface;
use Imagine\Exception\InvalidArgumentException;
use Imagine\Exception\RuntimeException;
use Imagine\Image\Palette\Color\ColorInterface;

class Effects implements EffectsInterface
{
    /**
     * @var string
     */
    const SVG_FILTER_ID_PREFIX = 'svgImagineFilterV1_';

    /**
     * @var \DOMDocument
     */
    private $document;

    /**
     * @param \DOMDocument $document
     */
    public function __construct(\DOMDocument $document)
    {
        $this->document = $document;
    }

    /**
     * {@inheritdoc}
     */
    public function gamma($correction)
    {
        throw new RuntimeException('This method is not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function negative()
    {
        throw new RuntimeException('This method is not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function grayscale()
    {
        throw new RuntimeException('This method is not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function colorize(ColorInterface $color)
    {
        throw new RuntimeException('This method is not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function sharpen()
    {
        throw new RuntimeException('This method is not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function blur($sigma)
    {
        $deviation = (float) $sigma;

        if ($deviation <= 0) {
            throw new InvalidArgumentException(sprintf(
                'Invalid sigma %s, must be a positive float or interger',
                var_export($sigma, true)
            ));
        }

        $this->getSvgFilter()->appendChild($this->createFilter('feGaussianBlur', [
            'stdDeviation' => json_encode($deviation),
        ]));

        return $this;
    }

    /**
     * Get the main filter element or create it if none is present.
     *
     * @return \DOMElement
     */
    private function getSvgFilter()
    {
        $svg = $this->document->documentElement;
        $filter = null;

        if (preg_match(
            '/^url\(#('.self::SVG_FILTER_ID_PREFIX.'[0-9a-f]{16})\)$/',
            (string) $svg->getAttribute('filter'),
            $matches
        )) {
            $id = $matches[1];
        } else {
            if ($svg->hasAttribute('filter')) {
                $this->wrapSvg();
            }
            $id = self::SVG_FILTER_ID_PREFIX.bin2hex(random_bytes(8));
            $svg->setAttribute('filter', 'url(#'.$id.')');
        }

        foreach ($this->document->getElementsByTagName('filter') as $element) {
            if ($element->getAttribute('id') === $id) {
                return $element;
            }
        }

        $filter = $this->document->createElement('filter');
        $filter->setAttribute('id', $id);
        $svg->insertBefore($filter, $svg->firstChild);

        return $filter;
    }

    /**
     * Add an element that wraps all contents and frees up the filter attribute.
     */
    private function wrapSvg()
    {
        $svg = $this->document->documentElement;
        $svgInner = $this->document->createElement('svg');

        $svgInner->setAttribute('filter', $svg->getAttribute('filter'));
        $svg->removeAttribute('filter');

        while ($svg->firstChild) {
            $svgInner->appendChild($svg->firstChild);
        }

        $svg->appendChild($svgInner);
    }

    /**
     * Create filter element with the specified attributes.
     *
     * @param string $name
     * @param array  $attributes
     *
     * @return \DOMElement
     */
    private function createFilter($name, array $attributes)
    {
        $filter = $this->document->createElement($name);

        foreach ($attributes as $key => $value) {
            $filter->setAttribute($key, $value);
        }

        return $filter;
    }
}
