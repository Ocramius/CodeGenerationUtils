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
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitorAbstract;
use ReflectionClass;

/**
 * Renames a matched class to a new name.
 * Removes the namespace if the class is in the global namespace.
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassRenamerVisitor extends NodeVisitorAbstract
{
    /**
     * @var ReflectionClass
     */
    private $reflectedClass;

    /**
     * @var string
     */
    private $newName;

    /**
     * @var string
     */
    private $newNamespace;

    /**
     * @var \PhpParser\Node\Stmt\Namespace_|null
     */
    private $currentNamespace;

    /**
     * @var \PhpParser\Node\Stmt\Class_|null the currently detected class in this namespace
     */
    private $replacedInNamespace;

    /**
     * @param ReflectionClass $reflectedClass
     * @param string          $newFQCN
     */
    public function __construct(ReflectionClass $reflectedClass, $newFQCN)
    {
        $this->reflectedClass = $reflectedClass;
        $fqcnParts            = explode('\\', $newFQCN);
        $this->newNamespace   = implode('\\', array_slice($fqcnParts, 0, -1));
        $this->newName        = end($fqcnParts);
    }

    /**
     * Cleanup internal state
     *
     * @param array $nodes
     *
     * @return null
     */
    public function beforeTraverse(array $nodes)
    {
        // reset state
        $this->currentNamespace    = null;
        $this->replacedInNamespace = null;
    }

    /**
     * @param \PhpParser\Node $node
     *
     * @return \PhpParser\Node\Stmt\Namespace_|void
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Namespace_) {
            return $this->currentNamespace = $node;
        }
    }

    /**
     * Replaces (if matching) the given node to comply with the new given name
     *
     * @param \PhpParser\Node $node
     *
     * @todo can be abstracted away into a visitor that allows to modify the matched node via a callback
     *
     * @return array|null|\PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Namespace_|void
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Namespace_) {
            $namespace                 = $this->currentNamespace;
            $replacedInNamespace       = $this->replacedInNamespace;
            $this->currentNamespace    = null;
            $this->replacedInNamespace = null;

            if ($namespace && $replacedInNamespace) {
                if (! $this->newNamespace) {
                    // @todo what happens to other classes in here?
                    return array($replacedInNamespace);
                }

                $namespace->name->parts = explode('\\', $this->newNamespace);

                return $namespace;
            }
        }

        if ($node instanceof Class_
            && $this->namespaceMatches()
            && ($this->reflectedClass->getShortName() === $node->name)
        ) {
            $node->name = $this->newName;

            // @todo too simplistic (assumes single class per namespace right now)
            if ($this->currentNamespace) {
                $this->replacedInNamespace = $node;
                $this->currentNamespace->stmts = array($node);
            } elseif ($this->newNamespace) {
                // wrap in namespace if no previous namespace exists
                return new Namespace_(new Name($this->newNamespace), array($node));
            }

            return $node;
        }
    }

    /**
     * Checks if the current namespace matches with the one provided with the reflection class
     *
     * @return bool
     */
    private function namespaceMatches()
    {
        $currentNamespace = ($this->currentNamespace && is_array($this->currentNamespace->name->parts))
            ? $this->currentNamespace->name->toString()
            : '';

        return $currentNamespace === $this->reflectedClass->getNamespaceName();
    }
}
