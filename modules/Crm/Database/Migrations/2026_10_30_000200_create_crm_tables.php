<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Columns follow the BD pipeline sheet. Derived columns (weighted value, days in stage,
        // days since last contact) are computed, not stored.
        Schema::create('leads', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('company_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('number');
            $table->foreignUlid('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source');
            $table->string('stage');
            $table->string('organization_name')->nullable();
            $table->string('contact_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_alt')->nullable();
            $table->string('industry')->nullable();
            $table->json('services')->nullable();
            $table->string('client_type')->nullable();
            $table->decimal('proposal_value', 14, 2)->nullable();
            $table->unsignedTinyInteger('probability');
            $table->date('expected_close_on')->nullable();
            $table->timestamp('last_contact_at')->nullable();
            $table->string('next_action')->nullable();
            $table->date('next_follow_up_on')->nullable();
            $table->string('lost_reason')->nullable();
            $table->text('message')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('stage_changed_at');
            $table->timestamp('won_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'number']);
            $table->index(['company_id', 'stage']);
            $table->index(['company_id', 'next_follow_up_on']);
        });

        Schema::create('lead_activities', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->string('outcome')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->date('next_follow_up_on')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_activities');
        Schema::dropIfExists('leads');
    }
};
