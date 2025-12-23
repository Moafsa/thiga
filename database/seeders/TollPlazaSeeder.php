<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TollPlaza;

class TollPlazaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Example toll plazas - these are sample data
        // In production, you would populate this with real toll plaza data
        
        $tollPlazas = [
            [
                'name' => 'Pedágio Anhanguera - SP-330',
                'highway' => 'SP-330',
                'city' => 'Campinas',
                'state' => 'SP',
                'latitude' => -22.9075,
                'longitude' => -47.0572,
                'price_car' => 5.50,
                'price_van' => 8.00,
                'price_truck_2_axles' => 12.00,
                'price_truck_3_axles' => 18.00,
                'price_truck_4_axles' => 25.00,
                'price_truck_5_axles' => 35.00,
                'price_bus' => 15.00,
            ],
            [
                'name' => 'Pedágio Bandeirantes - SP-348',
                'highway' => 'SP-348',
                'city' => 'Jundiaí',
                'state' => 'SP',
                'latitude' => -23.1864,
                'longitude' => -46.8842,
                'price_car' => 6.00,
                'price_van' => 9.00,
                'price_truck_2_axles' => 13.50,
                'price_truck_3_axles' => 20.00,
                'price_truck_4_axles' => 28.00,
                'price_truck_5_axles' => 38.00,
                'price_bus' => 16.00,
            ],
            [
                'name' => 'Pedágio Fernão Dias - BR-381',
                'highway' => 'BR-381',
                'city' => 'Mairiporã',
                'state' => 'SP',
                'latitude' => -23.3189,
                'longitude' => -46.5869,
                'price_car' => 7.50,
                'price_van' => 11.00,
                'price_truck_2_axles' => 15.00,
                'price_truck_3_axles' => 22.00,
                'price_truck_4_axles' => 30.00,
                'price_truck_5_axles' => 42.00,
                'price_bus' => 18.00,
            ],
            [
                'name' => 'Pedágio Régis Bittencourt - BR-116',
                'highway' => 'BR-116',
                'city' => 'São Paulo',
                'state' => 'SP',
                'latitude' => -23.5505,
                'longitude' => -46.6333,
                'price_car' => 5.00,
                'price_van' => 7.50,
                'price_truck_2_axles' => 11.00,
                'price_truck_3_axles' => 16.50,
                'price_truck_4_axles' => 23.00,
                'price_truck_5_axles' => 32.00,
                'price_bus' => 14.00,
            ],
            [
                'name' => 'Pedágio Dutra - BR-116',
                'highway' => 'BR-116',
                'city' => 'Guarulhos',
                'state' => 'SP',
                'latitude' => -23.4538,
                'longitude' => -46.5332,
                'price_car' => 6.50,
                'price_van' => 9.50,
                'price_truck_2_axles' => 14.00,
                'price_truck_3_axles' => 21.00,
                'price_truck_4_axles' => 29.00,
                'price_truck_5_axles' => 40.00,
                'price_bus' => 17.00,
            ],
        ];

        foreach ($tollPlazas as $tollPlaza) {
            TollPlaza::updateOrCreate(
                [
                    'name' => $tollPlaza['name'],
                    'highway' => $tollPlaza['highway'],
                ],
                $tollPlaza
            );
        }
    }
}


























