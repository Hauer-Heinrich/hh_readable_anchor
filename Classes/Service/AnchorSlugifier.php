<?php
declare(strict_types=1);

namespace HauerHeinrich\HhReadableAnchor\Service;

use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Wandelt beliebigen Text in eine lesbare, URL- und HTML-ID-konforme Sprungmarke um.
 * Nutzt intern den SlugHelper des TYPO3-Core (gleiche Logik wie bei Seiten-Slugs),
 * z. B. "Über uns & Team" -> "ueber-uns-team".
 */
final class AnchorSlugifier {
    public function slugify(string $value, int $maxLength = 0): string {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        // Zeichen, die in einem Fragment/ID stören würden, vorab zu Leerzeichen machen
        $value = str_replace(['/', '\\', '#', '.', '?', '&'], ' ', $value);

        $slugHelper = GeneralUtility::makeInstance(
            SlugHelper::class,
            'tt_content',
            'tx_hhreadableanchor_anchor',
            [
                'fallbackCharacter' => '-',
                'prependSlash' => false,
            ]
        );

        $slug = $slugHelper->sanitize($value);

        // Sicherheitshalber: keine Slashes, keine doppelten/umschließenden Bindestriche
        $slug = str_replace('/', '-', $slug);
        $slug = (string)preg_replace('/-{2,}/', '-', $slug);
        $slug = trim($slug, '-');

        if ($maxLength > 0 && mb_strlen($slug) > $maxLength) {
            $slug = rtrim(mb_substr($slug, 0, $maxLength), '-');
        }

        return $slug;
    }
}
