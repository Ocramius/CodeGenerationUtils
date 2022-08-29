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

namespace CodeGenerationUtilsTest\Inflector;

use CodeGenerationUtils\Inflector\ClassNameInflector;
use CodeGenerationUtils\Inflector\ClassNameInflectorInterface;
use PHPUnit\Framework\TestCase;

/**
 * Tests for {@see \CodeGenerationUtils\Inflector\ClassNameInflector}
 *
 * @covers \CodeGenerationUtils\Inflector\ClassNameInflector
 */
class ClassNameInflectorTest extends TestCase
{
    /** @dataProvider getClassNames */
    public function testInflector(string $realClassName, string $generatedClassName): void
    {
        $inflector = new ClassNameInflector('GeneratedClassNS');

        self::assertFalse($inflector->isGeneratedClassName($realClassName));
        self::assertTrue($inflector->isGeneratedClassName($generatedClassName));
        self::assertStringMatchesFormat($realClassName, $inflector->getUserClassName($realClassName));
        self::assertStringMatchesFormat($generatedClassName, $inflector->getGeneratedClassName($generatedClassName));
        self::assertStringMatchesFormat($generatedClassName, $inflector->getGeneratedClassName($realClassName));
        self::assertStringMatchesFormat($realClassName, $inflector->getUserClassName($generatedClassName));
    }

    public function testGeneratesSameClassNameWithSameParameters(): void
    {
        $inflector = new ClassNameInflector('GeneratedClassNS');

        self::assertSame($inflector->getGeneratedClassName('Foo\\Bar'), $inflector->getGeneratedClassName('Foo\\Bar'));
        self::assertSame(
            $inflector->getGeneratedClassName('Foo\\Bar', ['baz' => 'tab']),
            $inflector->getGeneratedClassName('Foo\\Bar', ['baz' => 'tab']),
        );
        self::assertSame(
            $inflector->getGeneratedClassName('Foo\\Bar', ['tab' => 'baz']),
            $inflector->getGeneratedClassName('Foo\\Bar', ['tab' => 'baz']),
        );
    }

    public function testGeneratesDifferentClassNameWithDifferentParameters(): void
    {
        $inflector = new ClassNameInflector('GeneratedClassNS');

        self::assertNotSame(
            $inflector->getGeneratedClassName('Foo\\Bar'),
            $inflector->getGeneratedClassName('Foo\\Bar', ['foo' => 'bar']),
        );
        self::assertNotSame(
            $inflector->getGeneratedClassName('Foo\\Bar', ['baz' => 'tab']),
            $inflector->getGeneratedClassName('Foo\\Bar', ['tab' => 'baz']),
        );
        self::assertNotSame(
            $inflector->getGeneratedClassName('Foo\\Bar', ['foo' => 'bar', 'tab' => 'baz']),
            $inflector->getGeneratedClassName('Foo\\Bar', ['foo' => 'bar']),
        );
        self::assertNotSame(
            $inflector->getGeneratedClassName('Foo\\Bar', ['foo' => 'bar', 'tab' => 'baz']),
            $inflector->getGeneratedClassName('Foo\\Bar', ['tab' => 'baz', 'foo' => 'bar']),
        );
    }

    /** @psalm-return non-empty-list<array{string, string}> */
    public function getClassNames(): array
    {
        return [
            [
                'Foo',
                'GeneratedClassNS\\' . ClassNameInflectorInterface::GENERATED_CLASS_MARKER . '\\Foo\\%s',
            ],
            [
                'Foo\\Bar',
                'GeneratedClassNS\\' . ClassNameInflectorInterface::GENERATED_CLASS_MARKER . '\\Foo\\Bar\\%s',
            ],
        ];
    }
}
