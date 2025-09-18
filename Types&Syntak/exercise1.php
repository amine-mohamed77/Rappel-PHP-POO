<?php

function buildArticle(array $row): array {
    // Defaults
    $row['title']     ??= 'Sans titre';
    $row['author']    ??= 'N/A';
    $row['published'] ??= true;
    $row['views']     ??= 0;

    return [
        'title'     => (string) $row['title'],
        'excerpt'   => ($row['excerpt'] ?? '') === '' ? null : (string) $row['excerpt'],
        'views'     => max(0, (int) $row['views']),
        'published' => (bool) $row['published'],
        'author'    => (string) $row['author'],
    ];
}


$testData = [
    [],  // completely empty → all defaults
    ['views' => 0],  // views = 0 (should stay 0)
    ['excerpt' => ''],  // empty string → converted to null
    ['excerpt' => null], // null → stays null
    ['title' => 'My Article', 'author' => 'Amin'], // custom title & author, others default
    ['views' => -5, 'published' => 0], // negative views → 0, published false
    ['excerpt' => 'This is a summary.'], // excerpt filled, others default
];

echo "<pre>";
foreach ($testData as $data) {
    var_dump(buildArticle($data));
}
echo "</pre>";




    //////////////
    echo "<br>";

