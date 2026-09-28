<?php
declare(strict_types=1);

namespace HauerHeinrich\HhReadableAnchor\ViewHelpers;

use HauerHeinrich\HhReadableAnchor\Service\AnchorResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Liefert die Anker-ID eines Inhaltselements.
 *
 * Beispiele:
 *   <div id="{ra:anchor(data: data)}"> ... </div>
 *   <a href="{page.link}#{ra:anchor(data: element.data)}">...</a>
 */
final class AnchorViewHelper extends AbstractViewHelper {
    public function __construct(
        private readonly AnchorResolver $anchorResolver,
    ) {}

    public function initializeArguments(): void {
        $this->registerArgument('data', 'array', 'Datensatz des Inhaltselements (tt_content)', true);
    }

    public function render(): string {
        return $this->anchorResolver->resolve((array)$this->arguments['data']);
    }
}
