<?php

declare(strict_types=1);

namespace Remind\Form\Tests\Unit\Mvc\Property\TypeConverter;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;
use Remind\Form\Mvc\Property\TypeConverter\MultiUploadedFileReferenceConverter;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Form\Mvc\Property\TypeConverter\UploadedFileReferenceConverter;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(MultiUploadedFileReferenceConverter::class)]
class MultiUploadedFileReferenceConverterTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    #[Test]
    public function convertFromReturnsFirstErrorAndStopsProcessing(): void
    {
        $converter = new MultiUploadedFileReferenceConverter();

        $uploadedFileReferenceConverter = $this->createMock(UploadedFileReferenceConverter::class);
        $error = new Error('Conversion failed', 1730725801);

        $uploadedFileReferenceConverter
            ->expects(self::once())
            ->method('convertFrom')
            ->with('first-file', 'targetType', [], null)
            ->willReturn($error);

        $reflection = new ReflectionProperty(MultiUploadedFileReferenceConverter::class, 'uploadedFileReferenceConverter');
        $reflection->setValue($converter, $uploadedFileReferenceConverter);

        $result = $converter->convertFrom(['first-file', 'second-file'], 'targetType');

        self::assertSame($error, $result);
    }
}
