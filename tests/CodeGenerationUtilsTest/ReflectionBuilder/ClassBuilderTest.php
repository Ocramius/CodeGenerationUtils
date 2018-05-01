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

namespace CodeGenerationUtilsTest\Visitor;

use CodeGenerationUtils\ReflectionBuilder\ClassBuilder;
use CodeGenerationUtilsTestAsset\ClassWithDefaultValueIsConstantMethod;
use PhpParser\Node\Stmt\ClassMethod;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

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
    public function testBuildSelf()
    {
        $classBuilder = new ClassBuilder();
        $ast          = $classBuilder->fromReflection(new ReflectionClass(__CLASS__));
        /* @var $namespace \PhpParser\Node\Stmt\Namespace_ */
        $namespace    = $ast[0];

        self::assertInstanceOf('PhpParser\Node\Stmt\Namespace_', $namespace);
        self::assertSame(__NAMESPACE__, $namespace->name->toString());

        /* @var $class \PhpParser\Node\Stmt\Class_ */
        $class = $namespace->stmts[0];

        self::assertInstanceOf('PhpParser\Node\Stmt\Class_', $class);
        self::assertSame('ClassBuilderTest', $class->name->name);

        $currentMethod = __FUNCTION__;
        /* @var $methods \PhpParser\Node\Stmt\ClassMethod[] */
        $methods       = array_filter(
            $class->stmts,
            function ($node) use ($currentMethod) {
                return $node instanceof ClassMethod && $node->name->name === $currentMethod;
            }
        );

        self::assertCount(1, $methods);

        /* @var $thisMethod \PhpParser\Node\Stmt\ClassMethod */
        $thisMethod = reset($methods);

        self::assertSame($currentMethod, $thisMethod->name->name);
    }

    /**
     * Check the isDefaultValueConstant edge case.
     */
    public function testBuildWithDefaultValueConstantParameter()
    {
        $classBuilder = new ClassBuilder();
        $testClass    = new ClassWithDefaultValueIsConstantMethod();
        $ast          = $classBuilder->fromReflection(new ReflectionClass($testClass));

        /* @var $namespace \PhpParser\Node\Stmt\Namespace_ */
        $namespace = $ast[0];
        /* @var $class \PhpParser\Node\Stmt\Class_ */
        $class     = $namespace->stmts[0];
        $method    = 'defaultValueIsConstant';

        /* @var $methods \PhpParser\Node\Stmt\ClassMethod[] */
        $methods = array_filter(
            $class->stmts,
            function ($node) use ($method) {
                return ($node instanceof ClassMethod && $node->name->name === $method);
            }
        );

        self::assertCount(1, $methods);

        /* @var $thisMethod \PhpParser\Node\Stmt\ClassMethod */
        $thisMethod = reset($methods);

        self::assertSame($method, $thisMethod->name->name);
    }
}
