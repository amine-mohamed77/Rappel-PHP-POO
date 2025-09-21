<?php
// Composer is a tool in PHP that manages dependencies (libraries and packages your program needs) and sets up automatic class autoloading. This means, with Composer, you don’t need to manually require or include every PHP file, nor do you need to fetch libraries one by one. It’s like a manager that organizes your code and brings you the things you need.

// When do we use Composer?
// We use Composer in these cases:

// When we want to use new libraries: For example, if you want a library like guzzlehttp/guzzle to make HTTP requests, Composer fetches and installs it easily.
// When we have a large project: Composer organizes code with PSR-4 autoloading, so you can put your classes in a folder (e.g., src/) and Composer loads them automatically.
// When we want automated scripts: Composer allows you to define commands (scripts) like `composer run seed` to run tasks like generating files or running tests.
// When we want to organize the project: Composer works with a `composer.json` file that specifies the project’s name, version, and dependencies.

// Why use Composer?

// Ease of management: You don’t need to manually fetch libraries; Composer downloads them and ensures the correct versions.
// Autoload: It automatically loads your classes without needing `require` statements everywhere.
// Scripts: It allows you to define short commands (e.g., `composer run start`) to execute PHP scripts or binaries.
// Community and standards: Composer works with Packagist (a PHP library repository) and uses standards like PSR-4 for namespace mapping.
// Integration with frameworks: Many frameworks like Laravel, Symfony, and CodeIgniter require Composer for installation and organization.

// The goal of Composer
// The main goals are:

// Organize the project: With `composer.json`, you can define what your project needs (libraries, PHP version, autoload, scripts).
// Make code reusable: With PSR-4, you can organize classes in folders, and Composer loads them automatically.
// Save time: You don’t need to write excessive code for requiring files or manually fetching libraries; Composer does it for you.
// Ensure compatibility: It fetches the correct library versions and ensures there are no conflicts.
// Simplify CLI scripts: As shown in the course, you can create scripts like `start` or `seed` to generate files like `articles.seed.json` for use in Laravel.



// composer init
//  -> Initializes a new Composer project by creating a composer.json file in the current directory.

// composer dump-autoload
//  -> Regenerates the Composer autoloader files to include all classes and files from your project and its dependencies. 
// Whenever you add or change something in the autoload section, you must regenerate the autoload files.


// -----------------

// "autoload": {
//     "psr-4": {
//         "App\\": "src/"
//     }
// }

// autoload → This section defines how Composer should automatically load your PHP classes without requiring manual require or include.

// psr-4 → This is the autoloading standard Composer uses. PSR-4 maps namespaces to directories.

// "App\\" → This is the namespace prefix. Any PHP class starting with App\... will be handled by this rule.

// "src/" → This is the folder where those classes actually live.