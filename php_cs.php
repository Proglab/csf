<?php

// @codeCoverageIgnoreStart
$finder = PhpCsFixer\Finder::create()
    ->exclude(['var', 'public', 'vendor', 'php_cs.php'])
    ->in(__DIR__);

return PhpCsFixer\Config::create()
    ->setFinder($finder);
// @codeCoverageIgnoreEnd
