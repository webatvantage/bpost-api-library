<?php

declare(strict_types=1);

use Webatvantage\PhpCsFixer\Config\Config;

/*
 * Only the restructured code is formatted. The legacy tree still carries the
 * old @Symfony style and is excluded until its phase ports it away; shrink
 * these lists as each phase lands rather than reformatting code that is about
 * to be deleted.
 */
$legacySources = ['ApiCaller', 'Bpack247', 'Bpost', 'Common', 'Exception'];
$legacySourceFiles = [
	'Bpack247.php',
	'Bpost.php',
	'BpostException.php',
	'FormHandler.php',
	'Logger.php',
];

$legacyTests = ['Bpack247', 'Bpost', 'BpostApiExamples', 'Common', 'Exception', 'connection-tests'];
$legacyTestFiles = ['index.php', 'phpunit-bootstrap.php'];

$finder = PhpCsFixer\Finder::create()
	->in([__DIR__ . '/src', __DIR__ . '/tests'])
	->exclude([...$legacySources, ...$legacyTests])
	->notPath([...$legacySourceFiles, ...$legacyTestFiles])
	->name('*.php')
	->ignoreDotFiles(true)
	->ignoreVCS(true);

return Config::default()
	->setFinder($finder);
