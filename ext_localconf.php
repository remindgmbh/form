<?php

declare(strict_types=1);

use Remind\Form\Mvc\Property\PropertyMappingConfiguration;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die;

(function (): void {
    ExtensionManagementUtility::addTypoScript(
        'rmnd_form',
        'setup',
        "@import 'EXT:rmnd_form/Configuration/TypoScript/setup.typoscript'"
    );

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterBuildingFinished'][1731395231]
        = PropertyMappingConfiguration::class;
})();
