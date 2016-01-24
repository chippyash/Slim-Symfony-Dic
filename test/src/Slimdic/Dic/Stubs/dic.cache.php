<?php
namespace Slimdic\Dic;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\InactiveScopeException;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;

/**
 * SlimdicServiceContainer.
 *
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 */
class SlimdicServiceContainer extends \Slimdic\Dic\ServiceContainer
{
    private $parameters;
    private $targetDirs = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->parameters = $this->getDefaultParameters();

        $this->services =
        $this->scopedServices =
        $this->scopeStacks = array();
        $this->scopes = array();
        $this->scopeChildren = array();
        $this->methodMap = array(
            'callableresolver' => 'getCallableresolverService',
            'environment' => 'getEnvironmentService',
            'errorhandler' => 'getErrorhandlerService',
            'foo' => 'getFooService',
            'foundhandler' => 'getFoundhandlerService',
            'notallowedhandler' => 'getNotallowedhandlerService',
            'notfoundhandler' => 'getNotfoundhandlerService',
            'request' => 'getRequestService',
            'response' => 'getResponseService',
            'router' => 'getRouterService',
            'settings' => 'getSettingsService',
        );

        $this->aliases = array();
    }

    /**
     * {@inheritdoc}
     */
    public function compile()
    {
        throw new LogicException('You cannot compile a dumped frozen container.');
    }

    /**
     * Gets the 'callableresolver' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Slim\CallableResolver A Slim\CallableResolver instance.
     */
    protected function getCallableresolverService()
    {
        return $this->services['callableresolver'] = new \Slim\CallableResolver($this);
    }

    /**
     * Gets the 'environment' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Slim\Http\Environment A Slim\Http\Environment instance.
     */
    protected function getEnvironmentService()
    {
        return $this->services['environment'] = new \Slim\Http\Environment(call_user_func(array('Slimdic\\Dic\\Builder', 'getServerConfig')));
    }

    /**
     * Gets the 'errorhandler' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Slim\Handlers\Error A Slim\Handlers\Error instance.
     */
    protected function getErrorhandlerService()
    {
        return $this->services['errorhandler'] = new \Slim\Handlers\Error(false);
    }

    /**
     * Gets the 'foo' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @throws RuntimeException always since this service is expected to be injected dynamically
     */
    protected function getFooService()
    {
        throw new RuntimeException('You have requested a synthetic service ("foo"). The DIC does not know how to construct this service.');
    }

    /**
     * Gets the 'foundhandler' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Slim\Handlers\Strategies\RequestResponse A Slim\Handlers\Strategies\RequestResponse instance.
     */
    protected function getFoundhandlerService()
    {
        return $this->services['foundhandler'] = new \Slim\Handlers\Strategies\RequestResponse();
    }

    /**
     * Gets the 'notallowedhandler' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Slim\Handlers\NotAllowed A Slim\Handlers\NotAllowed instance.
     */
    protected function getNotallowedhandlerService()
    {
        return $this->services['notallowedhandler'] = new \Slim\Handlers\NotAllowed();
    }

    /**
     * Gets the 'notfoundhandler' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Slim\Handlers\NotFound A Slim\Handlers\NotFound instance.
     */
    protected function getNotfoundhandlerService()
    {
        return $this->services['notfoundhandler'] = new \Slim\Handlers\NotFound();
    }

    /**
     * Gets the 'request' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Slim\Http\Request A Slim\Http\Request instance.
     */
    protected function getRequestService()
    {
        return $this->services['request'] = \Slim\Http\Request::createFromEnvironment($this->get('environment'));
    }

    /**
     * Gets the 'response' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Slim\Http\Response A Slim\Http\Response instance.
     */
    protected function getResponseService()
    {
        $this->services['response'] = $instance = new \Slim\Http\Response(200, new \Slim\Http\Headers(array('Content-Type' => 'text/html; charset=UTF-8')));

        $instance->withProtocolVersion('1.1');

        return $instance;
    }

    /**
     * Gets the 'router' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Slim\Router A Slim\Router instance.
     */
    protected function getRouterService()
    {
        return $this->services['router'] = new \Slim\Router();
    }

    /**
     * Gets the 'settings' service.
     *
     * This service is shared.
     * This method always returns the same instance of the service.
     *
     * @return \Slim\Collection A Slim\Collection instance.
     */
    protected function getSettingsService()
    {
        return $this->services['settings'] = new \Slim\Collection(array('httpVersion' => '1.1', 'responseChunkSize' => 4096, 'outputBuffering' => 'append', 'determineRouteBeforeAppMiddleware' => false, 'displayErrorDetails' => false));
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter($name)
    {
        $name = strtolower($name);

        if (!(isset($this->parameters[$name]) || array_key_exists($name, $this->parameters))) {
            throw new InvalidArgumentException(sprintf('The parameter "%s" must be defined.', $name));
        }

        return $this->parameters[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function hasParameter($name)
    {
        $name = strtolower($name);

        return isset($this->parameters[$name]) || array_key_exists($name, $this->parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter($name, $value)
    {
        throw new LogicException('Impossible to call set() on a frozen ParameterBag.');
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterBag()
    {
        if (null === $this->parameterBag) {
            $this->parameterBag = new FrozenParameterBag($this->parameters);
        }

        return $this->parameterBag;
    }

    /**
     * Gets the default parameters.
     *
     * @return array An array of the default parameters
     */
    protected function getDefaultParameters()
    {
        return array(
            'slim.config.httpversion' => '1.1',
            'slim.config.outputbuffering' => 'append',
            'slim.config.determineroutebeforeappmiddleware' => false,
            'slim.config.displayerrordetails' => false,
            'slim.config.response.defaultcontenttype' => 'text/html; charset=UTF-8',
            'slim.config.response.defaultstatus' => 200,
            'slim.config.response.chunksize' => 4096,
            'slim.config.response.defaultheaders' => array(
                'Content-Type' => 'text/html; charset=UTF-8',
            ),
            'slim.config.classname.settings' => 'Slim\\Collection',
            'slim.config.classname.environment' => 'Slim\\Http\\Environment',
            'slim.config.classname.request' => 'Slim\\Http\\Request',
            'slim.config.classname.response' => 'Slim\\Http\\Response',
            'slim.config.classname.headers' => 'Slim\\Http\\Headers',
            'slim.config.classname.router' => 'Slim\\Router',
            'slim.config.classname.foundhandler' => 'Slim\\Handlers\\Strategies\\RequestResponse',
            'slim.config.classname.notfoundhandler' => 'Slim\\Handlers\\NotFound',
            'slim.config.classname.errorhandler' => 'Slim\\Handlers\\Error',
            'slim.config.classname.notallowedhandler' => 'Slim\\Handlers\\NotAllowed',
            'slim.config.classname.callableresolver' => 'Slim\\CallableResolver',
        );
    }
}
