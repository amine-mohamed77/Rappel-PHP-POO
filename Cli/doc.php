CLI (Command Line Interface)

It’s when you run programs directly in a terminal instead of clicking icons.

For PHP, that means launching scripts like:

php script.php arg1 arg2


Useful for automation, scripting, and system tasks.



------------

<!-- $argv / $argc -->

Special variables available in PHP when running from CLI.

$argv → array of arguments passed to the script.

$argv[0] is always the script name.

$argv[1], $argv[2], … are the arguments.

$argc → number of arguments in $argv.

// php demo.php hello world
print_r($argv);
echo $argc;


Output:

Array
(
    [0] => demo.php
    [1] => hello
    [2] => world
)
3


----------------
<!-- getopt -->

PHP function to parse command-line options (flags like -v, --input=file.txt).

Saves you from manually checking $argv.

// php script.php -v --input=test.txt
$options = getopt("v", ["input:"]);
print_r($options);


Output:

Array
(
    [v] => false
    [input] => test.txt
)


(false just means the flag has no associated value.)

-----------
<!-- STDIN / STDOUT / STDERR -->

Standard input/output streams:

STDIN → where a program reads input (usually keyboard, or data piped in).

STDOUT → normal program output (printed text).

STDERR → error messages (separate channel from normal output).

echo "hello" | php script.php


→ "hello" arrives in STDIN.
------------------------------

<!-- Code de sortie (Exit code) -->

A number returned when the program ends (exit(N) in PHP).

Convention:

0 = success

1 = generic error

2 = incorrect usage (e.g., missing arguments)

3 = Data error

Lets other programs/shell scripts know if your program ran correctly.

if ($argc < 2) {
    fwrite(STDERR, "Missing argument\n");
    exit(2); // usage error
}
echo "All good\n";
exit(0); // success




______________________________________________

Command to run:
php tool.php -v --input=data.json --limit=5

<!-- 1. Option definitions -->
$short = 'v'; // -v (bool)
$long  = ['input:', 'limit::', 'help', 'dry-run']; 


$short = 'v';

Defines short option -v.

No colon : after it → it’s just a boolean flag (present = true).

$long = [...] defines long options:

'input:' → requires a value (--input=data.json).

'limit::' → value is optional (--limit=5 or just --limit).

'help' → boolean flag (--help).

'dry-run' → boolean flag (--dry-run).

<!-- 2. Parsing arguments -->
$opts = getopt($short, $long);


getopt() parses $argv according to rules.

With the command php tool.php -v --input=data.json --limit=5,
$opts becomes:

[
    'v'      => false,       // -v present → flag
    'input'  => 'data.json', // --input
    'limit'  => '5',         // --limit
]


(false just means the flag has no associated value.)

<!-- 3. Extracting values -->
$verbose = array_key_exists('v', $opts);


$verbose = true if -v was given.

$input   = $opts['input'] ?? null;


$input = 'data.json'.

$limit   = isset($opts['limit']) ? (int)$opts['limit'] : null;


$limit = 5 (converted to integer).

$help    = array_key_exists('help', $opts);
$dryRun  = array_key_exists('dry-run', $opts);


$help = false (not passed).

$dryRun = false (not passed).

<!-- 4. Summary of the example run -->

For:

php tool.php -v --input=data.json --limit=5


Values will be:

$verbose = true

$input = "data.json"

$limit = 5

$help = false

$dryRun = false

👉 So basically, this script builds a flexible CLI parser where:

-v turns on verbose mode

--input=file chooses the input file

--limit[=N] limits how many items to process

--help shows usage

--dry-run simulates without applying changes


_________________________________________________________________
php tool.php -v --input=data.json --limit=5
php tool.php → runs the PHP script named tool.php.

<!-- -v -->

This is a short option (single -).

In many tools, -v means verbose mode, so the script may show extra details about what it’s doing.

Its exact effect depends on how tool.php is written.

<!-- --input=data.json -->

This is a long option (double --).

Here it likely tells the script to use data.json as the input file.

The script will probably read data from that JSON file to process.

<!-- --limit=5 -->

Another long option.

Usually sets a maximum number of items/records/entries to process, display, or output.

In this case, the script will likely only handle 5 items from data.json.

👉 The command runs tool.php, in verbose mode, using data.json as input, and only processing 5 items.

---

<!-- Verbose mode  -->
(usually triggered by -v) just means the program will give you more detailed output about what it’s doing.

Without -v: the script might only print the final result.

With -v: it might show extra steps like:

which file it’s reading

how many records it found

debug or progress messages

errors or warnings in more detail

It’s mainly for debugging or understanding what’s happening behind the scenes.
------------
<!-- --help (or -h) -->

Shows a help message that lists:

All available options/flags (-v, --input, --limit, etc.)

A short description of what each one does

Sometimes usage examples

You run it like this:

php tool.php --help


It doesn’t actually run the main logic of the script — it just prints instructions and exits.

<!-- --dry-run (sometimes written --dryrun) -->

Tells the program to simulate what it would do, without actually doing it.

Useful for previewing changes before committing them.

Example use cases:

A script that deletes files → with --dry-run, it only shows which files it would delete, but doesn’t actually delete them.