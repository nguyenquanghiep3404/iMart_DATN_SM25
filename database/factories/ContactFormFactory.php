<?php

namespace Database\Factories;
use App\Models\ContactForm;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFormFactory extends Factory
{
    protected $model = ContactForm::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'phone' => $this->faker->optional(0.7)->phoneNumber,
            'subject' => $this->faker->catchPhrase,
            'message' => $this->faker->paragraph(rand(2,5)),
            'status' => $this->faker->randomElement(['new', 'read', 'responded', 'closed']),
            'ip_address' => $this->faker->ipv4,
        ];
    }
}
