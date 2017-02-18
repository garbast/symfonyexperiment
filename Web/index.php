<?php

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../vendor/autoload.php';
\Symfony\Component\Debug\Debug::enable();

$kernel = new \Evoweb\CurseDownloader\AppKernel('dev', true);
$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
