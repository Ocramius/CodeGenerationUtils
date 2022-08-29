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
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitorAbstract;

use function array_map;
use function implode;
use function trim;

/**
 * Implements the given interfaces on the given class name within the AST
 */
class ClassImplementorVisitor extends NodeVisitorAbstract
{
    /** @var Name[] */
    private array $interfaces;

    private Namespace_|null $currentNamespace = null;

    /** @param string[] $interfaces */
    public function __construct(private string $matchedClassFQCN, array $interfaces)
    {
        $this->interfaces = array_map(
            static function ($interfaceName) {
                return new FullyQualified($interfaceName);
            },
            $interfaces,
        );
    }

    /**
     * {@inheritDoc}
     *
     * Cleanup internal state
     */
    public function beforeTraverse(array $nodes)
    {
        $this->currentNamespace = null;

        return null;
    }

    public function enterNode(Node $node): Namespace_|null
    {
        if ($node instanceof Namespace_) {
            $this->currentNamespace = $node;

            return $node;
        }

        return null;
    }

    /**
     * Replaces class nodes with nodes implementing the given interfaces. Implemented interfaces are replaced,
     * not added.
     *
     * @todo can be abstracted away into a visitor that allows to modify the matched node via a callback
     */
    public function leaveNode(Node $node): Class_|null
    {
        if ($node instanceof Namespace_) {
            $this->currentNamespace = null;
        }

        if ($node instanceof Class_) {
            $namespace = $this->currentNamespace && $this->currentNamespace->name !== null
                ? implode('\\', $this->currentNamespace->name->parts)
                : '';

            if (trim($namespace . '\\' . (string) $node->name, '\\') === $this->matchedClassFQCN) {
                $node->implements = $this->interfaces;
            }

            return $node;
        }

        return null;
    }
}
