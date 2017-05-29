## XML Utility Classes

This class will help you in accomplishing various actions needed when working with XML and also the class is PSR-4 namespaced

### Convert from Array to XML

```php
<?php
require_once 'vendor/autoload.php';
$books = array(
    '@attributes' => array(
        'type' => 'fiction'
    ),
    'book' => array(
        array(
            '@attributes' => array(
                'author' => 'George Orwell'
            ),
            'title' => '1984'
        ),
        array(
            '@attributes' => array(
                'author' => 'Isaac Asimov'
            ),
            'title' => array('@cdata'=>'Foundation'),
            'price' => '$15.61'
        ),
        array(
            '@attributes' => array(
                'author' => 'Robert A Heinlein'
            ),
            'title' =>  array('@cdata'=>'Stranger in a Strange Land'),
            'price' => array(
                '@attributes' => array(
                    'discount' => '10%'
                ),
                '@value' => '$18.00'
            )
        )
    )
);

$xml = \Joseph\Xml\Util\Array2XML::getInstance();
$xmlData = $xml->createXML('books', $books);
header('Content-Type: application/xml');
echo $xmlData->saveXML();
```

Will result in 

```xml
<?xml version="1.0" encoding="UTF-8"?>
<books type="fiction">
  <book author="George Orwell">
    <title>1984</title>
  </book>
  <book author="Isaac Asimov">
    <title><![CDATA[Foundation]]></title>
    <price>$15.61</price>
  </book>
  <book author="Robert A Heinlein">
    <title><![CDATA[Stranger in a Strange Land]]></title>
    <price discount="10%">$18.00</price>
  </book>
</books>
```

### Convert from XML to Array

```php
<?php
require_once 'vendor/autoload.php';
$movies = '<?xml version="1.0" encoding="UTF-8"?>
<movies type="documentary">
    <movie>
        <title>PHP: Behind the Parser</title>
        <characters>
            <character>
                <name>Ms. Coder</name>
                <actor>Onlivia Actora</actor>
            </character>
            <character>
                <name>Mr. Coder</name>
                <actor>El ActÓr</actor>
            </character>
        </characters>
        <plot><![CDATA[So, this language. It\'s like, a programming language. Or is it a scripting language? 
All is revealed in this thrilling horror spoof of a documentary.]]></plot>
        <great-lines>
            <line>PHP solves all my web problems</line>
        </great-lines>
        <rating type="thumbs">7</rating>
        <rating type="stars">5</rating>
    </movie>
</movies>';

$xml = \Joseph\Xml\Util\XML2Array::getInstance();
$xml->loadData($movies);
var_dump($xml->getArray());
```

Will result in

```php
<?php

array (
  'movies' => 
  array (
    'movie' => 
    array (
      'title' => 'PHP: Behind the Parser',
      'characters' => 
      array (
        'character' => 
        array (
          0 => 
          array (
            'name' => 'Ms. Coder',
            'actor' => 'Onlivia Actora',
          ),
          1 => 
          array (
            'name' => 'Mr. Coder',
            'actor' => 'El ActÓr',
          ),
        ),
      ),
      'plot' => 
      array (
        '@cdata' => 'So, this language. It\'s like, a programming language. Or is it a scripting language? 
All is revealed in this thrilling horror spoof of a documentary.',
      ),
      'great-lines' => 
      array (
        'line' => 'PHP solves all my web problems',
      ),
      'rating' => 
      array (
        0 => 
        array (
          '@value' => '7',
          '@attributes' => 
          array (
            'type' => 'thumbs',
          ),
        ),
        1 => 
        array (
          '@value' => '5',
          '@attributes' => 
          array (
            'type' => 'stars',
          ),
        ),
      ),
    ),
    '@attributes' => 
    array (
      'type' => 'documentary',
    ),
  ),
)
```

You can also replace the above loadData as loadFile which will help in loading an XML file

```
$xml->loadFile($movies);
```
