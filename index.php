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
echo "<pre>";
var_dump(buildArticle([]));
// title: "Sans titre", excerpt: null, views: 0, published: true, author: "N/A"
echo "</pre>";

echo "<pre>";
var_dump(buildArticle(['views' => 0]));
// views: 0
echo "</pre>";

echo "<pre>";
var_dump(buildArticle(['excerpt' => '']));
// excerpt: null
echo "</pre>";

echo "<pre>";
var_dump(buildArticle(['excerpt' => null]));
// excerpt: null
echo "</pre>";

/////////////////////:



    

