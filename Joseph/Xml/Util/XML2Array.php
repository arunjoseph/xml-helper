<?php

namespace Joseph\Xml\Util;

use DOMDocument;
use Exception;

/**
 * Class XML2Array
 * @package App
 */
class XML2Array
{
    /**
     * @var DOMDocument|null
     */
    private $xml = null;
    private $encoding = 'UTF-8';
    private $formatOutput;
    private $version;
    private static $instance = null;

    /**
     * XML2Array constructor.
     * @param string $version
     * @param string $encoding
     * @param bool $formatOutput
     */
    private function __construct($version = '1.0', $encoding = 'UTF-8', $formatOutput = true)
    {
        $this->version = $version;
        $this->formatOutput = $formatOutput;
        $this->encoding = $encoding;
    }

    /**
     * Used to create a singleton instance
     * @param string $version
     * @param string $encoding
     * @param bool $formatOutput
     * @return XML2Array|null
     */
    public static function getInstance($version = '1.0', $encoding = 'UTF-8', $formatOutput = true)
    {
        if (self::$instance === null) {
            self::$instance = new self($version, $encoding, $formatOutput);
        }
        return self::$instance;
    }

    /**
     * Used to load the data
     * @param $inputXml
     * @throws Exception
     */
    public function loadData($inputXml)
    {
        $domDoc = new DOMDocument($this->version, $this->encoding);
        if (is_string($inputXml)) {
            $parsed = $domDoc->loadXML($inputXml);
            if (!$parsed) {
                throw new Exception('Error parsing the XML string.');
            }
        } else {
            if (($inputXml instanceof DOMDocument) === false) {
                throw new Exception('The input XML object should be of type DOMDocument');
            }
            $domDoc = $inputXml;
        }
        $this->xml = $domDoc;
    }

    /**
     * Used to load a file
     * @param $filePath
     * @throws Exception
     */
    public function loadFile($filePath)
    {
        if (file_exists($filePath)) {
            $this->loadData(file_get_contents($filePath));
        } else {
            throw new Exception("The given file doesn't exists");
        }
    }

    /**
     * Used to generate the array
     * @return array
     * @throws Exception
     */
    public function getArray()
    {
        $result = [];
        $xml = $this->xml;
        if ($xml !== null) {
            $result[$xml->documentElement->tagName] = $this->convert($xml->documentElement);
        } else {
            throw new Exception("DOMDocument is not initialized either load an XML string or else a file or instance DOMDocument will also work");
        }
        return $result;
    }

    /**
     * Internal function to convert an XML data to array
     * @param $node
     * @return array|string
     */
    private function convert($node)
    {
        $output = [];
        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE:
                $output['@cdata'] = trim($node->textContent);
                break;

            case XML_TEXT_NODE:
                $output = trim($node->textContent);
                break;

            case XML_ELEMENT_NODE:
                for ($i = 0, $m = $node->childNodes->length; $i < $m; $i++) {
                    $child = $node->childNodes->item($i);
                    $v = $this->convert($child);
                    if (isset($child->tagName)) {
                        $t = $child->tagName;

                        // assume more nodes of same kind are coming
                        if (!isset($output[$t])) {
                            $output[$t] = [];
                        }
                        $output[$t][] = $v;
                    } else {
                        //check if it is not an empty text node
                        if ($v !== '') {
                            $output = $v;
                        }
                    }
                }

                if (is_array($output)) {
                    // if only one node of its kind, assign it directly instead if array($value);
                    foreach ($output as $t => $v) {
                        if (is_array($v) && count($v) == 1) {
                            $output[$t] = $v[0];
                        }
                    }
                    if (empty($output)) {
                        //for empty nodes
                        $output = '';
                    }
                }

                // loop through the attributes and collect them
                if ($node->attributes->length > 0) {
                    $a = [];
                    foreach ($node->attributes as $attrName => $attrNode) {
                        $a[$attrName] = (string)$attrNode->value;
                    }
                    // if its an leaf node, store the value in @value instead of directly storing it.
                    if (!is_array($output)) {
                        $output = ['@value' => $output];
                    }
                    $output['@attributes'] = $a;
                }
                break;
        }
        return $output;
    }

}
