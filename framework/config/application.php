<?php
$config = [
    'app' => [
        'name'          => 'Placeview',
        'product'       => 'dev.placeview.in',
        'cryptSalt'     => '$9diko$.f#11',

    ],
    'databases'   => [
        'db' => [
            'adapter'  => 'Phalcon\Db\Adapter\Pdo\Mysql',
            'host'     => '148.251.19.197',
            'username' => 'placeview_info',
            'password' => 'hTCAw6Vc77xNRs5f',
            'dbname'   => 'info_placeview',
            'charset'  => 'utf8',
        ]
    ],

    'view'        => [
        'viewsDir'      => '/app/views/',
        'partialsDir'   => 'partials',
        'layoutsDir'    => 'layouts',
        'defaultLayout' => 'default',
        'pagination'    => 20
    ],
    'assets'      => [
        'dir' => '/public/assets/',
    ],
    'cache'       => [
        'prefix' => 'skelet'
    ],
    'mail'        => [
        'fromName'         => 'noreply',
        'fromEmail'        => 'noreply@domain.com',
        'smtp'             => [
            'server'   => 'MAIL_SERVER',
            'port'     => 587,
            'security' => 'tls',
            'username' => 'USERNAME',
            'password' => 'PASSWORD',
        ],
        'mandrill_api_key' => 'MANDRILL',
        'amazon'           => [
            'AWSAccessKeyId' => "ASKID",
            'AWSSecretKey'   => "ASK"
        ],
    ],
    't9n'        => [
        'path'      => '/res/t9n/',
        'default'   => 'fa',
        'available' => ['fa', 'en']
    ],
    'auth'  => [
        'defaultRole' => 'guest',
    ],
    'oAuth'       => [
        "Github"    => [ # https://github.com/settings/applications
            'id'     => 'afc32ec3948dc0e2bfea',
            'secret' => '5199f8c0ebb118380e9f04044aa741af234399f3',
        ],
        "google"    => [ # https://console.developers.google.com/project/apps~hardy-gearing-640/apiui/credential
            'id'     => '823310552823-9pa1o8m958dtr6fqr9nma2qapq540vlo.apps.googleusercontent.com',
            'secret' => 'G2dLjBgX_OLuKFs65o4M7qOH',
        ],
        'Microsoft' => [ # https://account.live.com/developers/applications
            'id'     => '000000004012584B',
            'secret' => 'hbspvkNwtQxE74oQQeJbL6KcmsJLMQr-'
        ],
        'Facebook'  => [ # https://developers.facebook.com/
            'id'     => '1431072327175669',
            'secret' => 'f519b5fa6d8dc9478b261541d7f571f2'
        ]
    ],
    'notification' => [
        'developerId' => 1
    ],
    'api' => [
        'username' => 'Atitel',
        'password' => 'rah@Te123H',
        'wsdl' => 'http://services.hamrahvas.com/SMSBuffer.asmx?wsdl'
    ],
    'lang' => 'en',
    'langs' => ['en','ru','fr']
];

return new \Phalcon\Config($config);
