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

namespace CodeGenerationUtils\ReflectionBuilder;

use BadMethodCallException;
use PhpParser\Builder\Method;
use PhpParser\Builder\Param;
use PhpParser\Builder\Property;
use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Namespace_;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

use function assert;
use function explode;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;

/**
 * Rudimentary utility to build an AST from a reflection class
 *
 * @todo should be split into various utilities like this one and eventually replace `Zend\Code\Generator`
 */
class ClassBuilder
{
    /**
     * @return Node[]
     */
    public function fromReflection(ReflectionClass $reflectionClass): array
    {
        $class       = new Class_($reflectionClass->getShortName());
        $statements  = [$class];
        $parentClass = $reflectionClass->getParentClass();

        if ($parentClass) {
            $class->extends = new FullyQualified($parentClass->getName());
        }

        $interfaces = [];

        foreach ($reflectionClass->getInterfaces() as $reflectionInterface) {
            $interfaces[] = new FullyQualified($reflectionInterface->getName());
        }

        $class->implements = $interfaces;

        foreach ($reflectionClass->getConstants() as $constant => $value) {
            assert(is_bool($value) || $value === null || is_int($value) || is_float($value) || is_string($value) || is_array($value));
            $class->stmts[] = new ClassConst(
                [new Const_($constant, BuilderHelpers::normalizeValue($value))]
            );
        }

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $class->stmts[] = $this->buildProperty($reflectionProperty);
        }

        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $class->stmts[] = $this->buildMethod($reflectionMethod);
        }

        $namespace = $reflectionClass->getNamespaceName();

        if ($namespace === '') {
            return $statements;
        }

        return [new Namespace_(new Name(explode('\\', $namespace)), $statements)];
    }

    /**
     * @throws BadMethodCallException disabled method.
     *
     * @psalm-return never-return
     */
    public function getNode(): void
    {
        throw new BadMethodCallException('Disabled');
    }

    protected function buildProperty(ReflectionProperty $reflectionProperty): Node\Stmt\Property
    {
        $propertyBuilder = new Property($reflectionProperty->getName());

        if ($reflectionProperty->isPublic()) {
            $propertyBuilder->makePublic();
        }

        if ($reflectionProperty->isProtected()) {
            $propertyBuilder->makeProtected();
        }

        if ($reflectionProperty->isPrivate()) {
            $propertyBuilder->makePrivate();
        }

        if ($reflectionProperty->isStatic()) {
            $propertyBuilder->makeStatic();
        }

        if ($reflectionProperty->isDefault()) {
            $allDefaultProperties = $reflectionProperty->getDeclaringClass()->getDefaultProperties();

            $propertyBuilder->setDefault($allDefaultProperties[$reflectionProperty->getName()]);
        }

        return $propertyBuilder->getNode();
    }

    protected function buildMethod(ReflectionMethod $reflectionMethod): Node\Stmt\ClassMethod
    {
        $methodBuilder = new Method($reflectionMethod->getName());

        if ($reflectionMethod->isPublic()) {
            $methodBuilder->makePublic();
        }

        if ($reflectionMethod->isProtected()) {
            $methodBuilder->makeProtected();
        }

        if ($reflectionMethod->isPrivate()) {
            $methodBuilder->makePrivate();
        }

        if ($reflectionMethod->isStatic()) {
            $methodBuilder->makeStatic();
        }

        if ($reflectionMethod->isAbstract()) {
            $methodBuilder->makeAbstract();
        }

        if ($reflectionMethod->isFinal()) {
            $methodBuilder->makeFinal();
        }

        if ($reflectionMethod->returnsReference()) {
            $methodBuilder->makeReturnByRef();
        }

        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $methodBuilder->addParam($this->buildParameter($reflectionParameter));
        }

        // @todo should parse method body if possible (skipped for now)

        return $methodBuilder->getNode();
    }

    protected function buildParameter(ReflectionParameter $reflectionParameter): Node\Param
    {
        $parameterBuilder = new Param($reflectionParameter->getName());

        if ($reflectionParameter->isPassedByReference()) {
            $parameterBuilder->makeByRef();
        }

        if ($reflectionParameter->isArray()) {
            $parameterBuilder->setType('array');
        }

        if ($reflectionParameter->isCallable()) {
            $parameterBuilder->setType('callable');
        }

        $type = $reflectionParameter->getClass();

        if ($type !== null) {
            $parameterBuilder->setType($type->getName());
        }

        if ($reflectionParameter->isDefaultValueAvailable()) {
            if ($reflectionParameter->isDefaultValueConstant()) {
                $constantName = $reflectionParameter->getDefaultValueConstantName();

                assert($constantName !== null);

                $parameterBuilder->setDefault(new ConstFetch(new Name($constantName)));
            } else {
                $parameterBuilder->setDefault($reflectionParameter->getDefaultValue());
            }
        }

        return $parameterBuilder->getNode();
    }
}
