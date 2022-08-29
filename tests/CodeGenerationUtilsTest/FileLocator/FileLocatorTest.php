<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

declare(strict_types=1);

namespace CodeGenerationUtilsTest\FileLocator;

use CodeGenerationUtils\Exception\InvalidGeneratedClassesDirectoryException;
use CodeGenerationUtils\FileLocator\FileLocator;
use PHPUnit\Framework\TestCase;

use const DIRECTORY_SEPARATOR;

/**
 * Tests for {@see \CodeGenerationUtils\FileLocator\FileLocator}
 */
class FileLocatorTest extends TestCase
{
    /**
     * @covers \CodeGenerationUtils\FileLocator\FileLocator::__construct
     * @covers \CodeGenerationUtils\FileLocator\FileLocator::getGeneratedClassFileName
     */
    public function testGetGeneratedClassFileName(): void
    {
        $locator = new FileLocator(__DIR__);

        self::assertSame(
            __DIR__ . DIRECTORY_SEPARATOR . 'FooBarBaz.php',
            $locator->getGeneratedClassFileName('Foo\\Bar\\Baz'),
        );
        self::assertSame(
            __DIR__ . DIRECTORY_SEPARATOR . 'Foo_Bar_Baz.php',
            $locator->getGeneratedClassFileName('Foo_Bar_Baz'),
        );
    }

    /** @covers \CodeGenerationUtils\FileLocator\FileLocator::__construct */
    public function testRejectsNonExistingDirectory(): void
    {
        $this->expectException(InvalidGeneratedClassesDirectoryException::class);
        new FileLocator(__DIR__ . '/non-existing');
    }
}
