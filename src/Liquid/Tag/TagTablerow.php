<?php

/**
 * This file is part of the Liquid package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Liquid
 */

namespace Liquid\Tag;

use Illuminate\Filesystem\Filesystem;
use Illuminate\View\ViewFinderInterface;
use Liquid\AbstractBlock;
use Liquid\Context;
use Liquid\LiquidEngine;
use Liquid\LiquidException;
use Liquid\Regexp;

/**
 * Quickly create a table from a collection
 */
class TagTablerow extends AbstractBlock
{
    /**
     * The variable name of the table tag
     *
     * @var string
     */
    public $variableName;

    /**
     * The collection name of the table tags
     *
     * @var string
     */
    public $collectionName;

    /**
     * Additional attributes
     *
     * @var array
     */
    public $attributes;

    /**
     * Constructor
     *
     * @param string $markup
     * @param array $tokens
     * @param ViewFinderInterface $viewFinder
     *
     * @param Filesystem|null $files
     * @param null $compiled
     * @throws LiquidException
     */
    public function __construct($markup, array &$tokens, ViewFinderInterface $viewFinder = null, Filesystem $files = null, $compiled = null)
    {
        parent::__construct($markup, $tokens, $viewFinder, $files, $compiled);

        $syntax = new Regexp('/(\w+)\s+in\s+(' . LiquidEngine::VARIABLE_NAME . ')/');

        if ($syntax->match($markup)) {
            $this->variableName = $syntax->matches[1];
            $this->collectionName = $syntax->matches[2];

            $this->extractAttributes($markup);
        } else {
            throw new LiquidException("Syntax Error in 'table_row loop' - Valid syntax: table_row [item] in [collection] cols=3");
        }
    }

    /**
     * Renders the current node
     *
     * @param Context $context
     *
     * @return string
     * @throws LiquidException
     */
    public function render(Context $context)
    {
        $collection = $context->get($this->collectionName);

        if ($collection instanceof \Traversable) {
            $collection = iterator_to_array($collection);
        }

        if (!is_array($collection)) {
            die('not array, ' . var_export($collection, true));
        }

        // discard keys
        $collection = array_values($collection);

        if (isset($this->attributes['limit']) || isset($this->attributes['offset'])) {
            $limit = $context->get($this->attributes['limit']);
            $offset = $context->get($this->attributes['offset']);
            $collection = array_slice($collection, $offset, $limit);
        }

        $length = count($collection);

        $cols = isset($this->attributes['cols']) ? $context->get($this->attributes['cols']) : PHP_INT_MAX;

        $row = 1;
        $col = 0;

        $result = "<tr class=\"row1\">\n";

        $context->push();

        foreach ($collection as $index => $item) {
            $context->set($this->variableName, $item);
            $context->set('tablerowloop', array(
                'length' => $length,
                'index' => $index + 1,
                'index0' => $index,
                'rindex' => $length - $index,
                'rindex0' => $length - $index - 1,
                'first' => (int)($index == 0),
                'last' => (int)($index == $length - 1)
            ));

            $text = $this->renderAll($this->nodelist, $context);
            $break = isset($context->registers['break']);
            $continue = isset($context->registers['continue']);

            if ((!$break && !$continue) || strlen(trim($text)) > 0) {
                $result .= "<td class=\"col" . (++$col) . "\">$text</td>";
            }

            if ($col == $cols && !($index == $length - 1)) {
                $col = 0;
                $result .= "</tr>\n<tr class=\"row" . (++$row) . "\">\n";
            }

            if ($break) {
                unset($context->registers['break']);
                break;
            }
            if ($continue) {
                unset($context->registers['continue']);
            }
        }

        $context->pop();

        $result .= "</tr>\n";

        return $result;
    }
}