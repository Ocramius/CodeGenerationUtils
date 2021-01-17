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

use CodeGenerationUtils\Visitor\ClassClonerVisitor;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Namespace_;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function assert;
use function end;
use function implode;

/**
 * Tests for {@see \CodeGenerationUtils\Visitor\ClassClonerVisitor}
 *
 * @covers \CodeGenerationUtils\Visitor\ClassClonerVisitor
 */
class ClassClonerVisitorTest extends TestCase
{
    public function testClonesClassIntoEmptyNodeList(): void
    {
        $reflectionClass = new ReflectionClass(self::class);

        $visitor = new ClassClonerVisitor($reflectionClass);

        $nodes = $visitor->beforeTraverse([]);

        self::assertInstanceOf(Declare_::class, $nodes[0]);
        self::assertInstanceOf(Namespace_::class, $nodes[1]);

        $node = $nodes[1];
        assert($node instanceof Namespace_);

        self::assertSame(__NAMESPACE__, implode('\\', $node->name->parts));

        $class = end($node->stmts);
        assert($class instanceof Class_);

        self::assertInstanceOf(Class_::class, $class);
        self::assertSame('ClassClonerVisitorTest', (string) $class->name);
    }

    public function testClonesClassIntoNonEmptyNodeList(): void
    {
        self::markTestIncomplete('Still not clear thoughts on this...');
    }
}
