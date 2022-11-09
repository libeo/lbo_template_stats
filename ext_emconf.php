<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Template Stats BE',
    'description' => 'Permet de visualiser quelles pages utilisent un backend_layout donné.',
    'category' => 'module',
    'author' => '',
    'author_email' => '',
    'state' => 'alpha',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-11.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
