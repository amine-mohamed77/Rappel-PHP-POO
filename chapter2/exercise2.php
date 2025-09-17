<?php

$articles = [
    ['id'=>1,'title'=>'Intro Laravel','slug'=>'intro-laravel','views'=>120,'author'=>'Amina','category'=>'php','published'=>true],
];

$published = array_values(array_filter($articles, fn($a) => $a['published'] ?? false));

$normalized = array_map(
  fn($a) => [
    'id'       => $a['id'],
    'slug'     => slugify($a['title']),
    'views'    => $a['views'],
    'author'   => $a['author'],
    'category' => $a['category'],
  ],
  $published
);

function slugify(string $text): string {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    return trim($text, '-');
}


usort($normalized, fn($x, $y) => $y['views'] <=> $x['views']);

$summary = array_reduce(
  $published,
  function($acc, $a) {
      $acc['count']      = ($acc['count'] ?? 0) + 1;
      $acc['views_sum']  = ($acc['views_sum'] ?? 0) + $a['views'];
      $cat = $a['category'];
      $acc['by_category'][$cat] = ($acc['by_category'][$cat] ?? 0) + 1;
      return $acc;
  },
  ['count'=>0, 'views_sum'=>0, 'by_category'=>[]]
);

print_r($normalized);
print_r($summary);
