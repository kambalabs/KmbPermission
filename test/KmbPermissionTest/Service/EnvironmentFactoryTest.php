<?php
namespace KmbPermissionTest\Service;

use KmbPermission\Service\Environment;
use KmbPermissionTest\Bootstrap;

class EnvironmentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        /** @var Environment $service */
        $service = Bootstrap::getServiceManager()->get('KmbPermission\Service\Environment');

        $this->assertInstanceOf('KmbPermission\Service\Environment', $service);
        $this->assertInstanceOf('KmbDomain\Model\EnvironmentRepositoryInterface', $service->getEnvironmentRepository());
    }
}
