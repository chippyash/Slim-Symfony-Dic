<?php
/**
 * Freetimers Web Application Framework
 *
 * @author    Ashley Kitson
 * @copyright Freetimers Communications Ltd, 2018, UK
 * @license   Proprietary See LICENSE.md
 */
namespace Test\Slimdic;

/**
 * Hack to get over supporting PHP >=5.5,<7 | 7.1
 */
if (PHP_MAJOR_VERSION < 7) {

    class TestCase extends \PHPUnit_Framework_TestCase
    {

    }

} else {

    class TestCase extends \PHPUnit\Framework\TestCase
    {

    }
}
