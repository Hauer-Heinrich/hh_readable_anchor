<?php
declare(strict_types=1);

namespace HauerHeinrich\HhReadableAnchor\Service;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Ermittelt die Anker-ID eines Inhaltselements:
 *
 *  1) Redakteur hat das Feld "Sprungmarke" befüllt  -> dieser Wert (URL-konform)
 *  2) sonst: Überschrift ("header") vorhanden        -> Slug aus der Überschrift
 *  3) sonst                                          -> TYPO3-Standard "c<uid>"
 *
 * Doppelte Sprungmarken innerhalb eines Requests (= eines HTML-Dokuments)
 * werden durch ein Suffix eindeutig gemacht: "kontakt", "kontakt-2", ...
 * Pro Element wird das Ergebnis gecacht, damit z. B. Abschnittsmenü und
 * Inhaltselement immer dieselbe ID erhalten.
 */
final class AnchorResolver implements SingletonInterface {
    public const FIELD = 'tx_hhreadableanchor_anchor';

    private const DEFAULT_CONFIGURATION = [
        'prefix' => '',
        'reservedIds' => 'top,main,content,header,footer,navigation,nav,menu,page',
        'maxLength' => 80,
    ];

    /** TYPO3-Standard: header_layout 100 = "Verborgen" (Überschrift nur im Backend) */
    private const HEADER_LAYOUT_HIDDEN = 100;

    /** @var array<string, int> vergebene Anker => Record-Key */
    private array $usedAnchors = [];

    /** @var array<int, string> Record-Key => ermittelter Anker */
    private array $resolved = [];

    private ?array $configuration = null;

    public function __construct(
        private readonly AnchorSlugifier $slugifier,
        private readonly ExtensionConfiguration $extensionConfiguration,
    ) {}

    /**
     * @param array $record Datensatz aus tt_content (z. B. {data} im Fluid-Template)
     */
    public function resolve(array $record): string {
        $uid = (int)($record['uid'] ?? 0);
        $legacyAnchor = 'c' . $uid;

        // Bei Übersetzungen (Overlay) ist uid die Default-UID, _LOCALIZED_UID die übersetzte
        $recordKey = (int)($record['_LOCALIZED_UID'] ?? $uid);

        if (isset($this->resolved[$recordKey])) {
            return $this->resolved[$recordKey];
        }

        $configuration = $this->getConfiguration();

        // 1) eigenes Feld
        $base = $this->slugifier->slugify((string)($record[self::FIELD] ?? ''), $configuration['maxLength']);

        // 2) Überschrift
        if ($base === '' && !$this->isHeaderHidden($record)) {
            $base = $this->slugifier->slugify((string)($record['header'] ?? ''), $configuration['maxLength']);
        }

        // 3) TYPO3-Standard
        if ($base === '') {
            return $this->resolved[$recordKey] = $legacyAnchor;
        }

        $base = $configuration['prefix'] . $base;
        $anchor = $base;
        $counter = 2;
        while ($this->isTaken($anchor, $recordKey)) {
            $anchor = $base . '-' . $counter++;
        }

        $this->usedAnchors[$anchor] = $recordKey;

        return $this->resolved[$recordKey] = $anchor;
    }

    private function isTaken(string $anchor, int $recordKey): bool {
        if (isset($this->usedAnchors[$anchor])) {
            return $this->usedAnchors[$anchor] !== $recordKey;
        }

        if (in_array($anchor, $this->getConfiguration()['reservedIds'], true)) {
            return true;
        }

        // Darf nicht mit den weiterhin ausgegebenen Standard-Ankern "c<uid>" kollidieren
        return preg_match('/^c\d+$/', $anchor) === 1;
    }

    /**
     * @return array{prefix: string, reservedIds: string[], maxLength: int}
     */
    private function getConfiguration(): array {
        if ($this->configuration !== null) {
            return $this->configuration;
        }

        try {
            $configuration = (array)$this->extensionConfiguration->get('hh_readable_anchor');
        } catch (\Throwable) {
            $configuration = [];
        }
        $configuration = array_merge(self::DEFAULT_CONFIGURATION, $configuration);

        return $this->configuration = [
            'prefix' => (string)preg_replace('/[^a-z0-9_-]/', '', strtolower((string)$configuration['prefix'])),
            'reservedIds' => GeneralUtility::trimExplode(',', strtolower((string)$configuration['reservedIds']), true),
            'maxLength' => max(0, (int)$configuration['maxLength']),
        ];
    }

    private function isHeaderHidden(array $record): bool {
        return (int)($record['header_layout'] ?? 0) === self::HEADER_LAYOUT_HIDDEN;
    }
}
