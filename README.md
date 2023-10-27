# Scim Schema

SCIM schema PHP library with support for both v1 and v2.

> This library is a fork of https://github.com/tmilos/scim-s to make it compatible with modern PHP

> **Note:** This library is still work in progress, and you are welcome to help and contribute

> It was made by the specs from [SimpleCloud](http://www.simplecloud.info/) and by the example documents generated by
[PowerDMS/Owin.Scim](https://github.com/PowerDMS/Owin.Scim)

> Do not miss [SCIM Filter Parser](https://github.com/tmilos/scim-filter-parser) !

[![Author](http://img.shields.io/badge/author-@tmilos-blue.svg?style=flat-square)](https://twitter.com/tmilos77)
[![Build Status](https://travis-ci.org/tmilos/scim-schema.svg?branch=master)](https://travis-ci.org/tmilos/scim-schema)
[![Coverage Status](https://coveralls.io/repos/github/tmilos/scim-schema/badge.svg?branch=master)](https://coveralls.io/github/tmilos/scim-schema?branch=master)
[![Quality Score](https://img.shields.io/scrutinizer/g/tmilos/scim-schema.svg?style=flat-square)](https://scrutinizer-ci.com/g/tmilos/scim-schema)
[![License](https://img.shields.io/packagist/l/tmilos/scim-schema.svg)](https://packagist.org/packages/tmilos/scim-schema)
[![Packagist Version](https://img.shields.io/packagist/v/tmilos/scim-schema.svg?style=flat-square)](https://packagist.org/packages/tmilos/scim-schema)

# Install

Install Scim Schema using composer:

```bash
composer require protonlabs/scim-schema
```

# Usage

### Schema

Build default schema:

```php
$schemaBuilder = new SchemaBuilderV2(); // or SchemaBuilderV1

$groupSchema = $schemaBuilder->getGroup();
$userSchema  = $schemaBuilder->getUser();
$enterpriseUserSchema = $schemaBuilder->getEnterpriseUser();
$schemaSchema = $schemaBuilder->getSchema();
$serviceProviderConfigSchema = $schemaBuilder->getServiceProviderConfig();
$resourceTypeSchema = $schemaBuilder->getResourceType();
```

Or build your own custom schema:

```php
$schema = new Schema();

$schema->setName('CustomSchema');

$schema->addAttribute(
    AttributeBuilder::create('name', ScimConstants::ATTRIBUTE_TYPE_STRING, 'Name of the object')
        ->setMutability(false)
        ->getAttribute()
);
```

And serialize the scim schema object

```php
$schema = (new SchemaBuilderV2())->getUser();

$schema->serializeObject();
```

### Schema validation

An object can be validated against a schema:

```php
/** @var array $object */
$object = getTheObjectAsArray();

$validator = new SchemaValidator();
$objectSchema = getTheSchema();
$schemaExtensions = getSchemaExtensions();

$validationResult = $validator->validate(
    $object,
    $objectSchema,
    $schemaExtensions
);

if (!$validationResult->getErrors()) {
    // cool!
} else {
    print implode("\n", $validationResult->getErrorsAsStrings());
}
```
