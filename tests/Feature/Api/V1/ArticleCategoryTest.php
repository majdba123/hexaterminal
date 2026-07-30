<?php

namespace Tests\Feature\Api\V1;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\ArticleTag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_with_no_published_articles_are_excluded(): void
    {
        ArticleCategory::create(['slug' => 'empty', 'name' => ['en' => 'Empty']]);
        $populated = ArticleCategory::create(['slug' => 'populated', 'name' => ['en' => 'Populated']]);
        Article::create([
            'slug' => 'a1', 'title' => ['en' => 'A1'], 'is_published' => true,
            'article_category_id' => $populated->id,
        ]);

        $response = $this->getJson('/api/v1/public/article-categories')->assertOk();
        $slugs = collect($response->json('data'))->pluck('slug')->all();

        $this->assertSame(['populated'], $slugs);
        $this->assertSame(1, $response->json('data.0.published_count'));
    }

    public function test_articles_can_be_filtered_by_category_and_tag(): void
    {
        $category = ArticleCategory::create(['slug' => 'eng', 'name' => ['en' => 'Engineering']]);
        $tag = ArticleTag::create(['slug' => 'php', 'name' => ['en' => 'PHP']]);

        $matching = Article::create([
            'slug' => 'match', 'title' => ['en' => 'Match'], 'is_published' => true,
            'article_category_id' => $category->id,
        ]);
        $matching->tags()->attach($tag);

        Article::create(['slug' => 'other', 'title' => ['en' => 'Other'], 'is_published' => true]);

        $byCategory = $this->getJson('/api/v1/public/articles?category=eng')->assertOk();
        $this->assertSame(['match'], collect($byCategory->json('data'))->pluck('slug')->all());

        $byTag = $this->getJson('/api/v1/public/articles?tag=php')->assertOk();
        $this->assertSame(['match'], collect($byTag->json('data'))->pluck('slug')->all());
    }

    public function test_article_resource_includes_category_tags_and_reading_minutes(): void
    {
        $category = ArticleCategory::create(['slug' => 'eng', 'name' => ['en' => 'Engineering']]);
        Article::create([
            'slug' => 'a1', 'title' => ['en' => 'A1'],
            'body' => ['en' => str_repeat('word ', 400)],
            'is_published' => true, 'article_category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/v1/public/articles/a1')->assertOk();

        $this->assertSame('eng', $response->json('data.category.slug'));
        $this->assertSame(2, $response->json('data.reading_minutes'));
    }
}
