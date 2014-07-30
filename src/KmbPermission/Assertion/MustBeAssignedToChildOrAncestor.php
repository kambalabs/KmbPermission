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
namespace KmbPermission\Assertion;

use KmbDomain\Model\EnvironmentInterface;
use KmbDomain\Model\UserInterface;
use ZfcRbac\Assertion\AssertionInterface;
use ZfcRbac\Service\AuthorizationService;

class MustBeAssignedToChildOrAncestor implements AssertionInterface
{
    /**
     * Check if this assertion is true
     *
     * @param  AuthorizationService $authorizationService
     * @param  mixed                $context
     * @return bool
     */
    public function assert(AuthorizationService $authorizationService, $context = null)
    {
        /** @var UserInterface $identity */
        $identity = $authorizationService->getIdentity();

        /** @var EnvironmentInterface $context */
        if ($context->hasUser($identity) || $authorizationService->isGranted('manageEnv', $context)) {
            return true;
        }

        return $this->canReadOneAncestor($identity, $context) || $this->canReadAtLeastOneChild($identity, $context);
    }

    /**
     * @param UserInterface $identity
     * @param EnvironmentInterface $environment
     * @return bool
     */
    protected function canReadOneAncestor($identity, $environment)
    {
        if (!$environment->hasParent()) {
            return false;
        }
        if ($environment->getParent()->hasUser($identity)) {
            return true;
        }
        return $this->canReadOneAncestor($identity, $environment->getParent());
    }

    /**
     * @param UserInterface $identity
     * @param EnvironmentInterface $environment
     * @return bool
     */
    protected function canReadAtLeastOneChild($identity, $environment)
    {
        if (!$environment->hasChildren()) {
            return false;
        }
        $children = $environment->getChildren();
        foreach ($children as $child) {
            /** @var EnvironmentInterface $child */
            if ($child->hasUser($identity) || $this->canReadAtLeastOneChild($identity, $child)) {
                return true;
            }
        }
        return false;
    }
}
