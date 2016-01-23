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
    
    /**
     * Build the DIC and return the Slim\App application component
     *
     * The DIC will be available as app->getContainer()
     *
     * @param StringType $definitionXmlFile full path to xml dic definition file
     * @param StringType $cacheDir path to directory to store dic cached version
     * @param BoolType $cacheTheDic If true then attempt to load dic from cache and write it if not found
     * @param BoolType $dumpResolvedXmlFile If true will also dump the DI as a fully resolved xml file
     *
     * @throws \Exception
     *
     * @return \Slim\App
     */
    public static function getApp(
        StringType $definitionXmlFile,
        StringType $cacheDir,
        BoolType $cacheTheDic = null,
        BoolType $dumpResolvedXmlFile = null)
    {
        return FFor::create(self::grabFunctionParameters(__CLASS__, __FUNCTION__, func_get_args()))
            ->caching(function($cacheTheDic) {
                return Match::on($cacheTheDic)
                    ->null(function() {return false;})
                    ->any(function($cacheTheDic) {return $cacheTheDic();})
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
            ->app(function($caching, $diCacheName, $definitionXmlFile, $cacheDir, $cacheTheDic, $dumpResolvedXmlFile) {
                return new App(
                    Match::on(Option::create($caching && file_exists($diCacheName), false))
                        ->Monad_Option_Some(function() use ($diCacheName) {
                            require_once $diCacheName;
                            return new \ProjectServiceContainer();
                        })
                        ->Monad_Option_None(function() use ($definitionXmlFile, $cacheDir, $cacheTheDic, $dumpResolvedXmlFile) {
                            return self::buildDic($definitionXmlFile, $cacheDir, $cacheTheDic, $dumpResolvedXmlFile);
                        })
                        ->value()
                );
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
     * @param BoolType $cacheTheDic If true then attempt to load dic from cache and write it if not found
     * @param BoolType $dumpResolvedXmlFile If true will also dump the DI as a fully resolved xml file
     *
     * @throws \Exception
     *
     * @return Container
     */
    public static function buildDic(
        StringType $definitionXmlFile,
        StringType $cacheDir,
        BoolType $cacheTheDic = null,
        BoolType $dumpResolvedXmlFile = null)
    {
        if (!file_exists($definitionXmlFile())) {
            throw new \Exception(self::ERR_NO_DIC);
        }

        if (!file_exists($cacheDir())) {
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
                $loader = new XmlFileLoader($dic, new FileLocator(dirname($definitionXmlFile())));
                $loader->load($definitionXmlFile());
                $dic->compile();
            })
            //return the completed DIC
            ->fyield('dic');

        //and cache it
        if (!is_null($cacheTheDic) && $cacheTheDic()) {
            $dumper = new PhpDumper($dic);
            $diCacheName = $cacheDir . self::CACHE_PHP_NAME;
            file_put_contents($diCacheName, $dumper->dump(['base_class' => 'Slimdic\Dic\Container']));
        }

        if (!is_null($dumpResolvedXmlFile) && $dumpResolvedXmlFile()) {
            $xmlCacheName = $cacheDir . self::CACHE_XML_NAME;
            $xmlDumper = new XmlDumper($dic);
            file_put_contents($xmlCacheName, $xmlDumper->dump());
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
}
