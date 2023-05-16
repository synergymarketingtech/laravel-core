<?php

use App\Models\Core\Module;
use Illuminate\Support\Str;
use App\Models\Core\Permission;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permissions = [
            'Registrations' => [
                'sort_order' => 2,
                'icon' => 'fas fa-calendar-circle-user',
                'url' => 'registrations',
                'show_menu' => 0,
                'sub_items' => [
                    'View',
                    'Edit',
                    'List',
                ],
            ],
        ];

        foreach ($permissions as $name => $item) {
            $module = Module::updateOrCreate([
                'name' => $name,
            ], [
                'icon' => $item['icon'],
                'url' => $item['url'],
                'show_menu' => $item['show_menu'],
                'sort_order' => $item['sort_order'],
            ]);

            foreach ($item['sub_items'] as $i => $sub_item) {
                Permission::updateOrCreate([
                    'module_id' => $module['id'],
                    'action' => $sub_item,
                    'scope' => Str::slug($module['name']) . ':' . Str::slug($sub_item),
                ]);
            }
        }
    }
};
