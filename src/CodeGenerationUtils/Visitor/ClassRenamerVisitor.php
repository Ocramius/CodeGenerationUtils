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

use function array_slice;
use function assert;
use function end;
use function explode;
use function implode;

/**
 * Renames a matched class to a new name.
 * Removes the namespace if the class is in the global namespace.
 */
class ClassRenamerVisitor extends NodeVisitorAbstract
{
    private ReflectionClass $reflectedClass;

    private string $newName;

    private string $newNamespace;

    private ?Namespace_ $currentNamespace = null;

    /** @var Class_|null the currently detected class in this namespace */
    private ?Class_ $replacedInNamespace = null;

    public function __construct(ReflectionClass $reflectedClass, string $newFQCN)
    {
        $this->reflectedClass = $reflectedClass;
        $fqcnParts            = explode('\\', $newFQCN);
        $this->newNamespace   = implode('\\', array_slice($fqcnParts, 0, -1));
        $this->newName        = end($fqcnParts);
    }

    /** {@inheritDoc} */
    public function beforeTraverse(array $nodes)
    {
        $this->currentNamespace    = null;
        $this->replacedInNamespace = null;

        return null;
    }

    public function enterNode(Node $node): ?Namespace_
    {
        if ($node instanceof Namespace_) {
            return $this->currentNamespace = $node;
        }

        return null;
    }

    /**
     * Replaces (if matching) the given node to comply with the new given name
     *
     * @psalm-return array{Class_}|Class_|Namespace_|null
     * @todo can be abstracted away into a visitor that allows to modify the matched node via a callback
     */
    public function leaveNode(Node $node): array | Class_ | Namespace_ | null
    {
        if ($node instanceof Namespace_) {
            $namespace                 = $this->currentNamespace;
            $replacedInNamespace       = $this->replacedInNamespace;
            $this->currentNamespace    = null;
            $this->replacedInNamespace = null;

            if ($namespace && $replacedInNamespace) {
                if (! $this->newNamespace) {
                    // @todo what happens to other classes in here?
                    return [$replacedInNamespace];
                }

                $namespaceName = $namespace->name;
                /** @psalm-var non-empty-list<non-empty-string> $newParts leap of faith, especially the fact that each bit is non-empty! */
                $newParts = explode('\\', $this->newNamespace);

                assert($namespaceName !== null);

                $namespaceName->parts = $newParts;
                $namespace->name      = $namespaceName;

                return $namespace;
            }
        }

        if (
            $node instanceof Class_
            && $this->namespaceMatches()
            && ($this->reflectedClass->getShortName() === (string) $node->name)
        ) {
            $node->name = new Node\Identifier($this->newName);

            // @todo too simplistic (assumes single class per namespace right now)
            if ($this->currentNamespace) {
                $this->replacedInNamespace     = $node;
                $this->currentNamespace->stmts = [$node];
            } elseif ($this->newNamespace) {
                // wrap in namespace if no previous namespace exists
                return new Namespace_(new Name($this->newNamespace), [$node]);
            }

            return $node;
        }

        return null;
    }

    /**
     * Checks if the current namespace matches with the one provided with the reflection class
     */
    private function namespaceMatches(): bool
    {
        if ($this->currentNamespace === null) {
            return $this->reflectedClass->getNamespaceName() === '';
        }

        $currentNamespaceName = $this->currentNamespace->name;

        if ($currentNamespaceName === null) {
            return $this->reflectedClass->getNamespaceName() === '';
        }

        return $currentNamespaceName->toString() === $this->reflectedClass->getNamespaceName();
    }
}
