<?php
namespace App\Seed;

use App\Support\Str;

// Class ArticleFactory: Generate seed JSON
final class ArticleFactory
{
    /** @var string[] */
    private array $authors = ['Amine', 'Sara', 'Youssef', 'Nadia'];
    /** @var string[] */
    private array $topics = ['PHP', 'Laravel', 'Mobile', 'UX', 'MySQL'];

    /**
     * Generate an array of articles
     * @param int $count Number of articles
     * @param string|null $topic Bias theme (if provided)
     * @return array<int, array<string, mixed>>
     */
    public function make(int $count, ?string $topic = null): array
    {
        $titles = [
            'Best practices ' . ($topic ?? 'PHP'),
            'Discover ' . ($topic ?? 'Eloquent'),
            'REST API in ' . ($topic ?? 'PHP'),
            'Pagination and filters',
            'Nice exceptions'
        ];

        $used = [];
        $rows = [];

        for ($i = 1; $i <= $count; $i++) {
            $title = $titles[($i - 1) % count($titles)] . " #$i";
            $slug = Str::slug($title);

            // Ensure slug uniqueness
            $base = $slug;
            $n = 2;
            while (isset($used[$slug])) {
                $slug = $base . '-' . $n++;
            }
            $used[$slug] = true;

            $content = "Sample content for « $title ». " .
                       "This article illustrates JSON seed generation in CLI.";

            // Ensure at least 1 mandatory tag
            $tags = array_unique(array_filter([
                $this->topics[array_rand($this->topics)], // Always include at least one tag
                (rand(0, 1) === 1) ? $this->topics[array_rand($this->topics)] : null,
                (rand(0, 1) === 1) ? $this->topics[array_rand($this->topics)] : null
            ]));

            $rows[] = [
                'title' => $title,
                'slug' => $slug,
                'excerpt' => Str::excerpt($content, 180),
                'content' => $content,
                'author' => $this->authors[array_rand($this->authors)],
                'published_at' => date('c', time() - rand(0, 60 * 60 * 24 * 30)),
                'tags' => $tags
            ];
        }

        return $rows;
    }
}
?>