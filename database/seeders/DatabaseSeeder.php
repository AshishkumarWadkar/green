<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    // Seed permissions first
    $this->call([
      PermissionSeeder::class,
      RoleSeeder::class,
      MasterDataSeeder::class,
      UserSeeder::class,
      EnquirySeeder::class,
    ]);
  }
}
