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
use Slimdic\Dic\ServiceContainer;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class BuilderTest
 */
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
        $this->assertInstanceOf('Slimdic\Dic\ServiceContainer', $dic);
    }

    public function testYouCanCreateAContainerAndCacheACopy()
    {
        $dic = Builder::buildDic(
            new StringType($this->rootPath . '/Site/cfg/' . $this->dicFileName),
            new StringType($this->rootPath . '/spool')
        );
        $this->assertInstanceOf('Slimdic\Dic\ServiceContainer', $dic);
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
        $this->assertInstanceOf('Slimdic\Dic\ServiceContainer', $dic);
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

    public function testYouCanDoPreCompilationTasksByRegisteringAPrecompileFunction()
    {
        Builder::registerPreCompileFunction(function(ServiceContainer $dic) {
            $dic->setParameter('foo', 'bar');
        });
        $dic = Builder::buildDic(
            new StringType($this->rootPath . '/Site/cfg/' . $this->dicFileName)
        );
        $this->assertEquals('bar', $dic->get('foo'));
    }

    public function testYouCanDoPostCompilationInBuildStageTasksByRegisteringAPostcompileFunction()
    {
        Builder::registerPreCompileFunction(function(ServiceContainer $dic) {
            $dic->setDefinition('foo', (new Definition())->setSynthetic(true));
        });
        Builder::registerPostCompileFunction(function(ServiceContainer $dic, $stage) {
            if($stage == Builder::COMPILE_STAGE_BUILD) {
                $dic->set('foo', 'bar');
            }
        });
        $dic = Builder::buildDic(
            new StringType($this->rootPath . '/Site/cfg/' . $this->dicFileName)
        );
        $this->assertEquals('bar', $dic->get('foo'));
    }

    public function testYouCanDoPostCompilationInAppStageTasksByRegisteringAPostcompileFunction()
    {
        Builder::registerPreCompileFunction(function(ServiceContainer $dic) {
            $dic->setDefinition('foo', (new Definition())->setSynthetic(true));
        });
        Builder::registerPostCompileFunction(function(ServiceContainer $dic, $stage) {
            if($stage == Builder::COMPILE_STAGE_APP) {
                $dic->set('foo', 'bar');
            }
        });
        $app = Builder::getApp(
            new StringType($this->rootPath . '/Site/cfg/' . $this->dicFileName)
        );
        $this->assertEquals('bar', $app->getContainer()->get('foo'));
    }

    /**
     * Return minimal DIC definition
     * @return string
     */
    private function dicDefinition()
    {

        return <<<EOT
<?xml version="1.0" encoding="utf-8" ?>
<container xmlns="http://symfony.com/schema/dic/services"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

    <parameters>
        <parameter key="foofoo">foofoo</parameter>
    </parameters>

    <services>
        <service id="barbar" class="stdClass"/>
    </services>
</container>
EOT;
    }
}
