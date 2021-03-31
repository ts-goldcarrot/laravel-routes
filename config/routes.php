<?php

return [
    'namespace' => 'App\Http\Controllers',

    'modules' => [
        'api' => [
            'extendNamespaceFromFolders' => true,
            'extendPrefixFromFolders' => true,

            'namespace' => 'Api',
            'directory' => 'api',
            'prefix' => 'api',
            'middleware' => ['api'],
        ]
    ],

];
