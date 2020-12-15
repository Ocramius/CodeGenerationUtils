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

namespace CodeGenerationUtilsTest\ReflectionBuilder;

use CodeGenerationUtils\ReflectionBuilder\ClassBuilder;
use CodeGenerationUtilsTestAsset\ClassWithDefaultValueIsConstantMethod;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use PhpParser\Node\Stmt\Namespace_;

/**
 * Tests for {@see \CodeGenerationUtils\ReflectionBuilder\ClassBuilder}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \CodeGenerationUtils\ReflectionBuilder\ClassBuilder
 */
class ClassBuilderTest extends TestCase
{
    /**
     * Simple test reflecting this test class
     */
    public function testBuildSelf(): void
    {
        $classBuilder = new ClassBuilder();
        $ast          = $classBuilder->fromReflection(new ReflectionClass(__CLASS__));
        /* @var $namespace Namespace_ */
        $namespace    = $ast[0];

        self::assertInstanceOf(Namespace_::class, $namespace);
        self::assertSame(__NAMESPACE__, $namespace->name->toString());

        /* @var $class Class_ */
        $class = $namespace->stmts[0];

        self::assertInstanceOf(Class_::class, $class);
        self::assertSame('ClassBuilderTest', (string)$class->name);

        $currentMethod = __FUNCTION__;
        /* @var $methods ClassMethod[] */
        $methods       = array_filter(
            $class->stmts,
            static function ($node) use ($currentMethod) {
                return $node instanceof ClassMethod && (string)$node->name === $currentMethod;
            }
        );

        self::assertCount(1, $methods);

        $thisMethod = reset($methods);

        self::assertSame($currentMethod, (string)$thisMethod->name);
    }

    /**
     * Check the isDefaultValueConstant edge case.
     */
    public function testBuildWithDefaultValueConstantParameter(): void
    {
        $classBuilder = new ClassBuilder();
        $testClass    = new ClassWithDefaultValueIsConstantMethod();
        $ast          = $classBuilder->fromReflection(new ReflectionClass($testClass));

        /* @var $namespace Namespace_ */
        $namespace = $ast[0];
        /* @var $class Class_ */
        $class     = $namespace->stmts[0];
        $method    = 'defaultValueIsConstant';

        /* @var $methods ClassMethod[] */
        $methods = array_filter(
            $class->stmts,
            static function ($node) use ($method) {
                return ($node instanceof ClassMethod && (string)$node->name === $method);
            }
        );

        self::assertCount(1, $methods);

        $thisMethod = reset($methods);

        self::assertSame($method, (string)$thisMethod->name);
    }
}
