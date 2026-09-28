<?php
declare(strict_types=1);

namespace HauerHeinrich\HhReadableAnchor\Evaluation;

use HauerHeinrich\HhReadableAnchor\Service\AnchorSlugifier;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * TCA-Eval: Der Redakteur sieht nach dem Speichern direkt die URL-konforme
 * Schreibweise seiner Eingabe (z. B. "Über uns" -> "ueber-uns").
 */
final class AnchorEvaluation {
    /**
     * Serverseitige Auswertung durch den DataHandler.
     *
     * @param mixed $value
     * @param string $is_in
     * @param bool $set
     */
    public function evaluateFieldValue($value, $is_in = '', &$set = true): string {
        return GeneralUtility::makeInstance(AnchorSlugifier::class)->slugify((string)$value);
    }
}
