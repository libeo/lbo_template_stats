<?php
defined('TYPO3') || die();

(static function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'LboTemplateStats',
        'web',
        'templatestats',
        '',
        [
            \Libeo\LboTemplateStats\Controller\BackendLayoutController::class => 'list',
        ],
        [
            'access' => 'user,group',
            'icon'   => 'EXT:lbo_template_stats/Resources/Public/Icons/user_mod_templatestats.svg',
            'labels' => 'LLL:EXT:lbo_template_stats/Resources/Private/Language/locallang_templatestats.xlf',
        ]
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_lbotemplatestats_domain_model_backendlayout', 'EXT:lbo_template_stats/Resources/Private/Language/locallang_csh_tx_lbotemplatestats_domain_model_backendlayout.xlf');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_lbotemplatestats_domain_model_backendlayout');
})();
