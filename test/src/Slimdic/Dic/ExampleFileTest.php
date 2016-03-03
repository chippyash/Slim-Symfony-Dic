<?php
/**
 * Slim-Symfony-Dic
 *
 * @author Ashley Kitson
 * @copyright Ashley Kitson, 2016, UK
 * @license GPL V3+ See LICENSE.md
 */

namespace Slimdic\Test\Dic;

use Chippyash\Type\String\StringType;
use Slimdic\Dic\Builder;

/**
 * Test the example DIC XML definition file
 */
class ExampleFileTest extends \PHPUnit_Framework_TestCase
{
    public function testExampleFileContainerCompiles()
    {
        $exampleFile = realpath(__DIR__ . '/../../../../examples/dic.slim.xml');

        $dic = Builder::buildDic(new StringType($exampleFile));

        $this->assertInstanceOf('Slimdic\Dic\ServiceContainer', $dic);
        $this->assertInstanceOf($dic->getParameter('slim.config.classname.environment'), $dic->get('environment'));
        $this->assertInstanceOf($dic->getParameter('slim.config.classname.request'), $dic->get('request'));
        $this->assertInstanceOf($dic->getParameter('slim.config.classname.response'), $dic->get('response'));
        $this->assertInstanceOf($dic->getParameter('slim.config.classname.router'), $dic->get('router'));
        $this->assertInstanceOf($dic->getParameter('slim.config.classname.foundHandler'), $dic->get('foundHandler'));
        $this->assertInstanceOf($dic->getParameter('slim.config.classname.errorHandler'), $dic->get('errorHandler'));
        $this->assertInstanceOf($dic->getParameter('slim.config.classname.notAllowedHandler'), $dic->get('notAllowedHandler'));
        $this->assertInstanceOf($dic->getParameter('slim.config.classname.callableResolver'), $dic->get('callableResolver'));
    }
}
