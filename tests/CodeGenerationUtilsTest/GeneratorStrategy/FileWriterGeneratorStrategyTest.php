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

namespace CodeGenerationUtilsTest\GeneratorStrategy;

use CodeGenerationUtils\FileLocator\FileLocatorInterface;
use CodeGenerationUtils\GeneratorStrategy\FileWriterGeneratorStrategy;
use CodeGenerationUtils\Inflector\Util\UniqueIdentifierGenerator;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PHPUnit\Framework\TestCase;

/**
 * Tests for {@see \CodeGenerationUtils\GeneratorStrategy\FileWriterGeneratorStrategy}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class FileWriterGeneratorStrategyTest extends TestCase
{
    /**
     * @covers \CodeGenerationUtils\GeneratorStrategy\FileWriterGeneratorStrategy::__construct
     * @covers \CodeGenerationUtils\GeneratorStrategy\FileWriterGeneratorStrategy::generate
     */
    public function testGenerate()
    {
        /* @var $locator \PHPUnit_Framework_MockObject_MockObject|FileLocatorInterface */
        $locator   = $this->createMock(FileLocatorInterface::class);
        $generator = new FileWriterGeneratorStrategy($locator);
        $tmpFile   = sys_get_temp_dir() . '/FileWriterGeneratorStrategyTest' . uniqid('', true) . '.php';
        $className = UniqueIdentifierGenerator::getIdentifier('Bar');
        $fqcn      = 'Foo\\' . $className;

        $locator
            ->expects(self::any())
            ->method('getGeneratedClassFileName')
            ->with($fqcn)
            ->willReturn($tmpFile);

        $class     = new Class_($className);
        $namespace = new Namespace_(new Name('Foo'), array($class));
        $body      = $generator->generate(array($namespace));

        self::assertGreaterThan(0, strpos($body, $className));
        self::assertFalse(class_exists($fqcn, false));
        self::assertFileExists($tmpFile);

        require $tmpFile;

        self::assertTrue(class_exists($fqcn, false));
    }
}
