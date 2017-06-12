<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'authManager' => [
	        'class' => 'yii\rbac\DbManager',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
	            [
		            'class' => 'yii\rest\UrlRule',
		            'controller' => 'v1/user-meta',
		            'pluralize' => false,
		            'extraPatterns' => [
		            	'GET,OPTIONS by-type/<type:\d+>' => 'by-type',
			            'GET,OPTIONS by-type/<type:\d+>/<user_id:\d+>' => 'by-type',
			            'GET,OPTIONS <user_id:\d+>' => 'view',
		            	'POST,OPTIONS batch' => 'batch',

		            ],
	            ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/user',
                    'pluralize' => false
                ],
	            [
		            'class' => 'yii\rest\UrlRule',
		            'controller' => 'v1/menu',
		            'pluralize' => false
	            ],
	            [
		            'class' => 'yii\rest\UrlRule',
		            'controller' => 'v1/group',
		            'pluralize' => false
	            ],
	            [
		            'class' => 'yii\rest\UrlRule',
		            'controller' => 'v1/auth',
		            'extraPatterns' => [
		            	'GET,OPTIONS check/<name:\w+>' => 'check',
		            ],
		            'pluralize' => false
	            ],
	            [
		            'class' => 'yii\rest\UrlRule',
		            'controller' => 'v1/student',
		            'extraPatterns' => [

		            ],
		            'pluralize' => false
	            ],
	            [
		            'class' => 'yii\rest\UrlRule',
		            'controller' => 'v1/event',
		            'extraPatterns' => [
						'GET,OPTIONS <year:\d+>/<month\d+>/<day:\d+>' => 'index',
						'GET,OPTIONS by-type/<type:\d+>' => 'by-type',
						'POST,OPTIONS batch' => 'batch',
		            ],
		            'pluralize' => false
	            ],
	            [
		            'class' => 'yii\rest\UrlRule',
		            'controller' => 'v1/event-type',
		            'pluralize' => false
	            ],
	            [
		            'class' => 'yii\rest\UrlRule',
		            'controller' => 'v1/document',
		            'pluralize' => false,
		            'extraPatterns' => [
			            'GET,OPTIONS <type_id:\d+>/<meta_id:\d+>' => 'view',
			            'GET,OPTIONS <type_id:\d+>' => 'view',
		            ]
	            ],
	            [
		            'class' => 'yii\rest\UrlRule',
		            'controller' => 'v1/portfolio',
		            'pluralize' => false,
		            'extraPatterns' => [
			            'GET,OPTIONS <user_id:\d+>/<type_id:\d+>' => 'view',
			            'POST,OPTIONS upload/<user_id:\d+>' => 'upload',
		            ]
	            ],
	            [
		            'class' => 'yii\rest\UrlRule',
		            'controller' => 'v1/attachment',
		            'pluralize' => false,
		            'extraPatterns' => [
			            'GET,OPTIONS <user_id:\d+>/<type_id:\d+>' => 'view',
			            'POST,OPTIONS <user_id:\d+>/<type:\d+>' => 'create',
		            ]
	            ],
	            [
		            'class' => 'yii\rest\UrlRule',
		            'controller' => 'v1/protocol',
		            'pluralize' => false,
		            'extraPatterns' => [
			            'GET,OPTIONS by-type/<type:\d+>' => 'by-type',
		            ]
	            ],
	            [
		            'class' => 'yii\rest\UrlRule',
		            'controller' => 'v1/protocol-type',
		            'pluralize' => false,
	            ],
	            [
		            'class' => 'yii\rest\UrlRule',
		            'controller' => 'v1/family',
		            'pluralize' => false,
		            'extraPatterns' => [
		            	'GET,OPTIONS by-user/<user_id:\d+>' => 'by-user',
		            	'GET,OPTIONS by-type/<type:\d+>' => 'by-type'
		            ]
	            ],
	            [
		            'class' => 'yii\rest\UrlRule',
		            'controller' => 'v1/spec',
		            'pluralize' => false,
	            ],
            ],
        ],
    ],

];
