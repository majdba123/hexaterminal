<?php

namespace Database\Seeders;

use App\Models\FaqItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use JsonException;

class FaqItemsSeeder extends Seeder
{
    private const DATA_FILE = 'database/seeders/data/faq_seed_data.json';

    public function run(): void
    {
        foreach ($this->faqDefinitions() as $definition) {
            $faq = FaqItem::firstOrNew([
                'question->en' => $definition['question']['en'],
            ]);

            $faq->fill([
                'question' => $definition['question'],
                'answer' => $definition['answer'],
                'category' => null,
                'is_published' => true,
                'sort_order' => (int) $definition['sort_order'],
            ]);

            $faq->save();
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function faqDefinitions(): array
    {
        try {
            $data = json_decode(
                File::get(base_path(self::DATA_FILE)),
                true,
                flags: JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $exception) {
            throw new \RuntimeException('The approved FAQ seed data is invalid JSON.', previous: $exception);
        }

        if (! is_array($data['faqs'] ?? null)) {
            throw new \RuntimeException('The approved FAQ seed data has no faqs collection.');
        }

        return $data['faqs'];
    }
}
