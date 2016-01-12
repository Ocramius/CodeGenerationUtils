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

use PHPUnit_Framework_TestCase;
use CodeGenerationUtils\Inflector\ClassNameInflector;
use CodeGenerationUtils\Inflector\ClassNameInflectorInterface;

/**
 * Tests for {@see \CodeGenerationUtils\Inflector\ClassNameInflector}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassNameInflectorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getClassNames
     *
     * @covers \CodeGenerationUtils\Inflector\ClassNameInflector::__construct
     * @covers \CodeGenerationUtils\Inflector\ClassNameInflector::getUserClassName
     * @covers \CodeGenerationUtils\Inflector\ClassNameInflector::getGeneratedClassName
     * @covers \CodeGenerationUtils\Inflector\ClassNameInflector::isGeneratedClassName
     */
    public function testInflector($realClassName, $generatedClassName)
    {
        $inflector = new ClassNameInflector('GeneratedClassNS');

        self::assertFalse($inflector->isGeneratedClassName($realClassName));
        self::assertTrue($inflector->isGeneratedClassName($generatedClassName));
        self::assertStringMatchesFormat($realClassName, $inflector->getUserClassName($realClassName));
        self::assertStringMatchesFormat($generatedClassName, $inflector->getGeneratedClassName($generatedClassName));
        self::assertStringMatchesFormat($generatedClassName, $inflector->getGeneratedClassName($realClassName));
        self::assertStringMatchesFormat($realClassName, $inflector->getUserClassName($generatedClassName));
    }

    /**
     * @covers \CodeGenerationUtils\Inflector\ClassNameInflector::getGeneratedClassName
     */
    public function testGeneratesSameClassNameWithSameParameters()
    {
        $inflector = new ClassNameInflector('GeneratedClassNS');

        self::assertSame($inflector->getGeneratedClassName('Foo\\Bar'), $inflector->getGeneratedClassName('Foo\\Bar'));
        self::assertSame(
            $inflector->getGeneratedClassName('Foo\\Bar', array('baz' => 'tab')),
            $inflector->getGeneratedClassName('Foo\\Bar', array('baz' => 'tab'))
        );
        self::assertSame(
            $inflector->getGeneratedClassName('Foo\\Bar', array('tab' => 'baz')),
            $inflector->getGeneratedClassName('Foo\\Bar', array('tab' => 'baz'))
        );
    }

    /**
     * @covers \CodeGenerationUtils\Inflector\ClassNameInflector::getGeneratedClassName
     */
    public function testGeneratesDifferentClassNameWithDifferentParameters()
    {
        $inflector = new ClassNameInflector('GeneratedClassNS');

        self::assertNotSame(
            $inflector->getGeneratedClassName('Foo\\Bar'),
            $inflector->getGeneratedClassName('Foo\\Bar', array('foo' => 'bar'))
        );
        self::assertNotSame(
            $inflector->getGeneratedClassName('Foo\\Bar', array('baz' => 'tab')),
            $inflector->getGeneratedClassName('Foo\\Bar', array('tab' => 'baz'))
        );
        self::assertNotSame(
            $inflector->getGeneratedClassName('Foo\\Bar', array('foo' => 'bar', 'tab' => 'baz')),
            $inflector->getGeneratedClassName('Foo\\Bar', array('foo' => 'bar'))
        );
        self::assertNotSame(
            $inflector->getGeneratedClassName('Foo\\Bar', array('foo' => 'bar', 'tab' => 'baz')),
            $inflector->getGeneratedClassName('Foo\\Bar', array('tab' => 'baz', 'foo' => 'bar'))
        );
    }

    /**
     * @return array
     */
    public function getClassNames()
    {
        return array(
            array(
                'Foo',
                'GeneratedClassNS\\' . ClassNameInflectorInterface::GENERATED_CLASS_MARKER . '\\Foo\\%s'
            ),
            array(
                'Foo\\Bar',
                'GeneratedClassNS\\' . ClassNameInflectorInterface::GENERATED_CLASS_MARKER . '\\Foo\\Bar\\%s'
            ),
        );
    }
}
