# Hayo Decoder for PHP

A PHP library that parses Hayo source code and creates an Abstract Syntax Tree (AST), which can then be compiled into a target language.

> **Note:** This project is not useful on its own as it is a component of the [php-hayo](https://github.com/tacoberu/php-hayo) project.

## About Hayo

Hayo is a purely functional scripting language. A script is passed as a string, compiled into a function, and called with concrete data. The result is always the last evaluated expression. No side effects are possible.

For more information about the language syntax, see [SYNTAX.md](SYNTAX.md).

## Installation

You can install the package via Composer:

```bash
composer require tacoberu/hayo-decoder
```

## Usage

```php
use Taco\Hayo\HayoDecoder;

$decoder = new HayoDecoder();
$ast = $decoder->decode('... hayo source code ...');
```
