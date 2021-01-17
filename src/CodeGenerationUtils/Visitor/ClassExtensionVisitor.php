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

namespace CodeGenerationUtils\Visitor;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitorAbstract;

use function implode;
use function trim;

/**
 * Visitor that extends the matched class in the visited AST from another given class
 */
class ClassExtensionVisitor extends NodeVisitorAbstract
{
    private string $matchedClassFQCN;

    private string $newParentClassFQCN;

    private ?Namespace_ $currentNamespace = null;

    public function __construct(string $matchedClassFQCN, string $newParentClassFQCN)
    {
        $this->matchedClassFQCN   = $matchedClassFQCN;
        $this->newParentClassFQCN = $newParentClassFQCN;
    }

    /**
     * {@inheritDoc}
     *
     * Cleans up internal state
     *
     * @param array $nodes
     */
    public function beforeTraverse(array $nodes)
    {
        $this->currentNamespace = null;
    }

    public function enterNode(Node $node): ?Namespace_
    {
        if ($node instanceof Namespace_) {
            $this->currentNamespace = $node;

            return $node;
        }

        return null;
    }

    /**
     * {@inheritDoc}
     *
     * When leaving a node that is a class, replaces it with a modified version that extends the
     * given parent class
     *
     * @param Node $node
     *
     * @todo can be abstracted away into a visitor that allows to modify the node via a callback
     */
    public function leaveNode(Node $node): ?Class_
    {
        if ($node instanceof Namespace_) {
            $this->currentNamespace = null;
        }

        if ($node instanceof Class_) {
            $namespace = $this->currentNamespace && $this->currentNamespace->name !== null
                ? implode('\\', $this->currentNamespace->name->parts)
                : '';

            if (trim($namespace . '\\' . (string) $node->name, '\\') === $this->matchedClassFQCN) {
                $node->extends = new FullyQualified($this->newParentClassFQCN);
            }

            return $node;
        }

        return null;
    }
}
