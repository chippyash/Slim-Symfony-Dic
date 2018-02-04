<?php
/**
 * Slim-Symfony-Dic
 *
 * @author Ashley Kitson
 * @copyright Ashley Kitson, 2016, UK
 * @license GPL V3+ See LICENSE.md
 */

namespace Test\Slimdic\Dic;

use Chippyash\Type\String\StringType;
use Slimdic\Dic\Builder;
use Test\Slimdic\TestCase;

/**
* Test the example DIC XML definition file
*/
class ExampleFileTest extends TestCase
{
    public function testExampleFileContainerCompiles()
    {
        $xmlFile = PHP_MAJOR_VERSION < 7 ? 'dic.slim.s2.xml' : 'dic.slim.s3.xml';
        $exampleFile = realpath(
            __DIR__ . '/../../../examples/' . $xmlFile
        );

        $dic = Builder::buildDic(new StringType($exampleFile));

        if (PHP_MAJOR_VERSION < 7) {
            $this->assertInstanceOf('Slimdic\Dic\ServiceContainer', $dic);
        } else {
            $this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerBuilder', $dic);
        }
        $this->assertInstanceOf(
            $dic->getParameter('slim.config.className.environment'),
            $dic->get('environment')
        );
        $this->assertInstanceOf(
            $dic->getParameter('slim.config.className.request'),
            $dic->get('request')
        );
        $this->assertInstanceOf(
            $dic->getParameter('slim.config.className.response'),
            $dic->get('response')
        );
        $this->assertInstanceOf(
            $dic->getParameter('slim.config.className.router'),
            $dic->get('router')
        );
        $this->assertInstanceOf(
            $dic->getParameter('slim.config.className.foundHandler'),
            $dic->get('foundHandler')
        );
        $this->assertInstanceOf(
            $dic->getParameter('slim.config.className.errorHandler'),
            $dic->get('errorHandler')
        );
        $this->assertInstanceOf(
            $dic->getParameter('slim.config.className.notAllowedHandler'),
            $dic->get('notAllowedHandler')
        );
        $this->assertInstanceOf(
            $dic->getParameter('slim.config.className.callableResolver'),
            $dic->get('callableResolver')
        );
    }
}
