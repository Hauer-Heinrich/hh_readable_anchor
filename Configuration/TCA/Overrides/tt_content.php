<?php
defined('TYPO3') or die();

use HauerHeinrich\HhReadableAnchor\Evaluation\AnchorEvaluation;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

(static function (): void {
    $ll = 'LLL:EXT:hh_readable_anchor/Resources/Private/Language/locallang_db.xlf:';

    ExtensionManagementUtility::addTCAcolumns('tt_content', [
        'tx_hhreadableanchor_anchor' => [
            'exclude' => true,
            'label' => $ll . 'tt_content.tx_hhreadableanchor_anchor',
            'description' => $ll . 'tt_content.tx_hhreadableanchor_anchor.description',
            'config' => [
                'type' => 'input',
                'size' => 40,
                'max' => 100,
                'eval' => 'trim,' . AnchorEvaluation::class,
                'placeholder' => $ll . 'tt_content.tx_hhreadableanchor_anchor.placeholder',
            ],
        ],
    ]);

    // Für alle bis hierhin bekannten CTypes, direkt hinter der Überschriften-Palette.
    // CTypes von später geladenen Extensions (z. B. news_pi1) ergänzt der Listener
    // Classes/EventListener/AddAnchorFieldToAllContentTypes.php
    ExtensionManagementUtility::addToAllTCAtypes(
        'tt_content',
        'tx_hhreadableanchor_anchor',
        '',
        'after:header'
    );
})();
