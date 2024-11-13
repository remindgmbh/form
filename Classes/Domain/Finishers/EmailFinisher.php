<?php

declare(strict_types=1);

namespace Remind\Form\Domain\Finishers;

use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Form\Domain\Finishers\EmailFinisher as BaseEmailFinisher;
use TYPO3\CMS\Form\Domain\Model\FormElements\FileUpload;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

class EmailFinisher extends BaseEmailFinisher
{
    private bool $attachUploads = false;

    protected function executeInternal(): string|null
    {
        // temporarily store attachUploads option to use in initializeFluidEmail
        $this->attachUploads = (bool) $this->parseOption('attachUploads');

        // set option to false so BaseEmailFinisher skips attaching uploads
        $this->options['attachUploads'] = false;

        parent::executeInternal();

        return null;
    }

    protected function initializeFluidEmail(FormRuntime $formRuntime): FluidEmail
    {
        $mail = parent::initializeFluidEmail($formRuntime);

        if ($this->attachUploads) {
            foreach ($formRuntime->getFormDefinition()->getRenderablesRecursively() as $element) {
                if (!$element instanceof FileUpload) {
                    continue;
                }
                $files = $formRuntime[$element->getIdentifier()];
                if ($files) {
                    // $files may contain single file or multiple files
                    if (!is_array($files)) {
                        $files = [$files];
                    }

                    foreach ($files as $file) {
                        if ($file instanceof FileReference) {
                            $file = $file->getOriginalResource();
                        }
                        $mail->attach($file->getContents(), $file->getName(), $file->getMimeType());
                    }
                }
            }
        }

        return $mail;
    }
}
