<?php
/**
 * Slim-Dic
 *
 * @author Ashley Kitson
 * @copyright Ashley Kitson, 2016, UK
 * @license GPL V3+ See LICENSE.md
 */

namespace Slimdic\Dic;


use Interop\Container\ContainerInterface as IOPContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface as SymContainerInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * Provide Interop compatible DI container for the Symfony container
 */
class Container extends ContainerBuilder implements IOPContainerInterface
{

    /**
     * Interop::get() expects return of anything in the container.  This munges get()
     * and getParameter() from the Symfony container.
     *
     * It's purely arbitrary that we check the parameters first. It would be a real
     * edge case that a parameter has the same name as a service.
     *
     * @param string $id
     * @param int $invalidBehavior
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public function get($id, $invalidBehavior = SymContainerInterface::EXCEPTION_ON_INVALID_REFERENCE)
    {
        if (parent::hasParameter($id)) {
            return parent::getParameter($id);
        }

        if (parent::has($id)) {
            return parent::get($id, $invalidBehavior);
        }

        switch ($invalidBehavior) {
            case SymContainerInterface::EXCEPTION_ON_INVALID_REFERENCE:
                throw new InvalidArgumentException("Unknown definition: {$id}");
                break;
            case SymContainerInterface::NULL_ON_INVALID_REFERENCE:
                return null;
                break;
        }
    }

    /**
     * Interop::has() expects true if item is in the container.  This munges has()
     * and hasParameter() from the Symfony container
     *
     * @param string $id
     * @return bool
     */
    public function has($id)
    {
        return parent::hasParameter($id) || parent::has($id);
    }
}