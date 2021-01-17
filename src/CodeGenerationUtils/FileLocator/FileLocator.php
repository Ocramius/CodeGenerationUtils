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

namespace CodeGenerationUtils\FileLocator;

use CodeGenerationUtils\Exception\InvalidGeneratedClassesDirectoryException as InvalidDirectory;

use function is_bool;
use function realpath;
use function str_replace;

use const DIRECTORY_SEPARATOR;

class FileLocator implements FileLocatorInterface
{
    protected string $generatedClassesDirectory;

    /**
     * @throws InvalidDirectory
     */
    public function __construct(string $generatedClassesDirectory)
    {
        $realPath = realpath($generatedClassesDirectory);

        if (is_bool($realPath)) {
            throw InvalidDirectory::generatedClassesDirectoryNotFound($generatedClassesDirectory);
        }

        $this->generatedClassesDirectory = $realPath;
    }

    public function getGeneratedClassFileName(string $className): string
    {
        return $this->generatedClassesDirectory . DIRECTORY_SEPARATOR . str_replace('\\', '', $className) . '.php';
    }
}
