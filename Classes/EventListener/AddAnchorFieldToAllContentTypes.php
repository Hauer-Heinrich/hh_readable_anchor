<?php
declare(strict_types=1);

namespace HauerHeinrich\HhReadableAnchor\EventListener;

use HauerHeinrich\HhReadableAnchor\Service\AnchorResolver;
use TYPO3\CMS\Core\Configuration\Event\AfterTcaCompilationEvent;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Fügt das Feld "Sprungmarke" auch den CTypes hinzu, die von Extensions registriert
 * werden, die NACH readable_anchor geladen werden (z. B. EXT:news mit "news_pi1").
 *
 * Das Event feuert erst, wenn alle TCA-Dateien und -Overrides aller Extensions
 * verarbeitet sind. Das Ergebnis landet anschließend im TCA-Cache, der Listener
 * läuft also nicht bei jedem Request.
 */
final class AddAnchorFieldToAllContentTypes {

    public function __invoke(AfterTcaCompilationEvent $event): void {
        $tca = $event->getTca();

        if (!isset($tca['tt_content']['columns'][AnchorResolver::FIELD])) {
            return;
        }

        // addToAllTCAtypes() arbeitet auf $GLOBALS['TCA'] -> kurz auf den Event-Stand setzen
        $hadGlobalTca = array_key_exists('TCA', $GLOBALS);
        $backup = $GLOBALS['TCA'] ?? null;
        $GLOBALS['TCA'] = $tca;

        try {
            // Typen, die das Feld schon haben, überspringt der Core automatisch
            ExtensionManagementUtility::addToAllTCAtypes(
                'tt_content',
                AnchorResolver::FIELD,
                '',
                'after:header'
            );
            $event->setTca($GLOBALS['TCA']);
        } finally {
            if ($hadGlobalTca) {
                $GLOBALS['TCA'] = $backup;
            } else {
                unset($GLOBALS['TCA']);
            }
        }
    }
}
