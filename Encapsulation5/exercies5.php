<?php
declare(strict_types=1);

class Article {
    public readonly int $id;          // immutable after construction
    private string $title;            // encapsulated
    private string $slug;             // derived
    private array $tags = [];         // encapsulated

    //step 4 : delete the $count and replace it with ArticleRepository

    public function __construct(int $id, string $title, array $tags = []) {
        if ($id <= 0) throw new InvalidArgumentException("ID must be greater than 0.");
        $this->id = $id;
        $this->setTitle($title);
        $this->tags = $tags;
    }

    /** Factory with LSB: returns the correct subclass if called from it */
    public static function fromTitle(int $id, string $title): static {
        return new static($id, $title);
    }

    /** Getters (minimal public API) */
    public function title(): string { return $this->title; }
    public function slug(): string { return $this->slug; }
    public function tags(): array { return $this->tags; }

        /** Setter encapsulating validation + slug update */
        public function setTitle(string $title): void {
            $title = trim($title);
            if ($title === '') 
            throw new InvalidArgumentException("Title is required.");

            $this->title = $title;
            $this->slug = static::slugify($title);
        }
    
        public function addTag(string $tag): void {
            $t = trim($tag);
            if ($t === '') throw new InvalidArgumentException("Tag cannot be empty.");
            $this->tags[] = $t;
        }

    /** Export to array for JSON preparation */
    public function toArray(): array {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'tags' => $this->tags,
        ];
    }


    /** Protected: can be overridden in subclass */
    protected static function slugify(string $value): string {
        $s = strtolower($value);
        $s = preg_replace('/[^a-z0-9]+/i', '-', $s) ?? '';
        return trim($s, '-');
    }
}

/** Subclass: specialization via protected and LSB */
class FeaturedArticle extends Article {
    protected static function slugify(string $value): string {
        return 'featured-' . parent::slugify($value);
    }
} 

/** Dépôt en mémoire (Étape 4) */
class ArticleRepository {
    /** @var array<string,Article> indexé par slug */
    private static array $articles = [];

    public static function save(Article $a): void {
        $slug = $a->slug();
        if (isset(self::$articles[$slug])) {
            throw new DomainException("Slug déjà existant : $slug");
        }
        self::$articles[$slug] = $a;
    }

    public static function count(): int {
        return count(self::$articles);
    }

    public static function all(): array {
        return array_values(self::$articles);
    }
}

// Demo

// Step 1 — Readonly test (must fail if uncommented)
// $a = new Article(1, "Test readonly");
// $a->id = 99; // ❌ Expected error: Cannot modify readonly property

// Steps 2 + 3 — Generate 3 articles and display them
$a1 = Article::fromTitle(1, "Encapsulation & visibilité en PHP");
$a2 = FeaturedArticle::fromTitle(2, "Lire moins, comprendre plus");
$a3 = Article::fromTitle(3, "Programmation orientée objet");

$a2->addTag("best");
$a3->addTag("oop");

// Save in the repository (slug uniqueness constraint)
ArticleRepository::save($a1);
ArticleRepository::save($a2);
ArticleRepository::save($a3);

// Display the table (JSON preparation)
print_r(array_map(fn($a) => $a->toArray(), ArticleRepository::all()));

// Display the number of items
echo "Total articles : " . ArticleRepository::count() . PHP_EOL;

// Step 4 — Testing the Uniqueness Constraint
// ArticleRepository::save(Article::fromTitle(4, "Read less, understand more")); // ❌ DomainException error
?>