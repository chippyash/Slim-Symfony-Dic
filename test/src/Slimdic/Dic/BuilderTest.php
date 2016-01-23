<?php
/**
 * Slim-Symfony-Dic
 *
 * @author Ashley Kitson
 * @copyright Ashley Kitson, 2016, UK
 * @license GPL V3+ See LICENSE.md
 */

namespace Slimdic\Test\Dic;

use chippyash\Type\BoolType;
use chippyash\Type\String\StringType;
use org\bovigo\vfs\vfsStream;
use Slimdic\Dic\Builder;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $rootPath = 'vfs://root';

    protected $dicFileName = 'dic.production.xml';

    protected function setUp()
    {
        $structure = [
            'Site' => [
                'cfg' => [
                    $this->dicFileName => $this->dicDefinition()
                ]
            ],
            'spool' => []
        ];
        vfsStream::setup('root', null, $structure);
    }

    public function testYouCanCreateAContainer()
    {
        $dic = Builder::buildDic(
            new StringType($this->rootPath . '/Site/cfg/' . $this->dicFileName)
        );
        $this->assertInstanceOf('Slimdic\Dic\Container', $dic);
    }

    public function testYouCanCreateAContainerAndCacheACopy()
    {
        $dic = Builder::buildDic(
            new StringType($this->rootPath . '/Site/cfg/' . $this->dicFileName),
            new StringType($this->rootPath . '/spool')
        );
        $this->assertInstanceOf('Slimdic\Dic\Container', $dic);
        $this->assertTrue(file_exists($this->rootPath . '/spool' . Builder::CACHE_PHP_NAME));
        $this->assertFalse(file_exists($this->rootPath . '/spool' . Builder::CACHE_XML_NAME));
    }

    public function testYouCanCreateAContainerAndDumpAResolvedXmlDefinition()
    {
        $dic = Builder::buildDic(
            new StringType($this->rootPath . '/Site/cfg/' . $this->dicFileName),
            new StringType($this->rootPath . '/spool'),
            new BoolType(true)
        );
        $this->assertInstanceOf('Slimdic\Dic\Container', $dic);
        $this->assertTrue(file_exists($this->rootPath . '/spool' . Builder::CACHE_PHP_NAME));
        $this->assertTrue(file_exists($this->rootPath . '/spool' . Builder::CACHE_XML_NAME));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Cannot find DIC definition
     */
    public function testSpecifyingANonExistentDefinitionFileWillThrowAnException()
    {
        Builder::buildDic(
            new StringType('foo.xml')
        );
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Cache directory does not exist
     */
    public function testSpecifyingANonExistentCacheDirectoryWhenCreatingAContainerWillThrowAnException()
    {
        Builder::buildDic(
            new StringType($this->rootPath . '/Site/cfg/' . $this->dicFileName),
            new StringType('/foo')
        );
    }

    public function testYouCanCreateAnApplicationWithAnInteropCompatibleSymfonyContainer()
    {
        $app = Builder::getApp(
            new StringType($this->rootPath . '/Site/cfg/' . $this->dicFileName)
        );
        $this->assertInstanceOf('Slim\App', $app);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Container', $app->getContainer());
        $this->assertInstanceOf('Interop\Container\ContainerInterface', $app->getContainer());
    }

    public function testYouCanCreateAnApplicationWithACachedContainer()
    {
        Builder::buildDic(
            new StringType($this->rootPath . '/Site/cfg/' . $this->dicFileName),
            new StringType($this->rootPath . '/spool')
        );

        Builder::getApp(
            new StringType($this->rootPath . '/Site/cfg/' . $this->dicFileName),
            new StringType($this->rootPath . '/spool')
        );

        $this->assertTrue(file_exists($this->rootPath . '/spool' . Builder::CACHE_PHP_NAME));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Cache directory does not exist
     */
    public function testSpecifyingANonExistentCacheDirectoryWhenCreatingAnApplicationWillThrowAnException()
    {
        Builder::getApp(
            new StringType($this->rootPath . '/Site/cfg/' . $this->dicFileName),
            new StringType('/foo')
        );
    }

    public function testYouCanCreateAnApplicationAndDumpAResolvedXmlDefinition()
    {
        Builder::getApp(
            new StringType($this->rootPath . '/Site/cfg/' . $this->dicFileName),
            new StringType($this->rootPath . '/spool'),
            new BoolType(true)
        );
        $this->assertTrue(file_exists($this->rootPath . '/spool' . Builder::CACHE_PHP_NAME));
        $this->assertTrue(file_exists($this->rootPath . '/spool' . Builder::CACHE_XML_NAME));
    }

    public function testTheExampleMinimalConfigurationWillCompile()
    {
        $exampleFile = realpath(__DIR__ . '/../../../../examples/dic.slim.xml');
        file_put_contents(
            $this->rootPath . '/Site/cfg/' . $this->dicFileName,
            file_get_contents($exampleFile)
        );

        $dic = Builder::buildDic(
            new StringType($this->rootPath . '/Site/cfg/' . $this->dicFileName),
            new StringType($this->rootPath . '/spool')
        );

        $this->assertInstanceOf('Slimdic\Dic\Container', $dic);
        $this->assertInstanceOf($dic->getParameter('slim.config.className.environment'), $dic->get('environment'));
        $this->assertInstanceOf($dic->getParameter('slim.config.className.request'), $dic->get('request'));
        $this->assertInstanceOf($dic->getParameter('slim.config.className.response'), $dic->get('response'));
        $this->assertInstanceOf($dic->getParameter('slim.config.className.router'), $dic->get('router'));
        $this->assertInstanceOf($dic->getParameter('slim.config.className.foundHandler'), $dic->get('foundHandler'));
        $this->assertInstanceOf($dic->getParameter('slim.config.className.errorHandler'), $dic->get('errorHandler'));
        $this->assertInstanceOf($dic->getParameter('slim.config.className.notAllowedHandler'), $dic->get('notAllowedHandler'));
        $this->assertInstanceOf($dic->getParameter('slim.config.className.callableResolver'), $dic->get('callableResolver'));
    }

    /**
     * Return minimal DIC definition - same as /examples/dic.slim.xml
     * @return string
     */
    private function dicDefinition()
    {

        return <<<EOT
<?xml version="1.0" encoding="utf-8" ?>
<container xmlns="http://symfony.com/schema/dic/services"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">
</container>
EOT;
    }
}
