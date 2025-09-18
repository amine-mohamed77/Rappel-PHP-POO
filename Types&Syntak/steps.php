
<?php
//////step 1

    function strOrNull(?string $s): ?string {
        $s = $s !== null ? trim($s) : null;
        return $s === '' ? null : $s;
    }
    echo strOrNull("   "); // => null
    echo "<br>";
    echo strOrNull(null);  // => null
    echo "<br>";
    echo strOrNull("  Hello ") ;
    
    echo "<br>";

    function intOrZero(int|string|null $v): int {
        return max(0, (int)($v ?? 0));
    }
  
    /////
   echo intOrZero(null) ;
   echo "<br>";
   echo intOrZero('50') ;
   echo "<br>";
   echo intOrZero(-10) ;

   //////step 2
   $normalized = [
    'title'     => trim((string)($input['title'] ?? 'Sans titre')),
    'excerpt'   => strOrNull($input['excerpt'] ?? null),
    'views'     => intOrZero($input['views'] ?? null),
    'published' => $input['published'] ?? true, // défaut si non défini
    'author'    => trim((string)($input['author'] ?? 'N/A')),
  ];
  echo "<pre>";
  print_r($normalized);
  echo "</pre>";


  ////////step3
  $defaults = [
    'per_page' => 10,
    'sort'     => 'created_desc',
  ];
  
  $userQuery = ['per_page' => null]; // simulateur d'entrée
  $userQuery['per_page'] ??= $defaults['per_page']; // 10
  $userQuery['sort']     ??= $defaults['sort'];     // 'created_desc'
  
  echo "<pre>";
  print_r( $userQuery );
  echo "</pre>";