<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Coupon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        $adminRole    = Role::firstOrCreate(['name' => 'admin']);
        $customerRole = Role::firstOrCreate(['name' => 'customer']);

        // Admin
        $admin = User::firstOrCreate(['email' => 'admin@streetfit.com.br'], [
            'name'     => 'Admin StreetFit',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        // Categorias
        $categorias = [
            ['name' => 'Regatas Japonesas', 'slug' => 'regatas-japonesas'],
            ['name' => 'Camisetas',          'slug' => 'camisetas'],
            ['name' => 'Roupas Fitness',     'slug' => 'roupas-fitness'],
            ['name' => 'Acessórios',         'slug' => 'acessorios'],
        ];
        foreach ($categorias as $cat) {
            Category::firstOrCreate(['slug' => $cat['slug']], $cat + ['is_active' => true]);
        }

        // Produto de exemplo
        $cat = Category::where('slug', 'regatas-japonesas')->first();
        $product = Product::firstOrCreate(['slug' => 'regata-japonesa-dragon'], [
            'category_id'  => $cat->id,
            'name'         => 'Regata Japonesa Dragon',
            'description'  => '<p>Regata estilo japonês com estampa exclusiva de dragão.</p>',
            'price'        => 89.90,
            'compare_price'=> 119.90,
            'weight'       => 0.3,
            'width'        => 30,
            'height'       => 5,
            'length'       => 40,
            'is_active'    => true,
            'is_featured'  => true,
        ]);

        foreach (['P', 'M', 'G', 'GG'] as $size) {
            ProductVariant::firstOrCreate(
                ['product_id' => $product->id, 'size' => $size],
                ['stock' => 10, 'color' => 'Preto']
            );
        }

        // Cupom
        Coupon::firstOrCreate(['code' => 'BEMVINDO10'], [
            'type'       => 'percent',
            'value'      => 10,
            'is_active'  => true,
            'expires_at' => now()->addYear(),
        ]);
    }
}
