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
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

use function assert;
use function is_callable;
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
        $filter = $this->getMockBuilder('stdClass')->setMethods(['__invoke'])->getMock();
        assert($filter instanceof PHPUnit_Framework_MockObject_MockObject || is_callable($filter));

        $filter->expects(self::once())->method('__invoke')->with($method)->will(self::returnValue(true));

        $visitor = new MethodDisablerVisitor($filter);

        self::assertSame($method, $visitor->leaveNode($method));
        self::assertInstanceOf('PhpParser\Node\Stmt\Throw_', reset($method->stmts));
    }

    public function testSkipsOnFailedFiltering(): void
    {
        $method = new ClassMethod('test');
        $filter = $this->getMockBuilder('stdClass')->setMethods(['__invoke'])->getMock();
        assert($filter instanceof PHPUnit_Framework_MockObject_MockObject || is_callable($filter));

        $filter->expects(self::once())->method('__invoke')->with($method)->will(self::returnValue(false));

        $visitor = new MethodDisablerVisitor($filter);

        self::assertSame(false, $visitor->leaveNode($method));
    }

    public function testSkipsOnIgnoreFiltering(): void
    {
        $method = new ClassMethod('test');
        $filter = $this->getMockBuilder('stdClass')->setMethods(['__invoke'])->getMock();
        assert($filter instanceof PHPUnit_Framework_MockObject_MockObject || is_callable($filter));

        $filter->expects(self::once())->method('__invoke')->with($method)->will(self::returnValue(null));

        $visitor = new MethodDisablerVisitor($filter);

        self::assertNull($visitor->leaveNode($method));
    }

    public function testSkipsOnNodeTypeMismatch(): void
    {
        $class  = new Class_('test');
        $filter = $this->getMockBuilder('stdClass')->setMethods(['__invoke'])->getMock();
        assert($filter instanceof PHPUnit_Framework_MockObject_MockObject || is_callable($filter));

        $filter->expects(self::never())->method('__invoke');

        $visitor = new MethodDisablerVisitor($filter);

        self::assertNull($visitor->leaveNode($class));
    }
}
