<?php
defined('TYPO3') or die();

// Eigene Eval-Regel: macht die Redakteurs-Eingabe beim Speichern URL-konform
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals'][\HauerHeinrich\HhReadableAnchor\Evaluation\AnchorEvaluation::class] = '';

// Globaler Fluid-Namespace "ra" -> <ra:anchor data="{data}" /> ohne xmlns-Deklaration nutzbar
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['ra'][] = 'HauerHeinrich\\HhReadableAnchor\\ViewHelpers';
