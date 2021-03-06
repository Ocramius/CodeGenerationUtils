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

use function file_put_contents;
use function ini_get;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

/**
 * Generator strategy that produces the code and evaluates it at runtime
 */
class EvaluatingGeneratorStrategy extends BaseGeneratorStrategy
{
    /** @var bool flag indicating whether {@see eval} can be used */
    private bool $canEval = true;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->canEval = ! ini_get('suhosin.executor.disable_eval');
    }

    /**
     * Evaluates the generated code before returning it
     *
     * {@inheritDoc}
     */
    public function generate(array $ast): string
    {
        $code = parent::generate($ast);

        if (! $this->canEval) {
            $fileName = sys_get_temp_dir() . '/EvaluatingGeneratorStrategy.php.tmp.' . uniqid('', true);

            file_put_contents($fileName, "<?php\n" . $code);
            /** @psalm-suppress UnresolvableInclude we're doing `eval()` here! There's no going back! */
            require $fileName;
            unlink($fileName);

            return $code;
        }

        eval($code);

        return $code;
    }
}
