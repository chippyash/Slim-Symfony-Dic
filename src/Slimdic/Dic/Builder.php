<?php
/**
 * Chippyash Slim DIC integration
 *
 * For Slim V3
 * 
 * DIC builder
 * 
 * @author Ashley Kitson
 * @copyright Ashley Kitson, 2014-2016, UK
 */
namespace Slimdic\Dic;

use Assembler\FFor;
use Assembler\Traits\ParameterGrabable;
use chippyash\Type\BoolType;
use chippyash\Type\String\StringType;
use Monad\FTry;
use Monad\Match;
use Monad\Option;
use Slim\App;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Dumper\XmlDumper;

/**
 * Builder to compile the dic and return the application
 * containing the DIC
 */
abstract class Builder
{
    use ParameterGrabable;

    /**
     * DIC php cache name
     */
    const CACHE_PHP_NAME = '/dic.cache.php';
    
    /**
     * DIC XML cache name - written if environment mode == development
     */
    const CACHE_XML_NAME = '/dic.cache.xml';
    
    /**
     * Error string
     */
    const ERR_NO_DIC = 'Cannot find DIC definition';

    /**
     * Error string
     */
    const ERR_NO_CACHE_DIR = 'Cache directory does not exist';

    /**#@+
     * Flags for post compile stages
     */
    const COMPILE_STAGE_BUILD = 0;
    const COMPILE_STAGE_APP = 1;
    /**#@-*/

    /**
     * @var callable
     */
    protected static $preCompileFunction;

    /**
     * @var callable
     */
    protected static $postCompileFunction;

    /**
     * Build the DIC and return the Slim\App application component
     *
     * The DIC will be available as app->getContainer()
     *
     * @param StringType $definitionXmlFile full path to xml dic definition file
     * @param StringType $cacheDir path to directory to store dic cached version
     * @param BoolType $dumpResolvedXmlFile If true will also dump the DI as a fully resolved xml file
     *
     * @throws \Exception
     *
     * @return \Slim\App
     */
    public static function getApp(
        StringType $definitionXmlFile,
        StringType $cacheDir = null,
        BoolType $dumpResolvedXmlFile = null)
    {
        return FFor::create(self::grabFunctionParameters(__CLASS__, __FUNCTION__, func_get_args()))
            ->caching(function($cacheDir) {
                return Match::on($cacheDir)
                    ->null(function() {return false;})
                    ->any(function() {return true;})
                    ->value();
            })
            ->noCacheTest(function($cacheDir, $caching) {
                Match::on(Option::create($caching && !file_exists($cacheDir()), false))
                    ->Monad_Option_Some(function() {
                        throw new \Exception(self::ERR_NO_CACHE_DIR);
                    });
            })
            ->diCacheName(function($cacheDir) {
                return $cacheDir . self::CACHE_PHP_NAME;
            })
            ->app(function($caching, $diCacheName, $definitionXmlFile, $cacheDir, $dumpResolvedXmlFile) {
                return new App(
                    Match::on(Option::create($caching && file_exists($diCacheName), false))
                        ->Monad_Option_Some(function() use ($diCacheName) {
                            require_once $diCacheName;
                            return new \ProjectServiceContainer();
                        })
                        ->Monad_Option_None(function() use ($definitionXmlFile, $cacheDir, $dumpResolvedXmlFile) {
                            return self::buildDic($definitionXmlFile, $cacheDir, $dumpResolvedXmlFile);
                        })
                        ->value()
                );
            })
            ->postCompile(function($app) {
                self::postCompile($app->getContainer(), self::COMPILE_STAGE_APP);
            })
            ->fyield('app');
    }
    
    /**
     * Build and return the DIC
     *
     * Stores cached version of DIC. If $dumpResolvedXmlFile == true, will
     * also write out an xml version in the same location which can be helpful
     * for debugging
     * 
     * @param StringType $definitionXmlFile full path to xml dic definition file
     * @param StringType $cacheDir path to directory to store dic cached version
     * @param BoolType $dumpResolvedXmlFile If true will also dump the DI as a fully resolved xml file
     *
     * @throws \Exception
     *
     * @return Container
     */
    public static function buildDic(
        StringType $definitionXmlFile,
        StringType $cacheDir = null,
        BoolType $dumpResolvedXmlFile = null)
    {
        if (!file_exists($definitionXmlFile())) {
            throw new \Exception(self::ERR_NO_DIC);
        }

        if (!is_null($cacheDir) && !file_exists($cacheDir())) {
            throw new \Exception(self::ERR_NO_CACHE_DIR);
        }

        //create dic
        $dic = FFor::create(['definitionXmlFile' => $definitionXmlFile])
            //create the DIC
            ->dic(function(){
                return new Container();
            })
            //do some processing on the DIC
            ->process(function($dic, $definitionXmlFile) {
                (new XmlFileLoader($dic, new FileLocator(dirname($definitionXmlFile()))))
                    ->load($definitionXmlFile());
                self::preCompile($dic);
                $dic->compile();
                self::postCompile($dic, self::COMPILE_STAGE_BUILD);
            })
            //return the completed DIC
            ->fyield('dic');

        //and cache it
        if (!is_null($cacheDir)) {
            file_put_contents(
                $cacheDir . self::CACHE_PHP_NAME,
                (new PhpDumper($dic))->dump(['base_class' => 'Slimdic\Dic\Container'])
            );
        }

        if (!is_null($dumpResolvedXmlFile) && $dumpResolvedXmlFile()) {
            file_put_contents(
                $cacheDir . self::CACHE_XML_NAME,
                (new XmlDumper($dic))->dump()
            );
        }
        
        return $dic;
    }

    /**
     * Helper function for dic - simply returns $_SERVER
     *
     * @return array
     */
    public static function getServerConfig()
    {
        return $_SERVER;
    }

    /**
     * Register a function to be called just before compiling the DI
     *
     * Function signature is function(Slim\Dic\Container $dic)
     *
     * @param callable $func
     */
    public static function registerPreCompileFunction(callable $func)
    {
        self::$preCompileFunction = $func;
    }

    /**
     * Register a function to be called just after compiling the DI
     *
     * Function signature is function(Slim\Dic\Container $dic, int $stage)
     *
     * @param callable $func
     */
    public static function registerPostCompileFunction(callable $func)
    {
        self::$postCompileFunction = $func;
    }

    /**
     * Do some processing on dic before compilation
     *
     * @param Container $dic
     */
    protected static function preCompile(Container $dic) {
        if (empty(self::$preCompileFunction)) {
            return;
        }
        $func = self::$preCompileFunction;
        $func($dic);
    }

    /**
     * Do some processing on dic after compilation
     *
     * @param Container $dic
     * @param int $stage
     */
    protected static function postCompile(Container $dic, $stage) {
        if (empty(self::$postCompileFunction)) {
            return;
        }
        $func = self::$postCompileFunction;
        $func($dic, $stage);
    }
}
