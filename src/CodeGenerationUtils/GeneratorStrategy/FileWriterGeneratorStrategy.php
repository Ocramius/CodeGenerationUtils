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

namespace CodeGenerationUtils\GeneratorStrategy;

use CodeGenerationUtils\FileLocator\FileLocatorInterface;
use CodeGenerationUtils\Visitor\ClassFQCNResolverVisitor;
use CodeGenerationUtils\Visitor\Exception\UnexpectedValueException;
use PhpParser\NodeTraverser;
use PhpParser\NodeTraverserInterface;

use function file_put_contents;
use function rename;
use function trim;
use function uniqid;

/**
 * Generator strategy that writes the generated classes to disk while generating them
 *
 * {@inheritDoc}
 */
class FileWriterGeneratorStrategy extends BaseGeneratorStrategy
{
    private NodeTraverserInterface $traverser;
    private ClassFQCNResolverVisitor $visitor;

    public function __construct(protected FileLocatorInterface $fileLocator)
    {
        $this->traverser = new NodeTraverser();
        $this->visitor   = new ClassFQCNResolverVisitor();

        $this->traverser->addVisitor($this->visitor);
    }

    /**
     * Write generated code to disk and return the class code
     *
     * {@inheritDoc}
     *
     * @throws UnexpectedValueException
     */
    public function generate(array $ast): string
    {
        $this->traverser->traverse($ast);

        $generatedCode = parent::generate($ast);
        $className     = trim($this->visitor->getNamespace() . '\\' . $this->visitor->getName(), '\\');
        $fileName      = $this->fileLocator->getGeneratedClassFileName($className);
        $tmpFileName   = $fileName . '.' . uniqid('', true);

        // renaming files is necessary to avoid race conditions when the same file is written multiple times
        // in a short time period
        file_put_contents($tmpFileName, "<?php\n\n" . $generatedCode);
        rename($tmpFileName, $fileName);

        return $generatedCode;
    }
}
