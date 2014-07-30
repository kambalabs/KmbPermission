<?php
return [
    'view_manager' => [
        'template_map' => [
            'error/403' => __DIR__ . '/../view/error/403.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo',
            ],
        ],
    ],
    'zfc_rbac' => [
        'protection_policy' => \ZfcRbac\Guard\GuardInterface::POLICY_DENY,
        'role_provider' => [
            'ZfcRbac\Role\InMemoryRoleProvider' => [
                'root' => [
                    'children' => ['admin'],
                    'permissions' => ['manageAllEnv']
                ],
                'admin' => [
                    'children' => ['user'],
                    'permissions' => ['manageEnv', 'manageEnvChildren']
                ],
                'user' => [
                    'permissions' => ['readEnv']
                ],
            ],
        ],
        'assertion_manager' => [
            'invokables' => [
                'mustBeAdminAssignedToAncestor' => 'KmbPermission\Assertion\MustBeAdminAssignedToAncestor',
                'mustBeAdminAssignedToEnvironment' => 'KmbPermission\Assertion\MustBeAdminAssignedToEnvironment',
                'mustBeAssignedToChildOrAncestor' => 'KmbPermission\Assertion\MustBeAssignedToChildOrAncestor',
            ]
        ],
        'assertion_map' => [
            'manageEnv' => 'mustBeAdminAssignedToAncestor',
            'manageEnvChildren' => 'mustBeAdminAssignedToEnvironment',
            'readEnv' => 'mustBeAssignedToChildOrAncestor',
        ],
        /**
         * Various plugin managers for guards and role providers. Each of them must follow a common
         * plugin manager config format, and can be used to create your custom objects
         */
        // 'guard_manager'               => [],
        // 'role_provider_manager'       => []
    ]
];
