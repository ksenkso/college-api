<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'api/user',
                    'extraPatterns' => [
	                    'OPTIONS' => 'options'
                    ],
                ],
	            [
		            'class' => 'yii\rest\UrlRule',
		            'controller' => 'api/auth',
		            'extraPatterns' => [
			            'DELETE' => 'delete',
			            'HEAD' => 'check',
			            'OPTIONS check' => 'options'
		            ],
	            ],
            ],
        ],
    ],

];
