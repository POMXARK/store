<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__) // Укажите директорию вашего проекта
    ->name('*.php') // Укажите тип файлов, которые нужно обрабатывать
    ->notPath('vendor') // Исключите директорию vendor
    ->notPath('var'); // Исключите директорию var

return (new Config())
    ->setRules([
        '@Symfony' => true, // Использование правил Symfony
        'array_syntax' => ['syntax' => 'short'], // Использование короткого синтаксиса массива
        'no_unused_imports' => true, // Удаление неиспользуемых импортов
        'global_namespace_import' => true,
        'declare_strict_types' => true,
        'single_quote' => true,
        // Другие правила могут быть добавлены по мере необходимости
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder);
