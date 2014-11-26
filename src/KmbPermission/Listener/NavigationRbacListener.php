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
namespace KmbPermission\Listener;

use Zend\EventManager\EventInterface;
use Zend\Navigation\Page\AbstractPage;
use ZfcRbac\Service\AuthorizationService;
use ZfcRbac\Service\RoleService;

class NavigationRbacListener
{
    /** @var  AuthorizationService */
    protected $authorizationService;

    /** @var  RoleService */
    protected $roleService;

    /**
     * @param AuthorizationService $authorizationService
     * @param RoleService          $roleService
     */
    public function __construct(AuthorizationService $authorizationService, RoleService $roleService)
    {
        $this->authorizationService = $authorizationService;
        $this->roleService = $roleService;
    }

    public function isAllowed(EventInterface $event)
    {
        $event->stopPropagation();
        $accepted = true;

        /** @var AbstractPage $page */
        $page = $event->getParam('page');

        $permission = $page->getPermission();
        if ($permission) {
            $accepted = $this->authorizationService->isGranted($permission);
        }

        $roles = $page->get('roles');
        if ($roles) {
            $accepted = $this->roleService->matchIdentityRoles(is_array($roles) ? $roles : [$roles]);
        }

        return $accepted;
    }
}
