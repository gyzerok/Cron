<?php
namespace Cron\CronBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;

interface InitializableControllerInterface
{
    public function initialize(Request $request);
}
