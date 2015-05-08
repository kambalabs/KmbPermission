<?php
/**
 * @copyright Copyright (c) 2014 Orange Applications for Business
 * @link      http://github.com/kambalabs for the sources repositories
 *
 * This file is part of Kamba.
 *
 * Kamba is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * Kamba is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Kamba.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace KmbPermission\Service;

use KmbDomain\Model;
use KmbDomain\Service\EnvironmentRepositoryInterface;
use Zend\Stdlib\ArrayUtils;
use ZfcRbac\Exception\UnauthorizedException;
use ZfcRbac\Service\AuthorizationService;
use ZfcRbac\Service\AuthorizationServiceAwareInterface;

class Environment implements AuthorizationServiceAwareInterface, EnvironmentInterface
{
    /** @var AuthorizationService */
    protected $authorizationService;

    /** @var EnvironmentRepositoryInterface */
    protected $environmentRepository;

    /**
     * Get all readable environments (for non root users).
     * Return an empty array for root users : means all environments.
     *
     * @param  Model\EnvironmentInterface $environment
     * @return Model\EnvironmentInterface[]
     * @throws UnauthorizedException   When user don't have read privilege on specified environment or
     *                                 don't have any read privilege at all.
     */
    public function getAllReadable($environment = null)
    {
        $firstLevelEnvironments = null;
        if ($environment !== null) {
            if (!$this->authorizationService->isGranted('readEnv', $environment)) {
                throw new UnauthorizedException();
            }
            $firstLevelEnvironments = [$environment];
        } elseif ($this->authorizationService->isGranted('manageAllEnv')) {
            return [];
        } else {
            /** @var Model\UserInterface $user */
            $user = $this->authorizationService->getIdentity();
            $firstLevelEnvironments = $this->environmentRepository->getAllForUser($user);
            if (empty($firstLevelEnvironments)) {
                throw new UnauthorizedException();
            }
        }

        $environments = [];
        foreach ($firstLevelEnvironments as $firstLevelEnvironment) {
            $descendants = $firstLevelEnvironment->getDescendants();
            array_unshift($descendants, $firstLevelEnvironment);
            $environments = ArrayUtils::merge($environments, $descendants);
        }
        return array_unique($environments);
    }

    /**
     * Set the AuthorizationService
     *
     * @TODO: for v3, update the interface to typehint to AuthorizationServiceInterface instead
     *
     * @param   AuthorizationService $authorizationService
     * @return  void
     */
    public function setAuthorizationService(AuthorizationService $authorizationService)
    {
        $this->authorizationService = $authorizationService;
    }

    /**
     * Set EnvironmentRepository.
     *
     * @param \KmbDomain\Service\EnvironmentRepositoryInterface $environmentRepository
     * @return Environment
     */
    public function setEnvironmentRepository($environmentRepository)
    {
        $this->environmentRepository = $environmentRepository;
        return $this;
    }

    /**
     * Get EnvironmentRepository.
     *
     * @return \KmbDomain\Service\EnvironmentRepositoryInterface
     */
    public function getEnvironmentRepository()
    {
        return $this->environmentRepository;
    }
}
