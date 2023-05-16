<?php

use App\Models\Core\Module;
use App\Models\Core\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

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
            'Reports' => [
                'sort_order' => 3,
                'icon' => 'fas fa-chart-user',
                'url' => 'reports',
                'show_menu' => 1,
                'sub_items' => [
                    'Daily Reports',
                    'Monthly Reports',
                    'Yearly Reports',
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

        Permission::whereIn('scope', ['members:reports', 'members:yearly-reports'])->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
