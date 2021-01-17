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

use CodeGenerationUtils\Visitor\ClassRenamerVisitor;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Tests for {@see \CodeGenerationUtils\Visitor\ClassClonerVisitor}
 *
 * @covers \CodeGenerationUtils\Visitor\ClassRenamerVisitor
 */
class ClassRenamerVisitorTest extends TestCase
{
    public function testRenamesNodesOnMatchingClass(): void
    {
        $visitor   = new ClassRenamerVisitor(new ReflectionClass(self::class), 'Foo\\Bar\\Baz');
        $class     = new Class_('ClassRenamerVisitorTest');
        $namespace = new Namespace_(
            new Name(['CodeGenerationUtilsTest', 'Visitor'])
        );

        $visitor->beforeTraverse([]);
        self::assertSame($namespace, $visitor->enterNode($namespace));
        self::assertNull($visitor->enterNode($class));
        self::assertSame($class, $visitor->leaveNode($class));
        self::assertSame($namespace, $visitor->leaveNode($namespace));

        self::assertSame('Baz', (string) $class->name);
        self::assertSame(['Foo', 'Bar'], $namespace->name->parts);
        self::assertSame([$class], $namespace->stmts);
    }

    public function testIgnoresNodesOnNonMatchingClass(): void
    {
        $visitor   = new ClassRenamerVisitor(new ReflectionClass(self::class), 'Foo\\Bar\\Baz');
        $class     = new Class_('Wrong');
        $namespace = new Namespace_(
            new Name(['CodeGenerationUtilsTest', 'Visitor'])
        );

        $visitor->beforeTraverse([]);
        self::assertSame($namespace, $visitor->enterNode($namespace));
        self::assertNull($visitor->enterNode($class));
        $visitor->leaveNode($class);
        $visitor->leaveNode($namespace);

        self::assertSame('Wrong', (string) $class->name);
        self::assertSame(['CodeGenerationUtilsTest', 'Visitor'], $namespace->name->parts);
    }

    public function testIgnoresNodesOnNonMatchingNamespace(): void
    {
        $visitor   = new ClassRenamerVisitor(new ReflectionClass(self::class), 'Foo\\Bar\\Baz');
        $class     = new Class_('ClassRenamerVisitorTest');
        $namespace = new Namespace_(
            new Name(['Wrong', 'Namespace', 'Here'])
        );

        $visitor->beforeTraverse([]);
        self::assertSame($namespace, $visitor->enterNode($namespace));
        self::assertNull($visitor->enterNode($class));
        $visitor->leaveNode($class);
        $visitor->leaveNode($namespace);

        self::assertSame('ClassRenamerVisitorTest', (string) $class->name);
        self::assertSame(['Wrong', 'Namespace', 'Here'], $namespace->name->parts);
    }

    public function testMatchOnEmptyNamespace(): void
    {
        $visitor = new ClassRenamerVisitor(new ReflectionClass('stdClass'), 'Baz');
        $class   = new Class_('stdClass');

        $visitor->beforeTraverse([]);
        self::assertNull($visitor->enterNode($class));
        self::assertSame($class, $visitor->leaveNode($class));

        self::assertSame('Baz', (string) $class->name);
    }

    public function testUnwrapsNamespacedClassCorrectly(): void
    {
        $visitor   = new ClassRenamerVisitor(new ReflectionClass(self::class), 'Baz');
        $class     = new Class_('ClassRenamerVisitorTest');
        $namespace = new Namespace_(
            new Name(['CodeGenerationUtilsTest', 'Visitor'])
        );

        $visitor->beforeTraverse([]);
        self::assertSame($namespace, $visitor->enterNode($namespace));
        self::assertNull($visitor->enterNode($class));
        self::assertSame($class, $visitor->leaveNode($class));
        self::assertSame([$class], $visitor->leaveNode($namespace));

        self::assertSame('Baz', (string) $class->name);
    }

    public function testWrapsGlobalClassCorrectly(): void
    {
        $visitor = new ClassRenamerVisitor(new ReflectionClass('stdClass'), 'Foo\\Bar');
        $class   = new Class_('stdClass');

        $visitor->beforeTraverse([]);
        self::assertNull($visitor->enterNode($class));
        $namespace = $visitor->leaveNode($class);

        self::assertInstanceOf(Namespace_::class, $namespace);
        self::assertSame('Foo', $namespace->name->toString());
        self::assertSame([$class], $namespace->stmts);
    }

    public function testMismatchOnEmptyNamespace(): void
    {
        $visitor   = new ClassRenamerVisitor(new ReflectionClass('stdClass'), 'Baz');
        $class     = new Class_('stdClass');
        $namespace = new Namespace_(
            new Name(['Wrong', 'Namespace', 'Here'])
        );

        $visitor->beforeTraverse([]);
        self::assertSame($namespace, $visitor->enterNode($namespace));
        self::assertNull($visitor->enterNode($class));
        $visitor->leaveNode($class);
        $visitor->leaveNode($namespace);

        self::assertSame('stdClass', (string) $class->name);
        self::assertSame(['Wrong', 'Namespace', 'Here'], $namespace->name->parts);
    }
}
