<?php

declare(strict_types=1);

// Shared by the examples: the autoloader of Composer, the example files in examples/input and the folder the examples
// write to - examples/output, or the folder EINVOICE_EXAMPLES_OUTPUT names.

$autoloader = null;
foreach ([__DIR__ . '/../vendor/autoload.php', __DIR__ . '/../../../autoload.php'] as $candidate) {
    if (is_file($candidate)) {
        $autoloader = $candidate;
        break;
    }
}
if ($autoloader === null) {
    fwrite(STDERR, "Run \"composer install\" first.\n");
    exit(1);
}
require $autoloader;

/**
 * The path of an example file in examples/input.
 */
function input(string $name): string
{
    $path = __DIR__ . '/input/' . $name;
    if (! is_file($path)) {
        throw new RuntimeException("There is no examples/input/$name.");
    }

    return $path;
}

/**
 * The path of a file an example writes.
 */
function output(string $name): string
{
    $directory = getenv('EINVOICE_EXAMPLES_OUTPUT') ?: __DIR__ . '/output';
    if (! is_dir($directory) && ! mkdir($directory, 0o777, true) && ! is_dir($directory)) {
        throw new RuntimeException("Cannot create the folder $directory.");
    }

    return $directory . '/' . $name;
}

/**
 * Writes a file of an example, says so and returns its path.
 */
function save(string $name, string $content): string
{
    $path = output($name);
    if (file_put_contents($path, $content) === false) {
        throw new RuntimeException("Cannot write $path.");
    }
    printf("wrote output/%s (%s KB)\n", $name, number_format(strlen($content) / 1024, 1));

    return $path;
}

/**
 * The file given on the command line (php example.php <file>), or the example file of that name.
 */
function fileArgument(string $inputFile): string
{
    $given = $_SERVER['argv'][1] ?? null;

    return is_string($given) && $given !== '' ? $given : input($inputFile);
}

/**
 * The content of a file, read or failed with its name.
 */
function contentOf(string $path): string
{
    $content = file_get_contents($path);
    if ($content === false) {
        throw new RuntimeException("Cannot read $path.");
    }

    return $content;
}
