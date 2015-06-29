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

namespace CodeGenerationUtils\Visitor;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\NodeVisitorAbstract;

/**
 * Disables class methods matching a given filter by replacing their body so that
 * they throw an exception when they are called.
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class MethodDisablerVisitor extends NodeVisitorAbstract
{
    /**
     * @var callable
     */
    private $filter;

    /**
     * Constructor.
     *
     * @param callable $filter a filter method that accepts a single parameter of
     *                         type {@see \PhpParser\Node} and returns null|true|false to
     *                         respectively ignore, remove or replace it.
     */
    public function __construct($filter)
    {
        $this->filter = $filter;
    }

    /**
     * Replaces the given node if it is a class method and matches according to the given callback
     *
     * @param PhpParser\Node $node
     *
     * @return bool|null|PhpParser\Node\Stmt\ClassMethod
     */
    public function leaveNode(Node $node)
    {
        $filter = $this->filter;

        if (! $node instanceof ClassMethod || null === ($filterResult = $filter($node))) {
            return null;
        }

        if (false === $filterResult) {
            return false;
        }

        $node->stmts = array(
            new Throw_(
                new New_(
                    new FullyQualified('BadMethodCallException'),
                    array(new Arg(new String_('Method is disabled')))
                )
            )
        );

        return $node;
    }
}
