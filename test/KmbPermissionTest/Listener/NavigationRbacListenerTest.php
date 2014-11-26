<?php
namespace KmbPermissionTest\Listener;

use KmbPermission\Listener\NavigationRbacListener;
use Zend\EventManager\Event;
use Zend\Navigation\Page\Mvc;

class NavigationRbacListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $authorizationService;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $roleService;

    /** @var  NavigationRbacListener */
    protected $listener;

    protected function setUp()
    {
        $this->authorizationService = $this->getMock('ZfcRbac\Service\AuthorizationService', ['isGranted'], [], '', false);
        $this->roleService = $this->getMock('ZfcRbac\Service\RoleService', ['matchIdentityRoles'], [], '', false);
        $this->listener = new NavigationRbacListener($this->authorizationService, $this->roleService);
    }

    /** @test */
    public function canBeAllowedWhenNothingSpecified()
    {
        $this->assertTrue($this->listener->isAllowed(new Event('isAllowed', null, ['page' => new Mvc()])));

    }

    /** @test */
    public function cannotBeAllowedWhenHasNotPermission()
    {
        $this->authorizationService->expects($this->any())
            ->method('isGranted')
            ->will($this->returnValue(false));
        $page = new Mvc();
        $page->setPermission('manageAllEnv');
        $event = new Event('isAllowed', null, ['page' => $page]);

        $this->assertFalse($this->listener->isAllowed($event));
    }

    /** @test */
    public function canBeAllowedWhenHasPermission()
    {
        $this->authorizationService->expects($this->any())
            ->method('isGranted')
            ->will($this->returnValue(true));
        $page = new Mvc();
        $page->setPermission('manageAllEnv');
        $event = new Event('isAllowed', null, ['page' => $page]);

        $this->assertTrue($this->listener->isAllowed($event));
    }

    /** @test */
    public function cannotBeAllowedWhenHasNotRoles()
    {
        $this->roleService->expects($this->any())
            ->method('matchIdentityRoles')
            ->will($this->returnValue(false));
        $page = new Mvc();
        $page->setOptions(['roles' => ['developer', 'admin']]);
        $event = new Event('isAllowed', null, ['page' => $page]);

        $this->assertFalse($this->listener->isAllowed($event));
    }

    /** @test */
    public function canBeAllowedWhenHasRole()
    {
        $this->roleService->expects($this->any())
            ->method('matchIdentityRoles')
            ->will($this->returnValue(true));
        $page = new Mvc();
        $page->setOptions(['roles' => 'admin']);
        $event = new Event('isAllowed', null, ['page' => $page]);

        $this->assertTrue($this->listener->isAllowed($event));
    }
}
