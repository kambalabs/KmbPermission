<?php
namespace KmbPermissionTest\Listener;

use KmbPermissionTest\Bootstrap;

class NavigationRbacListenerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        $service = Bootstrap::getServiceManager()->get('KmbPermission\Listener\NavigationRbacListener');

        $this->assertInstanceOf('KmbPermission\Listener\NavigationRbacListener', $service);
    }
}
