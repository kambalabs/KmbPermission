<?php
namespace KmbPermissionTest\Service;

use KmbDomain\Model\EnvironmentInterface;
use KmbMemoryInfrastructure\Fixtures;
use KmbPermission\Service;
use KmbPermissionTest\Bootstrap;
use Zend\ServiceManager\ServiceManager;

class EnvironmentTest extends \PHPUnit_Framework_TestCase
{
    use Fixtures;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $authorizationService;

    /** @var Service\Environment */
    protected $environmentService;

    protected function setUp()
    {
        $this->initFixtures();
        $this->authorizationService = $this->getMock('KmbPermissionTest\Service\AuthorizationService');
        $this->environmentService = new Service\Environment();
        $this->environmentService->setAuthorizationService($this->authorizationService);
        $this->environmentService->setEnvironmentRepository($this->environmentRepository);
    }

    /** @test */
    public function canGetAllReadableForSpecificEnvironment()
    {
        /** @var EnvironmentInterface $stable */
        $stable = $this->environmentRepository->getById(1);
        $this->authorizationService->expects($this->any())
            ->method('isGranted')
            ->with('readEnv', $stable)
            ->will($this->returnValue(true));

        $environments = $this->environmentService->getAllReadable($stable);

        $this->assertEquals(13, count($environments));
        $this->assertEquals($stable, $environments[0]);
    }

    /**
     * @test
     * @expectedException \ZfcRbac\Exception\UnauthorizedException
     */
    public function cannotGetAllReadableForUngrantedUserOnSpecifiedEnvironment()
    {
        /** @var EnvironmentInterface $stable */
        $stable = $this->environmentRepository->getById(1);
        $this->authorizationService->expects($this->any())
            ->method('isGranted')
            ->with('readEnv', $stable)
            ->will($this->returnValue(false));

        $this->environmentService->getAllReadable($stable);
    }

    /**
     * @test
     * @expectedException \ZfcRbac\Exception\UnauthorizedException
     */
    public function cannotGetAllReadableForUngrantedUserWithoutEnvironment()
    {
        $madams = $this->userRepository->getById(5);
        $this->authorizationService->expects($this->any())
            ->method('getIdentity')
            ->will($this->returnValue($madams));

        $this->environmentService->getAllReadable();
    }

    /** @test */
    public function canGetAllReadableWithoutEnvironmentForAdmin()
    {
        $psmith = $this->userRepository->getById(3);
        $this->authorizationService->expects($this->any())
            ->method('getIdentity')
            ->will($this->returnValue($psmith));

        $environments = $this->environmentService->getAllReadable();

        $this->assertEquals(12, count($environments));
        $this->assertEquals($this->environmentRepository->getById(4), $environments[0]);
    }

    /** @test */
    public function canGetAllReadableWithoutEnvironmentForRoot()
    {
        $this->authorizationService->expects($this->any())
            ->method('isGranted')
            ->with('manageAllEnv')
            ->will($this->returnValue(true));

        $environments = $this->environmentService->getAllReadable();

        $this->assertEquals([], $environments);
    }

    /**
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return Bootstrap::getServiceManager();
    }
}
