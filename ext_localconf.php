<?php
defined('TYPO3') || die();

(static function () {
    if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['template_stats_cache'] ?? null)) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['template_stats_cache']['backend'] = \TYPO3\CMS\Core\Cache\Backend\NullBackend::class;;
    }
})();
