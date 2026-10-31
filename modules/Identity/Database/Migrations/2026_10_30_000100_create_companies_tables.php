<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('name_ar');
            $table->string('domain');
            $table->boolean('is_parent')->default(false);
            $table->timestamps();
        });

        Schema::create('company_user', function (Blueprint $table) {
            $table->foreignUlid('company_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->string('role');
            $table->timestamps();
            $table->primary(['company_id', 'user_id']);
        });

        // The four companies are reference data every environment needs.
        $now = now();
        DB::table('companies')->insert(array_map(fn (array $c) => $c + [
            'id' => strtolower((string) Str::ulid()),
            'is_parent' => $c['slug'] === 'group',
            'created_at' => $now,
            'updated_at' => $now,
        ], [
            ['slug' => 'group', 'name' => 'UniPal Group', 'name_ar' => 'مجموعة يونيبال', 'domain' => 'unipal.group'],
            ['slug' => 'advisors', 'name' => 'UniPal Advisors', 'name_ar' => 'يونيبال للاستشارات', 'domain' => 'advisors.unipal.group'],
            ['slug' => 'tax', 'name' => 'UniPal Tax', 'name_ar' => 'يونيبال للضرائب', 'domain' => 'tax.unipal.group'],
            ['slug' => 'solutions', 'name' => 'UniPal Solutions', 'name_ar' => 'يونيبال للحلول', 'domain' => 'solutions.unipal.group'],
        ]));
    }

    public function down(): void
    {
        Schema::dropIfExists('company_user');
        Schema::dropIfExists('companies');
    }
};
