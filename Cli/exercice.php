#!/usr/bin/env php
<?php
declare(strict_types=1);

// Objective: Prepare a script that reads CSV from --input (file or STDIN), converts it to normalized JSON, and writes to STDOUT.
// Requirements:
// 1. Options: --input=PATH or -, --published-only, --limit[=N], --help
// 2. Required CSV columns: title, excerpt, views, published, author
// 3. Normalization: views=int, published=bool, empty fields -> default values
// 4. If error, write to STDERR + exit code (>0)
// 5. Test CSV and example call
// Additional explanation: This script is a CLI tool for processing article data from CSV format into JSON. It handles input validation, data normalization, filtering based on options, and outputs pretty-printed JSON. It's designed for automation tasks, such as seeding databases or data pipelines.

// Define constants to organize exit codes
// Additional explanation: These constants provide clear, named values for different exit statuses, making the code more readable and maintainable. EXIT_OK indicates success, while others signal specific error types.
const EXIT_OK = 0; // Success
const EXIT_USAGE = 2; // Error in usage (e.g., missing --input)
const EXIT_DATA_ERROR = 3; // Error in data (e.g., file not found)

// Function usage(): Writes the help message to STDOUT
// Additional explanation: This function displays usage instructions, including available options and an example command. It's called when --help is provided or when there's a usage error, helping users understand how to run the script correctly.
function usage(): void {
    $msg = <<<TXT
Seed Generator — Options:
  --input=PATH    Path to the CSV file or '-' for STDIN (required)
  --limit[=N]     Limit the number of articles to process (optional)
  --published-only  Only process published articles
  -v              Verbose mode (shows more details)
  --help          Display this help message

Example:
  php bin/seed_generator.php --input=/tmp/articles.csv --published-only --limit=2
  cat data.csv | php bin/seed_generator.php --input=-
TXT;
    fwrite(STDOUT, $msg . PHP_EOL);
}

// Function readCsvFrom(): Reads the CSV from a file or STDIN
// Additional explanation: This function opens the input source (file or STDIN), reads the header and rows, validates the structure, and returns an array of associative arrays for each row. It handles errors by writing to STDERR and exiting with an appropriate code, ensuring robust input handling.
function readCsvFrom(string $input): array {
    // If input = '-', read from STDIN, otherwise from file
    // Additional explanation: Using STDIN allows piping data into the script, making it flexible for Unix-style command chaining.
    $fh = ($input === '-') ? STDIN : @fopen($input, 'r');
    if ($fh === false) {
        fwrite(STDERR, "Error: Cannot open input '$input'\n");
        exit(EXIT_DATA_ERROR);
    }

    // Read the header (column titles)
    // Additional explanation: The header is crucial for mapping column names to values in each row using array_combine().
    $header = fgetcsv($fh);
    if ($header === false || empty($header)) {
        fwrite(STDERR, "Error: CSV is empty or header is invalid\n");
        if ($fh !== STDIN) fclose($fh);
        exit(EXIT_DATA_ERROR);
    }

    // Read the lines and store in rows
    // Additional explanation: This loop processes each row, ensuring it matches the header's column count to avoid malformed data.
    $rows = [];
    while (($line = fgetcsv($fh)) !== false) {
        if (count($line) === count($header)) { // Ensure number of columns matches
            $rows[] = array_combine($header, $line);
        }
    }
    if ($fh !== STDIN) fclose($fh);

    if (empty($rows)) {
        fwrite(STDERR, "Error: No data found in CSV\n");
        exit(EXIT_DATA_ERROR);
    }

    return $rows;
}

