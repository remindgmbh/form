<?php

declare(strict_types=1);

namespace Remind\Form\Mvc\Property\TypeConverter;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter;
use TYPO3\CMS\Form\Mvc\Property\TypeConverter\UploadedFileReferenceConverter;

class MultiUploadedFileReferenceConverter extends AbstractTypeConverter
{
    private UploadedFileReferenceConverter $uploadedFileReferenceConverter;

    public function __construct()
    {
        $this->uploadedFileReferenceConverter = GeneralUtility::makeInstance(UploadedFileReferenceConverter::class);
    }

    /**
     * @param mixed[] $convertedChildProperties
     * @return array<\TYPO3\CMS\Core\Resource\File|\TYPO3\CMS\Core\Resource\Folder|\TYPO3\CMS\Extbase\Domain\Model\FileReference>|Error|null
     */
    public function convertFrom(
        mixed $source,
        mixed $targetType,
        array $convertedChildProperties = [],
        PropertyMappingConfigurationInterface $configuration = null
    ): array|Error|null {
        if (is_array($source)) {
            $resources = [];
            foreach ($source as $file) {
                $resource = $this->uploadedFileReferenceConverter->convertFrom($file, $targetType, $convertedChildProperties, $configuration);

                if ($resource instanceof Error) {
                    return $resource;
                }

                if ($resource) {
                    $resources[] = $resource;
                }
            }
            return $resources;
        }

        return null;
    }
}
