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

namespace CodeGenerationUtils\Inflector;

use CodeGenerationUtils\Inflector\Util\ParameterEncoder;

use function strlen;
use function strrpos;
use function substr;

class ClassNameInflector implements ClassNameInflectorInterface
{
    protected string $generatedClassesNamespace;

    private int $generatedClassMarkerLength;

    private string $generatedClassMarker;

    private ParameterEncoder $parameterEncoder;

    public function __construct(string $generatedClassesNamespace)
    {
        $this->generatedClassesNamespace  = $generatedClassesNamespace;
        $this->generatedClassMarker       = '\\' . self::GENERATED_CLASS_MARKER . '\\';
        $this->generatedClassMarkerLength = strlen($this->generatedClassMarker);
        $this->parameterEncoder           = new ParameterEncoder();
    }

    public function getUserClassName(string $className): string
    {
        $position = strrpos($className, $this->generatedClassMarker);

        if ($position === false) {
            return $className;
        }

        return substr(
            $className,
            $this->generatedClassMarkerLength + $position,
            strrpos($className, '\\') - ($position + $this->generatedClassMarkerLength)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getGeneratedClassName(string $className, array $options = []): string
    {
        return $this->generatedClassesNamespace
            . $this->generatedClassMarker
            . $this->getUserClassName($className)
            . '\\' . $this->parameterEncoder->encodeParameters($options);
    }

    public function isGeneratedClassName(string $className): bool
    {
        return strrpos($className, $this->generatedClassMarker) !== false;
    }
}