// Function normalizeRow(): Organizes the data for each line
// Additional explanation: This function cleans and standardizes each row's data, handling missing values with defaults, type conversions (e.g., string to int or bool), and trimming whitespace. It ensures consistent output format regardless of input variations.
function normalizeRow(array $row): array {
    return [
        'title' => trim((string)($row['title'] ?? 'Untitled')), // Clean the title
        // Additional explanation: Trimming removes leading/trailing spaces; default to 'Untitled' if missing.
        'excerpt' => ($row['excerpt'] ?? null) !== '' ? (string)$row['excerpt'] : null, // Excerpt if present
        // Additional explanation: Preserves excerpt only if non-empty; otherwise, sets to null for clean JSON.
        'views' => (int)($row['views'] ?? 0), // Views -> int
        // Additional explanation: Converts views to integer, defaulting to 0 if absent or invalid.
        'published' => in_array(strtolower((string)($row['published'] ?? 'true')), ['1', 'true', 'yes', 'y', 'on'], true), // Published -> bool
        // Additional explanation: Supports various truthy strings for flexibility, converting to a strict boolean.
        'author' => (string)($row['author'] ?? 'N/A'), // Author if not present -> N/A
        // Additional explanation: Defaults to 'N/A' for missing authors, ensuring no null values in this field.
    ];
}

// Main program
// Additional explanation: This section parses command-line options using getopt(), validates required inputs, and orchestrates the data processing flow.
$opts = getopt('v', ['input:', 'published-only', 'limit::', 'help']);

// If --help, display help and exit
// Additional explanation: Early exit for help requests to provide immediate feedback without further processing.
if (array_key_exists('help', $opts)) {
    usage();
    exit(EXIT_OK);
}

// Validate --input
// Additional explanation: Ensures the required --input option is provided; otherwise, shows usage and exits with error.
$input = $opts['input'] ?? null;
if ($input === null) {
    fwrite(STDERR, "Error: --input must be provided (path or '-')\n\n");
    usage();
    exit(EXIT_USAGE);
}

// Read the remaining options
// Additional explanation: Parses optional flags like limit (with min value 1), published-only filter, and verbose mode for debugging output.
$limit = isset($opts['limit']) ? max(1, (int)$opts['limit']) : null;
$publishedOnly = array_key_exists('published-only', $opts);
$verbose = array_key_exists('v', $opts);

// If verbose, display details
// Additional explanation: Verbose mode logs actions to STDOUT, useful for monitoring script execution without affecting the main JSON output.
if ($verbose) {
    fwrite(STDOUT, "[Verbose] Reading from " . ($input === '-' ? 'STDIN' : $input) . PHP_EOL);
}

// Read the CSV and transform the data
// Additional explanation: Wraps the reading and normalization in a try-catch to handle any unexpected exceptions gracefully.
try {
    $rows = readCsvFrom($input);
    $items = array_map('normalizeRow', $rows);
} catch (Throwable $e) {
    fwrite(STDERR, "Error in CSV: " . $e->getMessage() . PHP_EOL);
    exit(EXIT_DATA_ERROR);
}

// If --published-only, filter only published ones
// Additional explanation: Uses array_filter() to keep only rows where 'published' is true, re-indexing with array_values() for clean array output.
if ($publishedOnly) {
    $items = array_values(array_filter($items, fn($a) => $a['published']));
}

// If --limit, limit the number of lines
// Additional explanation: Slices the array to the specified limit, processing only the first N items for performance or testing.
if ($limit !== null) {
    $items = array_slice($items, 0, $limit);
}

// Write the JSON to STDOUT
// Additional explanation: Encodes the processed data as pretty-printed JSON with Unicode support, ensuring readability and compatibility.
try {
    echo json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, "Error in JSON encode: " . $e->getMessage() . PHP_EOL);
    exit(EXIT_DATA_ERROR);
}

// Exit successfully
// Additional explanation: Explicit exit with success code, though PHP would exit 0 by default; this makes the intent clear.
exit(EXIT_OK);
?>


<!-- php exercice.php --input=/Cli/articles.csv -->
<!-- php Rappel-PHP-POO/Cli/exercice.php --input=Rappel-PHP-POO/Cli/articles.csv -->


<!-- php exercice.php --input=articles.csv (The Right One) -->


<!-- type <<CSV > /tmp/articles.csv
title,excerpt,views,published,author
Intro Laravel,,120,true,Amina
PHP 8 en pratique,Tour des nouveautés,300,true,Yassine
Composer & Autoload,,90,false,Amina
CSV -->