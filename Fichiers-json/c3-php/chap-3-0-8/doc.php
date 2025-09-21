<!-- 1. Key Definitions (Glossary) -->

UTF-8: A recommended text encoding. It should be used without BOM (Byte Order Mark) to ensure compatibility.

JSON_THROW_ON_ERROR: An option for json_decode() or json_encode() in PHP that makes them throw a JsonException if there is an error in the JSON.

JSON Encode Options

JSON_PRETTY_PRINT: Makes the JSON nicely formatted and easy to read.

JSON_UNESCAPED_UNICODE: Keeps Unicode characters (e.g., Arabic, Japanese) unescaped.

JSON_UNESCAPED_SLASHES: Prevents escaping of / in strings (useful for URLs).

JSON_INVALID_UTF8_SUBSTITUTE: Replaces invalid UTF-8 characters with a substitute.

Safe File Writing

mkdir(..., recursive: true): Creates missing parent directories automatically.

file_put_contents(..., LOCK_EX): Locks the file while writing to avoid conflicts.

I/O

Input/Output: Refers to reading and writing files.

<!-- 2. Most Important Concepts to Master -->

Centralization: Use reusable functions like loadJson() and saveJson() to keep code clean and maintainable.

Robustness: Use try-catch and JSON_THROW_ON_ERROR to catch errors, and write clear error messages to STDERR.

Validation: Before saving data, check that required fields (e.g., title and slug) are not empty. Throw a DomainException if validation fails.

Atomic Write: Write data to a temporary file (e.g., .tmp) and then rename it to the final filename to prevent partial writes.

CLI Integration: Accept command-line arguments from $argv (like file paths or number of articles) to generate dynamic data.

Merging Data: Read from an extra file (e.g., articles.extra.json) and merge it with the main data, ensuring no duplicate slugs.

<!-- 3. Essential Functions -->

loadJson($path): Reads JSON from a file, checks that the file exists and the JSON is valid, then returns an array.

saveJson($path, $data): Saves JSON to a file using atomic writing, ensures the directory exists, and guarantees proper encoding.

slugify($value): Converts a string into a slug (e.g., "Hello PHP" → "hello-php").

validateArticle($article): Ensures that required fields (title and slug) are not empty.

generateArticles($count): Dynamically generates a number of articles.

mergeArticles($main, $extra): Combines articles from multiple sources, ensuring no duplicate slugs.

<!-- ----------------------------- -->
/***********************************************
 * EXERCISE - CHALLENGE N1+
 ***********************************************/
// Goal: Build a CLI script that reads and writes JSON robustly, with:
// 1. Atomic write: Write to a tmp file then rename.
// 2. Validation: Ensure title and slug are not empty, throw DomainException if invalid.
// 3. CLI: Accept path from $argv[1] and number of articles from $argv[2].
// 4. Import/merge: Read articles.extra.json and merge without duplicate slugs.

<?php
// Exit code constants
const EXIT_OK = 0;          // Success
const EXIT_USAGE = 2;       // Usage error (e.g., missing arguments)
const EXIT_DATA_ERROR = 3;  // Data error (e.g., invalid file or JSON)

// Function usage(): Print help to STDOUT
function usage(): void {
    $msg = <<<TXT
JSON IO — Options:
  php json_io.php [path] [article_count]
  path            Path to the JSON file (default: storage/seeds/articles.seed.json)
  article_count   Number of articles to generate (default: 2)
  --help          Show this help message

Example:
  php json_io.php storage/seeds/articles.seed.json 3
TXT;
    fwrite(STDOUT, $msg . PHP_EOL);
}

// Function loadJson(): Read JSON from file robustly
function loadJson(string $path): array {
    $raw = @file_get_contents($path);
    if ($raw === false) {
        throw new RuntimeException("File not found or unreadable: $path");
    }
    try {
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            throw new RuntimeException("Invalid JSON in $path: must be an array");
        }
        return $data;
    } catch (JsonException $e) {
        throw new RuntimeException("Invalid JSON in $path", 0, $e);
    }
}

