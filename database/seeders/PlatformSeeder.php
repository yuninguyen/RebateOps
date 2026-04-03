<?php

namespace Database\Seeders;

use App\Models\Platform;
use Illuminate\Database\Seeder;
use App\Filament\Resources\Traits\HasPlatform;

class PlatformSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $platforms = [
            'Rakuten' => 'Rakuten',
            'RetailMeNot' => 'RetailMeNot',
            'JoinHoney' => 'Join Honey',
            'Price' => 'Price.com',
            'TopCashback' => 'TopCashback',
            'ActiveJunky' => 'Active Junky',
            'CouponCabin' => 'Coupon Cabin',
            'PayPal' => 'PayPal',
            'Amazon' => 'Amazon',
            'Netflix' => 'Netflix',
            'Facebook' => 'Facebook',
            'Instagram' => 'Instagram',
            'Twitter' => 'Twitter',
            'TikTok' => 'TikTok',
            'Discord' => 'Discord',
            'Pinterest' => 'Pinterest',
            'Reddit' => 'Reddit',
            'LinkedIn' => 'LinkedIn',
            'Spotify' => 'Spotify',
            'Telegram' => 'Telegram',
            'Tumblr' => 'Tumblr',
            'YouTube' => 'YouTube',
            'eBay' => 'eBay',
            'Etsy' => 'Etsy',
            'Swagbucks' => 'Swagbucks',
            'InboxDollars' => 'InboxDollars',
            'MyPoints' => 'MyPoints',
            'Drop' => 'Drop',
            'Dosh' => 'Dosh',
            'Ibotta' => 'Ibotta',
            'FetchRewards' => 'FetchRewards',
            'Checkout51' => 'Checkout51',
        ];

        foreach ($platforms as $slug => $name) {
            Platform::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'is_active' => true]
            );
        }
    }
}
