<?php

declare(strict_types=1);

namespace Remind\Form\Tests\Unit\Domain\Finishers;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Remind\Form\Domain\Finishers\EmailFinisher;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Information\Typo3Information;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Fluid\View\FluidViewAdapter;
use TYPO3\CMS\Form\Domain\Finishers\FinisherContext;
use TYPO3\CMS\Form\Domain\Finishers\FinisherVariableProvider;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Model\FormElements\FileUpload;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;

#[CoversClass(EmailFinisher::class)]
class EmailFinisherTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['TYPO3_CONF_VARS']['MAIL'] = [];
    }

    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();

        parent::tearDown();
    }

    #[Test]
    public function initializeFluidEmailAttachesAllFilesForMultipleUploadsWhenEnabled(): void
    {
        $fileUpload = $this->createMock(FileUpload::class);
        $fileUpload
            ->method('getIdentifier')
            ->willReturn('uploadField');

        $formDefinition = $this->createMock(FormDefinition::class);
        $formDefinition
            ->method('getRenderablesRecursively')
            ->willReturn([$fileUpload]);

        $uploadedFileA = new class {
            public function getContents(): string
            {
                return 'content-a';
            }

            public function getName(): string
            {
                return 'a.pdf';
            }

            public function getMimeType(): string
            {
                return 'application/pdf';
            }
        };

        $uploadedFileB = new class {
            public function getContents(): string
            {
                return 'content-b';
            }

            public function getName(): string
            {
                return 'b.png';
            }

            public function getMimeType(): string
            {
                return 'image/png';
            }
        };

        $formRuntime = $this->createMock(FormRuntime::class);
        $formRuntime
            ->method('getFormDefinition')
            ->willReturn($formDefinition);
        $formRuntime
            ->method('offsetGet')
            ->with('uploadField')
            ->willReturn([$uploadedFileA, $uploadedFileB]);

        $request = $this->createMock(Request::class);
        $request
            ->method('getAttribute')
            ->with('normalizedParams')
            ->willReturn($this->createMock(NormalizedParams::class));

        $finisherContext = $this->createMock(FinisherContext::class);
        $finisherContext
            ->method('getRequest')
            ->willReturn($request);
        $finisherContext
            ->method('getFinisherVariableProvider')
            ->willReturn(new FinisherVariableProvider());

        $viewHelperVariableContainer = new ViewHelperVariableContainer();
        $renderingContext = $this->createMock(RenderingContextInterface::class);
        $renderingContext
            ->method('getViewHelperVariableContainer')
            ->willReturn($viewHelperVariableContainer);

        $fluidViewAdapter = $this->createMock(FluidViewAdapter::class);
        $fluidViewAdapter
            ->method('getRenderingContext')
            ->willReturn($renderingContext);

        $viewFactory = $this->createMock(ViewFactoryInterface::class);
        $viewFactory
            ->expects(self::once())
            ->method('create')
            ->with(self::isInstanceOf(ViewFactoryData::class))
            ->willReturn($fluidViewAdapter);

        GeneralUtility::addInstance(ViewFactoryInterface::class, $viewFactory);
        GeneralUtility::addInstance(Typo3Information::class, $this->createMock(Typo3Information::class));

        $finisher = new TestableEmailFinisher();
        $finisher->setOptions([
            'templateName' => 'Default',
        ]);
        $finisher->setFinisherContextForTest($finisherContext);
        $finisher->setAttachUploadsForTest(true);

        $mail = $finisher->initializeFluidEmailProxy($formRuntime);

        self::assertCount(2, $mail->getAttachments());
        self::assertSame('a.pdf', $mail->getAttachments()[0]->getFilename());
        self::assertSame('b.png', $mail->getAttachments()[1]->getFilename());
    }
}
