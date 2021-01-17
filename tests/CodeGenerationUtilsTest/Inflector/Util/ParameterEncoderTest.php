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

namespace CodeGenerationUtilsTest\Inflector\Util;

use CodeGenerationUtils\Inflector\Util\ParameterEncoder;
use PHPUnit\Framework\TestCase;

/**
 * Tests for {@see \CodeGenerationUtils\Inflector\Util\ParameterEncoder}
 */
class ParameterEncoderTest extends TestCase
{
    /**
     * @param mixed[] $parameters
     *
     * @dataProvider getParameters
     * @covers \CodeGenerationUtils\Inflector\Util\ParameterEncoder::encodeParameters
     */
    public function testGeneratesValidClassName(array $parameters): void
    {
        $encoder = new ParameterEncoder();

        self::assertRegExp(
            '/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+/',
            $encoder->encodeParameters($parameters),
            'Encoded string is a valid class identifier'
        );
    }

    /** @psalm-return non-empty`-list<array{array<mixed>}> */
    public function getParameters(): array
    {
        return [
            [[]],
            [['foo' => 'bar']],
            [['bar' => 'baz']],
            [[null]],
            [[null, null]],
            [['bar' => null]],
            [['bar' => 12345]],
            [['foo' => 'bar', 'bar' => 'baz']],
        ];
    }
}
