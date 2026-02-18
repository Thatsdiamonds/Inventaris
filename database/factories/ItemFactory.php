<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class ItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Item::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $acquisitionDate = $this->faker->dateTimeBetween('-5 years', 'now');
        $isServiceRequired = $this->faker->boolean(40); // 40% chance requiring service
        
        $lastServiceDate = null;
        if ($isServiceRequired) {
            // Last service date must be after acquisition date and before now
            $lastServiceDate = $this->faker->dateTimeBetween($acquisitionDate, 'now');
        }

        return [
            'name' => $this->faker->words(3, true), // "Laptop Dell Latitude"
            'condition' => $this->faker->randomElement(['baik', 'baik', 'baik', 'rusak', 'perbaikan']), // Weighted towards 'baik'
            'service_interval_days' => $isServiceRequired ? $this->faker->randomElement([30, 90, 180, 365]) : null,
            'service_required' => $isServiceRequired,
            'last_service_date' => $lastServiceDate,
            'acquisition_date' => $acquisitionDate,
            'is_active' => true,
            // category_id, location_id, uqcode will be set by the Seeder to ensure valid relations and unique codes
        ];
    }
}
