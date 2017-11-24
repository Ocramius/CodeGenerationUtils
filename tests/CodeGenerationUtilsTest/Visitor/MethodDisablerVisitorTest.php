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
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPUnit\Framework\TestCase;

/**
 * Tests for {@see \CodeGenerationUtils\Visitor\ClassClonerVisitor}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 *
 * @covers \CodeGenerationUtils\Visitor\MethodDisablerVisitor
 */
class MethodDisablerVisitorTest extends TestCase
{
    public function testDisablesMethod()
    {
        $method = new ClassMethod('test');
        /* @var $filter \PHPUnit_Framework_MockObject_MockObject|callable */
        $filter = $this->getMockBuilder('stdClass')->setMethods(['__invoke'])->getMock();

        $filter->expects(self::once())->method('__invoke')->with($method)->will(self::returnValue(true));

        $visitor = new MethodDisablerVisitor($filter);

        self::assertSame($method, $visitor->leaveNode($method));
        self::assertInstanceOf('PhpParser\Node\Stmt\Throw_', reset($method->stmts));
    }

    public function testSkipsOnFailedFiltering()
    {
        $method = new ClassMethod('test');
        /* @var $filter \PHPUnit_Framework_MockObject_MockObject|callable */
        $filter = $this->getMockBuilder('stdClass')->setMethods(['__invoke'])->getMock();

        $filter->expects(self::once())->method('__invoke')->with($method)->will(self::returnValue(false));

        $visitor = new MethodDisablerVisitor($filter);

        self::assertSame(false, $visitor->leaveNode($method));
    }

    public function testSkipsOnIgnoreFiltering()
    {
        $method = new ClassMethod('test');
        /* @var $filter \PHPUnit_Framework_MockObject_MockObject|callable */
        $filter = $this->getMockBuilder('stdClass')->setMethods(['__invoke'])->getMock();

        $filter->expects(self::once())->method('__invoke')->with($method)->will(self::returnValue(null));

        $visitor = new MethodDisablerVisitor($filter);

        self::assertNull($visitor->leaveNode($method));
    }

    public function testSkipsOnNodeTypeMismatch()
    {
        $class  = new Class_('test');
        /* @var $filter \PHPUnit_Framework_MockObject_MockObject|callable */
        $filter = $this->getMockBuilder('stdClass')->setMethods(['__invoke'])->getMock();

        $filter->expects(self::never())->method('__invoke');

        $visitor = new MethodDisablerVisitor($filter);

        self::assertNull($visitor->leaveNode($class));
    }
}
