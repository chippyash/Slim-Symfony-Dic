<?php
/**
 * Slim-Dic
 *
 * @author Ashley Kitson
 * @copyright Ashley Kitson, 2016, UK
 * @license GPL V3+ See LICENSE.md
 */

namespace Slimdic\Test\Dic;

use Slimdic\Dic\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * System Under Test
     * @var Container
     */
    protected $sut;

    protected function setUp()
    {
        $this->sut = new Container();
        $this->sut->setParameter('parameter', 'foo');
        $this->sut->set('service', new \stdClass());
    }

    public function testYouCanTestForAParameterOrAServiceViaTheHasMethod()
    {
        $this->assertTrue($this->sut->has('parameter'));
        $this->assertTrue($this->sut->has('service'));
        $this->assertFalse($this->sut->has('foo'));
    }

    public function testYouCanGetAParameterOrAServiceViaTheGetMethod()
    {
        $this->assertEquals('foo', $this->sut->get('parameter'));
        $this->assertInstanceOf('stdClass', $this->sut->get('service'));
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @expectedExceptionMessage Unknown definition: foobar
     */
    public function testYouCanThrowAnExceptionIfYouCannotGetAContainerEntry()
    {
        $this->sut->get('foobar', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE);
    }

    public function testYouCanReturnNullIfYouCannotGetAContainerEntry()
    {
        $this->assertNull($this->sut->get('foobar', ContainerInterface::NULL_ON_INVALID_REFERENCE));
    }

    /**
     * very difficult to test as returning a void equates to null or empty
     */
    public function testYouCanReturnVoidIfYouCannotGetAContainerEntry()
    {
        $this->assertEmpty($this->sut->get('foobar', ContainerInterface::IGNORE_ON_INVALID_REFERENCE));
    }
}
