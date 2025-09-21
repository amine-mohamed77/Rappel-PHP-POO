#!/usr/bin/env php
<?php
require __DIR__ . '/../vendor/autoload.php';

// /***********************************************
//  * THEORETICAL PART - DEFINITIONS & MOST IMPORTANT THINGS
//  ***********************************************/

// /* 1. BASIC DEFINITIONS (GLOSSARY) */
// - Composer: A dependency manager for PHP that also handles class autoloading.
// - Autoload: Automatically loads classes without requiring manual `require` calls.
// - PSR-4: A standard that maps a namespace (e.g., App\) to a folder (e.g., src/) so autoloading works.
// - Composer Scripts: Shortcuts/commands you can run with `composer run <script>` (e.g., PHP scripts or binaries).

// /* 2. MOST IMPORTANT THINGS TO MASTER */
// - Initialization: Set up a project with `composer init` or by writing `composer.json` directly.
// - PSR-4 Autoload: Map `App\` -> `src/` in composer.json, then run `composer dump-autoload` to generate vendor/autoload.php.
// - Scripts: Define handy commands like `start`, `seed`, `clean` in the `scripts` section, then run them with `composer run`.
// - Robustness: Always check files exist, JSON is valid, and handle errors with clear messages.
// - Fil Rouge: This script generates `articles.seed.json` that will later be used in Laravel (seeders and API).

// /* 3. KEY FUNCTIONS (in this project) */
// - App\Support\Str::slug(): Convert a string into a slug (e.g., "Hello PHP" -> "hello-php").
// - App\Support\Str::excerpt(): Shorten text and strip HTML tags.
// - App\Seed\ArticleFactory::make($count): Generate an array of articles with dynamic data.

// /***********************************************
//  * EXERCISE - SCRIPT SEED_ARTICLES.PHP
//  ***********************************************/
// Objective: Create a CLI script that generates article seed JSON and saves it into a file.
// Requirements:
// 1. Clean script: Remove the existing seed file (from composer.json).
// 2. ArticleFactory: Generate at least 1 tag per article as a basic requirement.
// 3. Option --topic: Bias article titles toward a given theme (e.g., Laravel).

use App\Seed\ArticleFactory;

// Read CLI options
$options = getopt('', ['count::', 'out::', 'topic::']);
$count = isset($options['count']) ? max(1, (int)$options['count']) : 10;
$out = $options['out'] ?? 'storage/articles.seed.json';
$topic = $options['topic'] ?? null;

// Create the directory if it doesn’t exist
$dir = dirname($out);
if (!is_dir($dir)) {
    if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
        fwrite(STDERR, "Error: Could not create directory $dir\n");
        exit(1);
    }
}

// Generate articles
$factory = new ArticleFactory();
$articles = $factory->make($count, $topic);

// Encode to JSON
$json = json_encode($articles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($json === false) {
    fwrite(STDERR, "JSON Error: " . json_last_error_msg() . "\n");
    exit(1);
}
file_put_contents($out, $json);

echo "Seed created in $out (" . count($articles) . " articles)\n";
exit(0);
?>