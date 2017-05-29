<?php

namespace Joseph\Xml\Util;


use DOMDocument;
use DOMNode;
use Exception;

class Array2XML
{
    private $xml = null;
    private $encoding = 'UTF-8';
    private static $instance = null;

    /**
     * Initialize the root XML node [optional]
     * @param $version
     * @param $encoding
     * @param $formatOutput
     */
    private function __construct($version = '1.0', $encoding = 'UTF-8', $formatOutput = true)
    {
        $this->xml = new DOMDocument($version, $encoding);
        $this->xml->formatOutput = $formatOutput;
        $this->encoding = $encoding;
    }

    public static function getInstance($version = '1.0', $encoding = 'UTF-8', $formatOutput = true)
    {
        if (self::$instance === null) {
            self::$instance = new self($version, $encoding, $formatOutput);
        }
        return self::$instance;
    }

    /**
     * Convert an Array to XML
     * @param string $nodeName - name of the root node to be converted
     * @param array $inputArray - array to be converted
     * @return DomDocument
     */
    public function createXML($nodeName, $inputArray = array())
    {
        $xml = $this->xml;
        $xml->appendChild($this->convert($nodeName, $inputArray));
        $this->xml = null;    // clear the xml node in the class for 2nd time use.
        return $xml;
    }

    /**
     * Convert an Array to XML
     * @param string $node_name - name of the root node to be converted
     * @param array $arr - array to be converted
     * @return DOMNode
     * @throws Exception
     */
    private function convert($node_name, $arr = array())
    {
        $xml = $this->xml;
        $node = $xml->createElement($node_name);

        if (is_array($arr)) {
            // get the attributes first.;
            if (isset($arr['@attributes'])) {
                foreach ($arr['@attributes'] as $key => $value) {
                    if (!$this->isValidTagName($key)) {
                        throw new Exception('[Array2XML] Illegal character in attribute name. attribute: ' . $key . ' in node: ' . $node_name);
                    }
                    $node->setAttribute($key, $this->booleanToString($value));
                }
                unset($arr['@attributes']); //remove the key from the array once done.
            }

            // check if it has a value stored in @value, if yes store the value and return
            // else check if its directly stored as string
            if (isset($arr['@value'])) {
                $node->appendChild($xml->createTextNode($this->booleanToString($arr['@value'])));
                unset($arr['@value']);    //remove the key from the array once done.
                //return from recursion, as a note with value cannot have child nodes.
                return $node;
            } else if (isset($arr['@cdata'])) {
                $node->appendChild($xml->createCDATASection($this->booleanToString($arr['@cdata'])));
                unset($arr['@cdata']);    //remove the key from the array once done.
                //return from recursion, as a note with cdata cannot have child nodes.
                return $node;
            }
        }

        //create sub-nodes using recursion
        if (is_array($arr)) {
            // recurse to get the node for that key
            foreach ($arr as $key => $value) {
                if (!$this->isValidTagName($key)) {
                    throw new Exception('Illegal character in tag name. tag: ' . $key . ' in node: ' . $node_name);
                }
                if (is_array($value) && is_numeric(key($value))) {
                    // MORE THAN ONE NODE OF ITS KIND;
                    // if the new array is numeric index, means it is array of nodes of the same kind
                    // it should follow the parent key name
                    foreach ($value as $k => $v) {
                        $node->appendChild($this->convert($key, $v));
                    }
                } else {
                    // ONLY ONE NODE OF ITS KIND
                    $node->appendChild($this->convert($key, $value));
                }
                unset($arr[$key]); //remove the key from the array once done.
            }
        }

        // after we are done with all the keys in the array (if it is one)
        // we check if it has any text value, if yes, append it.
        if (!is_array($arr)) {
            $node->appendChild($xml->createTextNode($this->booleanToString($arr)));
        }

        return $node;
    }

    /**
     * Get string representation of boolean value
     * @param $value
     * @return string
     */
    private function booleanToString($value)
    {
        //convert boolean to text value.
        $value = $value === true ? 'true' : $value;
        $value = $value === false ? 'false' : $value;
        return $value;
    }

    /**
     * Check if the tag name or attribute name contains illegal characters
     * Ref: http://www.w3.org/TR/xml/#sec-common-syn
     * @param $tag
     * @return bool
     */
    private function isValidTagName($tag)
    {
        $pattern = '/^[a-z_]+[a-z0-9\:\-\.\_]*[^:]*$/i';
        return preg_match($pattern, $tag, $matches) && $matches[0] == $tag;
    }

}
