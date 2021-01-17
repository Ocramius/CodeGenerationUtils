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

use CodeGenerationUtils\Visitor\ClassFQCNResolverVisitor;
use CodeGenerationUtils\Visitor\Exception\UnexpectedValueException;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PHPUnit\Framework\TestCase;

/**
 * Tests for {@see \CodeGenerationUtils\Visitor\ClassClonerVisitor}
 *
 * @covers \CodeGenerationUtils\Visitor\ClassFQCNResolverVisitor
 */
class ClassFQCNResolverVisitorTest extends TestCase
{
    protected ClassFQCNResolverVisitor $visitor;

    public function setUp(): void
    {
        $this->visitor = new ClassFQCNResolverVisitor();
    }

    public function testDiscoversSimpleClass(): void
    {
        $class = new Class_('Foo');

        $this->visitor->beforeTraverse([$class]);
        $this->visitor->enterNode($class);

        self::assertSame('Foo', $this->visitor->getName());
        self::assertSame('', $this->visitor->getNamespace());
    }

    public function testDiscoversNamespacedClass(): void
    {
        $namespace = new Namespace_(new Name(['Bar', 'Baz']));
        $class     = new Class_('Foo');

        $namespace->stmts = [$class];

        $this->visitor->beforeTraverse([$namespace]);
        $this->visitor->enterNode($namespace);
        $this->visitor->enterNode($class);

        self::assertSame('Foo', $this->visitor->getName());
        self::assertSame('Bar\\Baz', $this->visitor->getNamespace());
    }

    public function testThrowsExceptionOnMultipleClasses(): void
    {
        $class1 = new Class_('Foo');
        $class2 = new Class_('Bar');

        $this->visitor->beforeTraverse([$class1, $class2]);

        $this->visitor->enterNode($class1);

        $this->expectException(UnexpectedValueException::class);

        $this->visitor->enterNode($class2);
    }

    public function testThrowsExceptionOnMultipleNamespaces(): void
    {
        $namespace1 = new Namespace_(new Name('Foo'));
        $namespace2 = new Namespace_(new Name('Bar'));

        $this->visitor->beforeTraverse([$namespace1, $namespace2]);

        $this->visitor->enterNode($namespace1);

        $this->expectException(UnexpectedValueException::class);

        $this->visitor->enterNode($namespace2);
    }

    public function testThrowsExceptionWhenNoClassIsFound(): void
    {
        self::assertSame('', $this->visitor->getNamespace());

        $this->expectException(UnexpectedValueException::class);

        $this->visitor->getName();
    }
}
