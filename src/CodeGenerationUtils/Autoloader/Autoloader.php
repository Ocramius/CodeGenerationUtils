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

namespace CodeGenerationUtils\Autoloader;

use CodeGenerationUtils\FileLocator\FileLocatorInterface;
use CodeGenerationUtils\Inflector\ClassNameInflectorInterface;

use function class_exists;
use function file_exists;

class Autoloader implements AutoloaderInterface
{
    protected FileLocatorInterface $fileLocator;

    protected ClassNameInflectorInterface $classNameInflector;

    public function __construct(FileLocatorInterface $fileLocator, ClassNameInflectorInterface $classNameInflector)
    {
        $this->fileLocator        = $fileLocator;
        $this->classNameInflector = $classNameInflector;
    }

    public function __invoke(string $className): bool
    {
        if (class_exists($className, false) || ! $this->classNameInflector->isGeneratedClassName($className)) {
            return false;
        }

        $file = $this->fileLocator->getGeneratedClassFileName($className);

        if (! file_exists($file)) {
            return false;
        }

        return (bool) require_once $file;
    }
}
