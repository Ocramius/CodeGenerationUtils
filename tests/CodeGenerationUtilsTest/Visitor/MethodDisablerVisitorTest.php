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

use CodeGenerationUtils\Visitor\MethodDisablerVisitor;
use CodeGenerationUtilsTestAsset\CallableFilterStub;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\TestCase;

use function reset;

/**
 * Tests for {@see \CodeGenerationUtils\Visitor\ClassClonerVisitor}
 *
 * @covers \CodeGenerationUtils\Visitor\MethodDisablerVisitor
 */
class MethodDisablerVisitorTest extends TestCase
{
    public function testDisablesMethod(): void
    {
        $method = new ClassMethod('test');
        $filter = $this->createMock(CallableFilterStub::class);

        $filter->expects(self::once())->method('__invoke')->with($method)->will(self::returnValue(true));

        /** @psalm-suppress InvalidArgument $visitor callable is correctly typed here */
        $visitor = new MethodDisablerVisitor($filter);

        self::assertSame($method, $visitor->leaveNode($method));

        $statements = $method->stmts;

        self::assertIsArray($statements);
        self::assertInstanceOf(Throw_::class, reset($statements));
    }

    public function testSkipsOnFailedFiltering(): void
    {
        $method = new ClassMethod('test');
        $filter = $this->createMock(CallableFilterStub::class);

        $filter->expects(self::once())->method('__invoke')->with($method)->will(self::returnValue(false));

        /** @psalm-suppress InvalidArgument $visitor callable is correctly typed here */
        $visitor = new MethodDisablerVisitor($filter);

        self::assertSame(NodeTraverser::REMOVE_NODE, $visitor->leaveNode($method));
    }

    public function testSkipsOnIgnoreFiltering(): void
    {
        $method = new ClassMethod('test');
        $filter = $this->createMock(CallableFilterStub::class);

        $filter->expects(self::once())->method('__invoke')->with($method)->will(self::returnValue(null));

        /** @psalm-suppress InvalidArgument $visitor callable is correctly typed here */
        $visitor = new MethodDisablerVisitor($filter);

        self::assertNull($visitor->leaveNode($method));
    }

    public function testSkipsOnNodeTypeMismatch(): void
    {
        $class  = new Class_('test');
        $filter = $this->createMock(CallableFilterStub::class);

        $filter->expects(self::never())->method('__invoke');

        /** @psalm-suppress InvalidArgument $visitor callable is correctly typed here */
        $visitor = new MethodDisablerVisitor($filter);

        self::assertNull($visitor->leaveNode($class));
    }
}
