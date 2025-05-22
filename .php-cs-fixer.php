<?php

$finder = PhpCsFixer\Finder::create()
    ->in('src')
;

$header = <<<EOT
This file is part of the tmilos/scim-schema package.

(c) Milos Tomic <tmilos@gmail.com>

This source file is subject to the MIT license that is bundled
with this source code in the file LICENSE.
EOT;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'simplified_null_return' => false,
        'phpdoc_no_empty_return' => false,
        'yoda_style' => false,
        'self_accessor' => false,
        'no_mixed_echo_print' => ['use' => 'print'],
        'header_comment' => ['header' => $header],
    ])
    ->setUsingCache(false)
    ->setFinder($finder)
;
