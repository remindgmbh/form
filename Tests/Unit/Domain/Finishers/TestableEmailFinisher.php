<?php

declare(strict_types=1);

namespace Remind\Form\Tests\Unit\Domain\Finishers;

use ReflectionProperty;
use Remind\Form\Domain\Finishers\EmailFinisher;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Form\Domain\Finishers\FinisherContext;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

class TestableEmailFinisher extends EmailFinisher
{
    public function initializeFluidEmailProxy(FormRuntime $formRuntime): FluidEmail
    {
        return $this->initializeFluidEmail($formRuntime);
    }

    public function setFinisherContextForTest(FinisherContext $finisherContext): void
    {
        $this->finisherContext = $finisherContext;
    }

    public function setAttachUploadsForTest(bool $attachUploads): void
    {
        $reflection = new ReflectionProperty(EmailFinisher::class, 'attachUploads');
        $reflection->setValue($this, $attachUploads);
    }
}
