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

use CodeGenerationUtils\Visitor\ClassExtensionVisitor;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PHPUnit_Framework_TestCase;

/**
 * Tests for {@see \CodeGenerationUtils\Visitor\ClassClonerVisitor}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \CodeGenerationUtils\Visitor\ClassExtensionVisitor
 */
class ClassExtensionVisitorTest extends PHPUnit_Framework_TestCase
{
    public function testRenamesNodesOnMatchingClass()
    {
        $visitor   = new ClassExtensionVisitor('Foo\\Bar', 'Baz\\Tab');
        $class     = new Class_('Bar');
        $namespace = new Namespace_(new Name('Foo'));

        $visitor->beforeTraverse(array());
        self::assertSame($namespace, $visitor->enterNode($namespace));
        self::assertNull($visitor->enterNode($class));
        self::assertSame($class, $visitor->leaveNode($class));
        self::assertNull($visitor->leaveNode($namespace));

        self::assertNotNull($class->extends);
        self::assertSame('Baz\\Tab', $class->extends->toString());
    }

    public function testIgnoresNodesOnNonMatchingClass()
    {
        $visitor   = new ClassExtensionVisitor('Foo\\Bar', 'Baz\\Tab');
        $class     = new Class_('Tab');
        $namespace = new Namespace_(new Name('Foo'));

        $visitor->beforeTraverse(array());
        self::assertSame($namespace, $visitor->enterNode($namespace));
        self::assertNull($visitor->enterNode($class));
        self::assertSame($class, $visitor->leaveNode($class));
        self::assertNull($visitor->leaveNode($namespace));

        self::assertNull($class->extends);
    }

    public function testIgnoresNodesOnNonMatchingNamespace()
    {
        $visitor   = new ClassExtensionVisitor('Foo\\Bar', 'Baz\\Tab');
        $class     = new Class_('Bar');
        $namespace = new Namespace_(new Name('Tab'));

        $visitor->beforeTraverse(array());
        self::assertSame($namespace, $visitor->enterNode($namespace));
        self::assertNull($visitor->enterNode($class));
        self::assertSame($class, $visitor->leaveNode($class));
        self::assertNull($visitor->leaveNode($namespace));

        self::assertNull($class->extends);
    }

    public function testMatchOnEmptyNamespace()
    {
        $visitor   = new ClassExtensionVisitor('Foo', 'Baz\\Tab');
        $class     = new Class_('Foo');

        $visitor->beforeTraverse(array());
        self::assertNull($visitor->enterNode($class));
        self::assertSame($class, $visitor->leaveNode($class));

        self::assertNotNull($class->extends);
        self::assertSame('Baz\\Tab', $class->extends->toString());
    }
}
