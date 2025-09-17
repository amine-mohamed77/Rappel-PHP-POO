<?php
//Dataset
$articles = [
    ['id'=>1,'title'=>'Intro Laravel','category'=>'php','views'=>120,'author'=>'Amina','published'=>true,  'tags'=>['php','laravel']],
    ['id'=>2,'title'=>'PHP 8 en pratique','category'=>'php','views'=>300,'author'=>'Yassine','published'=>true,  'tags'=>['php']],
    ['id'=>3,'title'=>'Composer & Autoload','category'=>'outils','views'=>90,'author'=>'Amina','published'=>false, 'tags'=>['composer','php']],
    ['id'=>4,'title'=>'Validation FormRequest','category'=>'laravel','views'=>210,'author'=>'Sara','published'=>true,  'tags'=>['laravel','validation']],
  ];
  

//step 1
function slugify(string $title):string{
    $slug = strtolower($title); // We change the letters to lowercase
    $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
    return trim($slug,"-");
}
echo slugify("Intro Laravel");



//step 2

$published = array_values(
array_filter($articles,fn($a)=>["published"]??false)
);

echo "<pre>";
print_r($published);

echo "</pre>";
//////////////////

//step 3
$light = array_map(
    fn($a) => [
      'id'    => $a['id'],
      'title' => $a['title'],
      'slug'  => slugify($a['title']),
      'views' => $a['views'],
    ],
    $published
  );

  print_r($light);


  echo "<br>";


//step 4
// arrange the views largest to smallest
//take the first 3

$top =$light;
usort($top,fn($a,$b)=>$b['views']<=>$a['views']);
$top =array_slice($top,0,3);

print_r($top);


// step 5
//We see how many published articles each author has.

$byAuthor = array_reduce(
  $published,
  function($acc, $a) {
      $author = $a['author'];
      $acc[$author] = ($acc[$author] ?? 0) + 1;
      return $acc;
  },
  []
);


print_r($byAuthor);

//step 6

$allTags = array_merge(...array_map(fn($a) => $a['tags'], $published));

$tagFreq = array_reduce(
  $allTags,
  function($acc, $tag) {
      $acc[$tag] = ($acc[$tag] ?? 0) + 1;
      return $acc;
  },
  []
);
// print_r($allTags);


//Step 7
echo "Top 3 (views):\n";
foreach ($top as $a) {
  echo "- {$a['title']} ({$a['views']} vues) — {$a['slug']}\n";
}

echo "\nPar auteur:\n";
foreach ($byAuthor as $author => $count) {
  echo "- $author: $count article(s)\n";
}

echo "\nTags:\n";
foreach ($tagFreq as $tag => $count) {
  echo "- $tag: $count\n";
}
