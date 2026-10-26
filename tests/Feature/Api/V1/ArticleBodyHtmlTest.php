<?php

namespace Tests\Feature\Api\V1;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleBodyHtmlTest extends TestCase
{
    use RefreshDatabase;

    public function test_rich_text_body_is_returned_as_sanitized_html(): void
    {
        Article::create([
            'slug' => 'rich', 'title' => ['en' => 'Rich'], 'is_published' => true,
            'body' => ['en' => '<h2>Heading</h2><p onclick="x()">Text</p><script>alert(1)</script>'],
        ]);

        $html = $this->getJson('/api/v1/public/articles/rich')->assertOk()->json('data.body_html');

        $this->assertStringContainsString('<h2>Heading</h2>', $html);
        $this->assertStringContainsString('<p>Text</p>', $html);
        $this->assertStringNotContainsString('script', $html);
        $this->assertStringNotContainsString('onclick', $html);
    }

    public function test_plain_text_body_is_escaped_into_paragraphs(): void
    {
        Article::create([
            'slug' => 'plain', 'title' => ['en' => 'Plain'], 'is_published' => true,
            'body' => ['en' => "First line\nstill first\n\nSecond 1 < 2"],
        ]);

        $html = $this->getJson('/api/v1/public/articles/plain')->assertOk()->json('data.body_html');

        $this->assertSame("<p>First line<br />\nstill first</p><p>Second 1 &lt; 2</p>", $html);
    }

    public function test_empty_body_has_null_html(): void
    {
        Article::create(['slug' => 'empty', 'title' => ['en' => 'Empty'], 'is_published' => true]);

        $this->getJson('/api/v1/public/articles/empty')->assertOk()->assertJsonPath('data.body_html', null);
    }
}
