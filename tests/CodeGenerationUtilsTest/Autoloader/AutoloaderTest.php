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

namespace CodeGenerationUtilsTest\Autoloader;

use CodeGenerationUtils\Autoloader\Autoloader;
use CodeGenerationUtils\FileLocator\FileLocatorInterface;
use CodeGenerationUtils\Inflector\ClassNameInflectorInterface;
use CodeGenerationUtils\Inflector\Util\UniqueIdentifierGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function class_exists;
use function file_put_contents;
use function sys_get_temp_dir;
use function uniqid;

/**
 * Tests for {@see \CodeGenerationUtils\Autoloader\Autoloader}
 */
class AutoloaderTest extends TestCase
{
    protected Autoloader $autoloader;

    /** @var FileLocatorInterface&MockObject */
    protected $fileLocator;

    /** @var ClassNameInflectorInterface&MockObject */
    protected $classNameInflector;

    /**
     * @covers \CodeGenerationUtils\Autoloader\Autoloader::__construct
     */
    public function setUp(): void
    {
        $this->fileLocator        = $this->createMock(FileLocatorInterface::class);
        $this->classNameInflector = $this->createMock(ClassNameInflectorInterface::class);
        $this->autoloader         = new Autoloader($this->fileLocator, $this->classNameInflector);
    }

    /**
     * @covers \CodeGenerationUtils\Autoloader\Autoloader::__invoke
     */
    public function testWillNotAutoloadUserClasses(): void
    {
        $className = 'Foo\\' . UniqueIdentifierGenerator::getIdentifier('Bar');
        $this
            ->classNameInflector
            ->expects(self::once())
            ->method('isGeneratedClassName')
            ->with($className)
            ->willReturn(false);

        self::assertFalse($this->autoloader->__invoke($className));
    }

    /**
     * @covers \CodeGenerationUtils\Autoloader\Autoloader::__invoke
     */
    public function testWillNotAutoloadNonExistingClass(): void
    {
        $className = 'Foo\\' . UniqueIdentifierGenerator::getIdentifier('Bar');

        $this
            ->classNameInflector
            ->expects(self::once())
            ->method('isGeneratedClassName')
            ->with($className)
            ->willReturn(true);

        $this
            ->fileLocator
            ->expects(self::once())
            ->method('getGeneratedClassFileName')
            ->willReturn(__DIR__ . '/non-existing');

        self::assertFalse($this->autoloader->__invoke($className));
    }

    /**
     * @covers \CodeGenerationUtils\Autoloader\Autoloader::__invoke
     */
    public function testWillNotAutoloadExistingClass(): void
    {
        self::assertFalse($this->autoloader->__invoke(self::class));
    }

    /**
     * @covers \CodeGenerationUtils\Autoloader\Autoloader::__invoke
     */
    public function testWillAutoloadExistingFile(): void
    {
        $namespace = 'Foo';
        $className = UniqueIdentifierGenerator::getIdentifier('Bar');
        $fqcn      = $namespace . '\\' . $className;
        $fileName  = sys_get_temp_dir() . '/foo_' . uniqid('', true) . '.php';

        file_put_contents($fileName, '<?php namespace ' . $namespace . '; class ' . $className . '{}');

        $this
            ->classNameInflector
            ->expects(self::once())
            ->method('isGeneratedClassName')
            ->with($fqcn)
            ->willReturn(true);

        $this
            ->fileLocator
            ->expects(self::once())
            ->method('getGeneratedClassFileName')
            ->willReturn($fileName);

        self::assertTrue($this->autoloader->__invoke($fqcn));
        self::assertTrue(class_exists($fqcn, false));
    }
}
