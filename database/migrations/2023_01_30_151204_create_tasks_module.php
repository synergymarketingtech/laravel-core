<?php

use Coderstm\Core\Models\Core\Module;
use Coderstm\Core\Models\Core\Permission;
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
            'Tasks' => [
                'sort_order' => 16,
                'show_menu' => 1,
                'icon' => 'fas fa-list-check',
                'url' => 'tasks',
                'sub_items' => [
                    'View',
                    'Edit',
                    'List',
                    'New',
                    'Delete',
                ],
            ],
        ];

        foreach ($permissions as $name => $item) {
            $module = Module::updateOrCreate([
                'name' => $name,
            ], $item);

            foreach ($item['sub_items'] as $i => $sub_item) {
                Permission::updateOrCreate([
                    'module_id' => $module['id'],
                    'action' => $sub_item,
                    'scope' => Str::slug($module['name']) . ':' . Str::slug($sub_item),
                ]);
            }
        }
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
