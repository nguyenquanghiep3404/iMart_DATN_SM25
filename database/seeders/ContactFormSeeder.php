<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ContactForm;


class ContactFormSeeder extends Seeder
{
    public function run(): void
    {
        // ContactForm::truncate(); // Cẩn thận
        ContactForm::factory(10)->create();
        $this->command->info('Contact Form submissions seeded successfully!');
    }
}
