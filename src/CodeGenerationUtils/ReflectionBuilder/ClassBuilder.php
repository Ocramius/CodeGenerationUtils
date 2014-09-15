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

namespace CodeGenerationUtils\ReflectionBuilder;

use PhpParser\Builder\Method;
use PhpParser\Builder\Param;
use PhpParser\Builder\Property;
use PhpParser\BuilderAbstract;
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

/**
 * Rudimentary utility to build an AST from a reflection class
 *
 * @todo should be splitted into various utilities like this one and eventually replace `Zend\Code\Generator`
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class ClassBuilder extends BuilderAbstract
{
    /**
     * @param \ReflectionClass $reflectionClass
     *
     * @return PhpParser\Node[]
     */
    public function fromReflection(ReflectionClass $reflectionClass)
    {
        $class = new Class_($reflectionClass->getShortName());
        $stmts = array($class);

        if ($parentClass = $reflectionClass->getParentClass()) {
            $class->extends = new FullyQualified($parentClass->getName());
        }

        $interfaces = array();

        foreach ($reflectionClass->getInterfaces() as $reflectionInterface) {
            $interfaces[] = new FullyQualified($reflectionInterface->getName());
        }

        $class->implements = $interfaces;

        foreach ($reflectionClass->getConstants() as $constant => $value) {
            $class->stmts[] = new ClassConst(
                array(new Const_($constant, $this->normalizeValue($value)))
            );
        }

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $class->stmts[] = $this->buildProperty($reflectionProperty);
        }

        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $class->stmts[] = $this->buildMethod($reflectionMethod);
        }

        if (! $namespace = $reflectionClass->getNamespaceName()) {
            return $stmts;
        }

        return array(new Namespace_(new Name(explode('\\', $namespace)), $stmts));
    }

    /**
     * @throws \BadMethodCallException disabled method
     */
    public function getNode()
    {
        throw new \BadMethodCallException('Disabled');
    }

    /**
     * @param ReflectionProperty $reflectionProperty
     *
     * @return \PhpParser\Node\Stmt\Property
     */
    protected function buildProperty(ReflectionProperty $reflectionProperty)
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

    /**
     * @param ReflectionMethod $reflectionMethod
     *
     * @return \PhpParser\Node\Stmt\ClassMethod
     */
    protected function buildMethod(ReflectionMethod $reflectionMethod)
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

    /**
     * @param ReflectionParameter $reflectionParameter
     *
     * @return \PhpParser\Node\Param
     */
    protected function buildParameter(ReflectionParameter $reflectionParameter)
    {
        $parameterBuilder = new Param($reflectionParameter->getName());

        if ($reflectionParameter->isPassedByReference()) {
            $parameterBuilder->makeByRef();
        }

        if ($reflectionParameter->isArray()) {
            $parameterBuilder->setTypeHint('array');
        }

        if (method_exists($reflectionParameter, 'isCallable') && $reflectionParameter->isCallable()) {
            $parameterBuilder->setTypeHint('callable');
        }

        if ($type = $reflectionParameter->getClass()) {
            $parameterBuilder->setTypeHint($type->getName());
        }

        if ($reflectionParameter->isDefaultValueAvailable()) {
            if (method_exists($reflectionParameter, 'isDefaultValueConstant')
                && $reflectionParameter->isDefaultValueConstant()
            ) {
                $parameterBuilder->setDefault(
                    new ConstFetch(
                        new Name($reflectionParameter->getDefaultValueConstantName())
                    )
                );
            } else {
                $parameterBuilder->setDefault($reflectionParameter->getDefaultValue());
            }
        }

        return $parameterBuilder->getNode();
    }
}
