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
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitorAbstract;

/**
 * Implements the given interfaces on the given class name within the AST
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassImplementorVisitor extends NodeVisitorAbstract
{
    private $matchedClassFQCN;

    /**
     * @var \PhpParser\Node\Name[]
     */
    private $interfaces;

    /**
     * @var PhpParser\Node\Stmt\Namespace_|null
     */
    private $currentNamespace;

    /**
     * @param string   $matchedClassFQCN
     * @param string[] $interfaces
     */
    public function __construct($matchedClassFQCN, array $interfaces)
    {
        $this->matchedClassFQCN = (string) $matchedClassFQCN;
        $this->interfaces       = array_map(
            function ($interfaceName) {
                return new FullyQualified($interfaceName);
            },
            $interfaces
        );
    }

    /**
     * Cleanup internal state
     *
     * @param array $nodes
     */
    public function beforeTraverse(array $nodes)
    {
        $this->currentNamespace = null;
    }

    /**
     * @param PhpParser\Node $node
     *
     * @return PhpParser\Node\Stmt\Namespace_|void
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Namespace_) {
            $this->currentNamespace = $node;

            return $node;
        }
    }

    /**
     * Replaces class nodes with nodes implementing the given interfaces. Implemented interfaces are replaced,
     * not added.
     *
     * @param PhpParser\Node $node
     *
     * @todo can be abstracted away into a visitor that allows to modify the matched node via a callback
     *
     * @return PhpParser\Node\Stmt\Class_|void
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Namespace_) {
            $this->currentNamespace = null;
        }

        if ($node instanceof Class_) {
            $namespace = ($this->currentNamespace && is_array($this->currentNamespace->name->parts))
                ? implode('\\', $this->currentNamespace->name->parts)
                : '';

            if (trim($namespace . '\\' . $node->name, '\\') === $this->matchedClassFQCN) {
                $node->implements = $this->interfaces;
            }

            return $node;
        }
    }
}
