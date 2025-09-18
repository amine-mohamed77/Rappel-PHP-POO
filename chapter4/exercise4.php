<?php
declare(strict_types=1);

// 1 
function validateArticle(array $a): void {
    if (!isset($a['title']) || !is_string($a['title']) || $a['title'] === '') {
        throw new DomainException("Article invalide: 'title' requis.");
    }
    if (!isset($a['slug']) || !is_string($a['slug']) || $a['slug'] === '') {
        throw new DomainException("Article invalide: 'slug' requis.");
    }
}

// 2 

function loadJson(string $path): array {
    $raw = @file_get_contents($path);
    if ($raw === false) {
        throw new RuntimeException("Fichier introuvable ou illisible: $path");
    }
    try {
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $je) {
        throw new RuntimeException("JSON invalide: $path", previous: $je);
    }
    if (!is_array($data)) {
        throw new UnexpectedValueException("Le JSON doit contenir un tableau racine.");
    }
    return $data;
}

// 3 

function main(array $argv): int {
    $path = $argv[1] ?? 'articles.input.json';
    $articles = loadJson($path);
    foreach ($articles as $i => $a) {
        validateArticle($a);
    }
    echo "[OK] $path: " . count($articles) . " article(s) valides." . PHP_EOL;
    return 0;
}


// 4

try {
    exit(main($argv));
} catch (Throwable $e) {
    fwrite(STDERR, "[ERR] " . $e->getMessage() . PHP_EOL);
    if ($e->getPrevious()) {
        fwrite(STDERR, "Cause: " . get_class($e->getPrevious()) . " — " . $e->getPrevious()->getMessage() . PHP_EOL);
    }
    exit(1);
}


?>


<!-- Key points  -->

Exception: Used for recoverable conditions, part of the traditional exception handling system.
Error: Represents critical issues (e.g., runtime errors), catchable since PHP 7.
Throwable: The common interface for both, allowing unified error handling in try-catch blocks.

Exceptions are used at the right granularity:

        DomainException: for invalid article data,

        RuntimeException: for file/JSON issues,

        UnexpectedValueException: for unexpected structure.

        Safe JSON decoding with JSON_THROW_ON_ERROR.

Propagation + rethrow: You catch JsonException, add context, then rethrow.

Top-level catch with Throwable: no uncontrolled crash, every error gets reported.

Exit codes:

0 → success,

1 → failure.

This is exactly the recommended structure for robust CLI (command line interface) PHP scripts. 


A command line interface (CLI) is a text-based interface 
where you can input commands that interact with a computer's operating system. 
The CLI operates with the help of the default shell, 
which is between the operating system and the user.