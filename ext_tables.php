<?php

// Module System > Backend Users
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
    'Gridtocontainer',
    'system',
    'gridtocontainer',
    'top',
    [
        \NP\Gridtocontainer\Controller\MigrationController::class => 'start,process',
    ],
    [
        'access' => 'admin',
        'icon' => 'EXT:gridtocontainer/Resources/Public/Icons/Extension.svg',
        'labels' => 'LLL:EXT:gridtocontainer/Resources/Private/Language/locallang_mod.xlf',
    ]
);
