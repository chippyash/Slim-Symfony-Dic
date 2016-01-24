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
use chippyash\Type\BoolType;
use chippyash\Type\String\StringType;
use Monad\FTry;
use Monad\Option;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
 * Builder to compile the dic
 */
abstract class Builder
{

    /**
     * Error string
     */
    const ERR_NO_DIC = 'Cannot find DIC definition';

    /**
     * @var callable
     */
    protected static $preCompileFunction;

    /**
     * @var callable
     */
    protected static $postCompileFunction;

    /**
     * Build and return the DIC
     *
     * @param StringType $definitionXmlFile full path to xml dic definition file
     *
     * @throws \Exception
     *
     * @return Container
     */
    public static function buildDic(StringType $definitionXmlFile)
    {
        if (!file_exists($definitionXmlFile())) {
            throw new \Exception(self::ERR_NO_DIC);
        }

        //create dic
        return FFor::create(['definitionXmlFile' => $definitionXmlFile])
            //create the DIC
            ->dic(function(){
                return new ServiceContainer();
            })
            //do some processing on the DIC
            ->process(function($dic, $definitionXmlFile) {
                (new XmlFileLoader($dic, new FileLocator(dirname($definitionXmlFile()))))
                    ->load($definitionXmlFile());
                self::preCompile($dic);
                $dic->compile();
                self::postCompile($dic);
            })
            //return the completed DIC
            ->fyield('dic');
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
     * Function signature is function(Slim\Dic\ServiceContainer $dic)
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
     * Function signature is function(Slim\Dic\ServiceContainer $dic)
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
     * @param ServiceContainer $dic
     */
    protected static function preCompile(ServiceContainer $dic) {
        if (empty(self::$preCompileFunction)) {
            return;
        }
        $func = self::$preCompileFunction;
        $func($dic);
    }

    /**
     * Do some processing on dic after compilation
     *
     * @param ServiceContainer $dic
     */
    protected static function postCompile(ServiceContainer $dic) {
        if (empty(self::$postCompileFunction)) {
            return;
        }
        $func = self::$postCompileFunction;
        $func($dic);
    }
}
