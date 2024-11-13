<?php

declare(strict_types=1);

namespace Remind\Form\Mvc\Property;

use Remind\Form\Mvc\Property\TypeConverter\MultiUploadedFileReferenceConverter;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Model\FormElements\FileUpload;
use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;

class PropertyMappingConfiguration
{
    public function afterBuildingFinished(RenderableInterface $renderable): void
    {
        if (
            $renderable instanceof FileUpload &&
            !empty($renderable->getProperties()['multiple'])
        ) {
            $typeConverter = GeneralUtility::makeInstance(MultiUploadedFileReferenceConverter::class);
            $renderable
                ->getRootForm()
                ->getProcessingRule($renderable->getIdentifier())
                ->getPropertyMappingConfiguration()
                ->setTypeConverter($typeConverter);
        }
    }
}
