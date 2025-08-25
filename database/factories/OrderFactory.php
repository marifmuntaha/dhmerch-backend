<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'whatsappId' => fake()->phoneNumber ."@c.us",
            'code' => fake()->randomNumber(9),
            'name' => fake()->name,
            'address' => fake()->address,
            'phone' => fake()->phoneNumber,
            'productId' => 'DM-210',
            'type' => fake()->randomElement(['Anak', 'Dewasa']),
            'size' => fake()->randomElement(['S', 'M', 'L', 'XL', 'XXL']),
            'arm' => fake()->randomElement(['Pendek', 'Panjang']),
            'price' => fake()->randomElement([75000, 80000, 85000, 95000]),
            'status' => fake()->randomElement(['1', '2', '3']),
            'payment' => fake()->randomElement(['1', '2']),
            'reference' => fake()->randomNumber(9),
            'payCode' => fake()->randomNumber(9),
            'created_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'updated_at' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }
}
