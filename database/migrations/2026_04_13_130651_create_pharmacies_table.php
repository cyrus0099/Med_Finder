<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pharmacies')) {
            Schema::create('pharmacies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('name');
                $table->string('city ');
                $table->string('address');
                $table->string('phone')->nullable();
                $table->string('status')->default('pending');
                $table->boolean('is_subscribed')->default(false);
                $table->string('subscription_plan')->nullable();
                $table->timestamp('subscribed_at')->nullable();
                $table->decimal('subscription_amount', 10, 2)->nullable();
                $table->timestamp('subscription_paid_at')->nullable();
                $table->timestamps();
            });

            return;
        }

        Schema::table('pharmacies', function (Blueprint $table) {
            if (! Schema::hasColumn('pharmacies', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('pharmacies', 'name')) {
                $table->string('name');
            }

            if (! Schema::hasColumn('pharmacies', 'city')) {
                $table->string('city');
            }

            if (! Schema::hasColumn('pharmacies', 'address')) {
                $table->string('address');
            }

            if (! Schema::hasColumn('pharmacies', 'phone')) {
                $table->string('phone')->nullable();
            }

            if (! Schema::hasColumn('pharmacies', 'status')) {
                $table->string('status')->default('pending');
            }

            if (! Schema::hasColumn('pharmacies', 'is_subscribed')) {
                $table->boolean('is_subscribed')->default(false);
            }

            if (! Schema::hasColumn('pharmacies', 'subscription_plan')) {
                $table->string('subscription_plan')->nullable();
            }

            if (! Schema::hasColumn('pharmacies', 'subscribed_at')) {
                $table->timestamp('subscribed_at')->nullable();
            }

            if (! Schema::hasColumn('pharmacies', 'subscription_amount')) {
                $table->decimal('subscription_amount', 10, 2)->nullable();
            }

            if (! Schema::hasColumn('pharmacies', 'subscription_paid_at')) {
                $table->timestamp('subscription_paid_at')->nullable();
            }

            if (! Schema::hasColumn('pharmacies', 'created_at') && ! Schema::hasColumn('pharmacies', 'updated_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        // This migration mirrors an existing live schema, so rollback is intentionally a no-op.
    }
};
