<?php

use Coderstm\Core\Models\User;
use Illuminate\Support\Str;
use Coderstm\Core\Models\Address;
use Illuminate\Support\Facades\Hash;
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
        $user = User::updateOrCreate([
            'email' => 'hello@coderstm.com',
        ], [
            'title' => 'Mr',
            'first_name' => 'Coders',
            'last_name' => 'Tm',
            'email_verified_at' => now(),
            'is_free_forever' => true,
            'password' => Hash::make('Gis0ra$$;'),
            'remember_token' => Str::random(10),
            'status' => 'Active',
        ]);

        $user->updateOrCreateAddress(Address::factory()->make()->toArray());
    }
};
