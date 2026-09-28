<?php
defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

(static function (): void {
    ExtensionManagementUtility::addStaticFile(
        'hh_readable_anchor',
        'Configuration/TypoScript/',
        'Readable Anchor (lesbare Sprungmarken)'
    );
})();
