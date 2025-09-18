<?php
//Dataset (associative array → object)

declare(strict_types=1);

$raw = [
  ['id'=>1,'title'=>'Intro Laravel','excerpt'=>null,'views'=>120],
  ['id'=>2,'title'=>'PHP 8 en pratique','excerpt'=>'Tour des nouveautés','views'=>300],
  ['id'=>3,'title'=>'Composer & Autoload','excerpt'=>null,'views'=>90],
];

//Step 1 — Model Articlewith promotion

class Article {
    public function __construct(
        public int $id,
        public string $title,
        public ?string $excerpt = null,
        public int $views = 0,
    ) {}

    public function slug(): string {
        $s = strtolower($this->title);
        $s = preg_replace('/[^a-z0-9]+/i', '-', $s);
        return trim($s, '-');
    }

    public function toArray(): array {
        return [
            'id'      => $this->id,
            'title'   => $this->title,
            'excerpt' => $this->excerpt,
            'views'   => $this->views,
            'slug'    => $this->slug(),
        ];
    }
}

//Step 2 — Static Factory:fromArray(array): self

class ArticleFactory {
    public static function fromArray(array $a): Article {
        // Valeurs par défaut + contrôles simples
        $id      = (int)($a['id'] ?? 0);
        $title   = trim((string)($a['title'] ?? 'Sans titre'));
        $excerpt = isset($a['excerpt']) ? (string)$a['excerpt'] : null;
        $views   = (int)($a['views'] ?? 0);

        return new Article($id, $title, $excerpt, $views);
    }
}

//Step 3 — Map an array of inputs to objects

$articles = array_map(
  fn(array $a) => ArticleFactory::fromArray($a),
  $raw
);

// Afficher un mini-rapport
foreach ($articles as $art) {
    $data = $art->toArray();
    echo "- {$data['title']} ({$data['views']} vues) — slug: {$data['slug']}\n";
}