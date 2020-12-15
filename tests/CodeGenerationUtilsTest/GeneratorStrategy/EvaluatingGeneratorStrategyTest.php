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

namespace CodeGenerationUtilsTest\GeneratorStrategy;

use CodeGenerationUtils\GeneratorStrategy\EvaluatingGeneratorStrategy;
use CodeGenerationUtils\Inflector\Util\UniqueIdentifierGenerator;
use PhpParser\Node\Stmt\Class_;
use PHPUnit\Framework\TestCase;

/**
 * Tests for {@see \CodeGenerationUtils\GeneratorStrategy\EvaluatingGeneratorStrategy}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class EvaluatingGeneratorStrategyTest extends TestCase
{
    /**
     * @covers \CodeGenerationUtils\GeneratorStrategy\EvaluatingGeneratorStrategy::generate
     * @covers \CodeGenerationUtils\GeneratorStrategy\EvaluatingGeneratorStrategy::__construct
     */
    public function testGenerate(): void
    {
        $strategy       = new EvaluatingGeneratorStrategy();
        $className      = UniqueIdentifierGenerator::getIdentifier('Foo');
        $generated      = $strategy->generate(array(new Class_($className)));

        self::assertGreaterThan(0, strpos($generated, $className));
        self::assertTrue(class_exists($className, false));
    }

    /**
     * @covers \CodeGenerationUtils\GeneratorStrategy\EvaluatingGeneratorStrategy::generate
     * @covers \CodeGenerationUtils\GeneratorStrategy\EvaluatingGeneratorStrategy::__construct
     */
    public function testGenerateWithDisabledEval(): void
    {
        if (! ini_get('suhosin.executor.disable_eval')) {
            self::markTestSkipped('Ini setting "suhosin.executor.disable_eval" is needed to run this test');
        }

        $strategy       = new EvaluatingGeneratorStrategy();
        $className      = 'Foo' . uniqid('', true);
        $generated      = $strategy->generate(array(new Class_($className)));

        self::assertGreaterThan(0, strpos($generated, $className));
        self::assertTrue(class_exists($className, false));
    }
}
