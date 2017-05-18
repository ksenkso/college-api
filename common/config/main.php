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
            'enableStrictParsing' => true,
            'rules' => [
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



            ],
        ],
    ],

];
