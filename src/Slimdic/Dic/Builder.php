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
use Chippyash\Type\String\StringType;
use Psr\Container\ContainerInterface;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
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
     * @param StringType $definitionFile full path to dic definition file
     *
     * @throws \Exception
     *
     * @return ServiceContainer
     */
    public static function buildDic(StringType $definitionFile)
    {
        if (!file_exists($definitionFile())) {
            throw new \Exception(self::ERR_NO_DIC);
        }

        //create dic
        /** @noinspection PhpUndefinedMethodInspection */
        return FFor::create(['definitionFile' => $definitionFile])
            //create the DIC
            ->dic(function () {
                return PHP_MAJOR_VERSION < 7 ? new ServiceContainer() : new ContainerBuilder();
            })
            //do some processing on the DIC
            ->process(function ($dic, $definitionFile) {
                $fileLocator = new FileLocator(dirname($definitionFile()));
                $fileLoaders = [
                    new XmlFileLoader($dic, $fileLocator),
                    new YamlFileLoader($dic, $fileLocator),
                ];
                (new DelegatingLoader(new LoaderResolver($fileLoaders)))->load($definitionFile());
                self::preCompile($dic);
                /** @noinspection PhpUndefinedMethodInspection */
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
     * @param ContainerInterface $dic
     */
    protected static function preCompile(ContainerInterface $dic)
    {
        if (empty(self::$preCompileFunction)) {
            return;
        }
        $func = self::$preCompileFunction;
        $func($dic);
    }

    /**
     * Do some processing on dic after compilation
     *
     * @param ContainerInterface $dic
     */
    protected static function postCompile(ContainerInterface $dic)
    {
        if (empty(self::$postCompileFunction)) {
            return;
        }
        $func = self::$postCompileFunction;
        $func($dic);
    }
}
