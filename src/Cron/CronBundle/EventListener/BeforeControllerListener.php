<?php
namespace Cron\CronBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Cron\CronBundle\Controller\InitializableControllerInterface;

class BeforeControllerListener
{
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        if (!is_array($controller)) {
            // not a object but a different kind of callable. Do nothing
            return;
        }

        $controllerObject = $controller[0];

        // skip initializing for exceptions
        if ($controllerObject instanceof ExceptionController) {
            return;
        }

        if ($controllerObject instanceof InitializableControllerInterface) {
            // this method is the one that is part of the interface.
            $controllerObject->initialize($event->getRequest());
        }
    }
}