// Function saveJson(): Write JSON atomically
function saveJson(string $path, array $data): void {
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    try {
        $json = json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
        );
        if ($json === false) {
            throw new RuntimeException("JSON encoding error");
        }
    } catch (Throwable $e) {
        throw new RuntimeException("Unable to encode JSON", 0, $e);
    }

    $tmpPath = $path . '.tmp';
    $ok = @file_put_contents($tmpPath, $json . PHP_EOL, LOCK_EX);
    if ($ok === false) {
        throw new RuntimeException("Unable to write to $tmpPath");
    }

    if (!rename($tmpPath, $path)) {
        throw new RuntimeException("Unable to rename $tmpPath to $path");
    }
}

// Function slugify(): Convert string to slug
function slugify(string $value): string {
    $s = strtolower($value);
    $s = preg_replace('/[^a-z0-9]+/i', '-', $s) ?? '';
    return trim($s, '-');
}

// Function validateArticle(): Validate article
function validateArticle(array $article): void {
    if (empty($article['title']) || !is_string($article['title'])) {
        throw new DomainException("Title must be a non-empty string");
    }
    if (empty($article['slug']) || !is_string($article['slug'])) {
        throw new DomainException("Slug must be a non-empty string");
    }
}

// Function generateArticles(): Dynamically generate articles
function generateArticles(int $count): array {
    $articles = [];
    for ($i = 1; $i <= $count; $i++) {
        $title = "Article $i: Intro to PHP";
        $articles[] = [
            'id' => $i,
            'title' => $title,
            'slug' => slugify($title),
            'excerpt' => "Description of article $i about PHP.",
            'tags' => ['php', "article$i"],
        ];
    }
    return $articles;
}

// Function mergeArticles(): Merge articles without duplicate slugs
function mergeArticles(array $main, array $extra): array {
    $slugs = array_column($main, 'slug');
    $merged = $main;

    foreach ($extra as $article) {
        if (!in_array($article['slug'] ?? '', $slugs)) {
            $merged[] = $article;
            $slugs[] = $article['slug'] ?? '';
        }
    }
    return $merged;
}

// Main program
$opts = getopt('', ['help']);
if (array_key_exists('help', $opts)) {
    usage();
    exit(EXIT_OK);
}

// Read arguments from CLI
$path = $argv[1] ?? __DIR__ . '/storage/seeds/articles.seed.json';
$count = isset($argv[2]) ? max(1, (int)$argv[2]) : 2;

// Generate articles
$articles = generateArticles($count);

// Validate each article
foreach ($articles as $article) {
    try {
        validateArticle($article);
    } catch (DomainException $e) {
        fwrite(STDERR, "[ERR] Validation error: " . $e->getMessage() . PHP_EOL);
        exit(EXIT_DATA_ERROR);
    }
}

// Read and merge from articles.extra.json if exists
$extraPath = __DIR__ . '/storage/seeds/articles.extra.json';
$extraArticles = [];
if (file_exists($extraPath)) {
    try {
        $extraArticles = loadJson($extraPath);
        $articles = mergeArticles($articles, $extraArticles);
    } catch (Throwable $e) {
        fwrite(STDERR, "[ERR] Error reading articles.extra.json: " . $e->getMessage() . PHP_EOL);
        exit(EXIT_DATA_ERROR);
    }
}

// Write seed file
try {
    saveJson($path, $articles);
    fwrite(STDOUT, "[OK] Seed written to $path\n");

    // Reload and verify
    $loaded = loadJson($path);
    fwrite(STDOUT, "[OK] Loaded: " . count($loaded) . " article(s).\n");
    fwrite(STDOUT, "First title: " . ($loaded[0]['title'] ?? 'N/A') . PHP_EOL);

    exit(EXIT_OK);
} catch (Throwable $e) {
    fwrite(STDERR, "[ERR] " . $e->getMessage() . PHP_EOL);
    if ($e->getPrevious()) {
        fwrite(STDERR, "Cause: " . get_class($e->getPrevious()) . " — " . $e->getPrevious()->getMessage() . PHP_EOL);
    }
    exit(EXIT_DATA_ERROR);
}
?>