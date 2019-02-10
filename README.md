# Code Generation Utils for PHP-Parser

Code Generation Utils is a small library that is not yet intended for general use.

It is a small project that aims at collecting common solutions to code generation problems
that I often face, and for now it doesn't have a really solid structure.

I built it to workaround limitations that I often faced while working
with [`Zend\Code`](https://github.com/zendframework/zf2/tree/master/library/Zend/Code),
and it is mainly based on the logic of [PHP-Parser](https://github.com/nikic/PHP-Parser).

It will be stabilized together with [GeneratedHydrator](https://github.com/Ocramius/GeneratedHydrator)
and [ProxyManager](https://github.com/Ocramius/ProxyManager) when these two both have
reached at least version `1.0.0`.

| Tests | Releases | Downloads |
| ----- | -------- | ------- |
|[![Build Status](https://travis-ci.org/Ocramius/CodeGenerationUtils.png?branch=master)](https://travis-ci.org/Ocramius/CodeGenerationUtils) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Ocramius/CodeGenerationUtils/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Ocramius/CodeGenerationUtils/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/Ocramius/CodeGenerationUtils/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Ocramius/CodeGenerationUtils/?branch=master)|[![Latest Stable Version](https://poser.pugx.org/ocramius/code-generator-utils/v/stable.png)](https://packagist.org/packages/ocramius/code-generator-utils) [![Latest Unstable Version](https://poser.pugx.org/ocramius/code-generator-utils/v/unstable.png)](https://packagist.org/packages/ocramius/code-generator-utils)|[![Total Downloads](https://poser.pugx.org/ocramius/code-generator-utils/downloads.png)](https://packagist.org/packages/ocramius/code-generator-utils)|

## Installation

Supported installation method is via composer:

```sh
$ php composer.phar require ocramius/code-generator-utils
```

## Provided components

The provided components are generally related with code generation and related problems.

#### `CodeGenerationUtils\Autoloader`

This is a small callback-based autoloader component - it should be used when trying to autoload
generated classes.

#### `CodeGenerationUtils\FileLocator`

The FileLocator basically represents a map of generated class names to files where those classes
should be read from or written to. This component can be useful for non-PSR-0-compliant generated code.

#### `CodeGenerationUtils\GeneratorStrategy`

Provides logic to serialize a PHP-Parser AST to a class. Current strategies allow to:

 * Serialize an AST to a string
 * Serialize an AST to a string and evaluate it (via `eval()`) at runtime
 * Serialize an AST to a string and save it to a file (via `CodeGenerationUtils\FileLocator`)

#### `CodeGenerationUtils\Inflector`

Provides various utilities to:

 * Convert a generated code's [FQCN](http://php.net/manual/en/language.namespaces.rules.php)
   to the FQCN of the class from which it was generated
 * Generate the FQCN of a generated class given an original class name and some arbitrary
   parameters to be encoded (allows multiple generated classes per origin class)
 * Generate unique valid identifier names

#### `CodeGenerationUtils\ReflectionBuilder`

Very rudimentary converter that builds PHP-Parser AST nodes from Reflection objects (still WIP)

#### `CodeGenerationUtils\Visitor`

Various visitors used to manipulate classes, methods and properties in a given PHP-Parser AST

## Contributing

Please read the [CONTRIBUTING.md](https://github.com/Ocramius/CodeGenerationUtils/blob/master/CONTRIBUTING.md) contents
if you wish to help out!
