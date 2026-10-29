<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 20)->unique();
            $table->timestamps();
        });
        DB::table('roles')->insert([
            ['name' => 'administrador', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'empleado', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'cliente', 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('role_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone', 30)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('cinemas', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('address');
            $table->string('city');
            $table->string('phone', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('customer_profiles', function (Blueprint $table): void {
            $table->foreignId('user_id')->primary()->constrained()->cascadeOnDelete();
            $table->date('birth_date')->nullable();
            $table->string('student_card', 40)->nullable()->unique();
            $table->boolean('is_retired')->default(false);
            $table->timestamps();
        });

        Schema::create('employee_profiles', function (Blueprint $table): void {
            $table->foreignId('user_id')->primary()->constrained()->cascadeOnDelete();
            $table->foreignId('cinema_id')->nullable()->constrained()->nullOnDelete();
            $table->string('position', 40)->nullable();
            $table->date('hire_date')->nullable();
            $table->timestamps();
        });

        Schema::create('admin_profiles', function (Blueprint $table): void {
            $table->foreignId('user_id')->primary()->constrained()->cascadeOnDelete();
            $table->foreignId('cinema_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 30);
            $table->string('affected_table', 40)->nullable();
            $table->string('route_name', 100)->nullable();
            $table->timestampTz('occurred_at');
            $table->index(['user_id', 'occurred_at']);
        });

        Schema::create('screens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cinema_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('capacity');
            $table->timestamps();
            $table->unique(['cinema_id', 'name']);
        });

        Schema::create('seats', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('screen_id')->constrained()->cascadeOnDelete();
            $table->string('row_label', 5);
            $table->unsignedSmallInteger('number');
            $table->string('seat_type', 20)->default('general');
            $table->timestamps();
            $table->unique(['screen_id', 'row_label', 'number']);
        });

        Schema::create('classifications', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 12)->unique();
            $table->string('description');
            $table->unsignedTinyInteger('minimum_age')->default(0);
            $table->timestamps();
        });

        Schema::create('genres', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('directors', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('actors', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('movies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('classification_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->text('synopsis');
            $table->unsignedSmallInteger('duration_minutes');
            $table->date('release_date')->nullable();
            $table->string('poster_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'title']);
        });

        Schema::create('genre_movie', function (Blueprint $table): void {
            $table->foreignId('genre_id')->constrained()->cascadeOnDelete();
            $table->foreignId('movie_id')->constrained()->cascadeOnDelete();
            $table->primary(['genre_id', 'movie_id']);
        });

        Schema::create('director_movie', function (Blueprint $table): void {
            $table->foreignId('director_id')->constrained()->cascadeOnDelete();
            $table->foreignId('movie_id')->constrained()->cascadeOnDelete();
            $table->primary(['director_id', 'movie_id']);
        });

        Schema::create('actor_movie', function (Blueprint $table): void {
            $table->foreignId('actor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('movie_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('billing_order')->default(0);
            $table->primary(['actor_id', 'movie_id']);
        });

        Schema::create('tariffs', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('price_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cinema_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tariff_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->decimal('amount', 10, 2);
            $table->timestamps();
            $table->unique(['cinema_id', 'tariff_id', 'day_of_week']);
        });
        DB::statement('ALTER TABLE price_rules ADD CONSTRAINT price_rules_day_check CHECK (day_of_week BETWEEN 0 AND 6)');

        Schema::create('screenings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('movie_id')->constrained()->restrictOnDelete();
            $table->foreignId('screen_id')->constrained()->restrictOnDelete();
            $table->foreignId('tariff_id')->constrained()->restrictOnDelete();
            $table->timestampTz('starts_at');
            $table->string('format', 12)->default('2D');
            $table->string('language', 30)->default('Español');
            $table->timestamps();
            $table->unique(['screen_id', 'starts_at']);
            $table->index(['starts_at', 'movie_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screenings');
        Schema::dropIfExists('price_rules');
        Schema::dropIfExists('tariffs');
        Schema::dropIfExists('actor_movie');
        Schema::dropIfExists('director_movie');
        Schema::dropIfExists('genre_movie');
        Schema::dropIfExists('movies');
        Schema::dropIfExists('actors');
        Schema::dropIfExists('directors');
        Schema::dropIfExists('genres');
        Schema::dropIfExists('classifications');
        Schema::dropIfExists('seats');
        Schema::dropIfExists('screens');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('admin_profiles');
        Schema::dropIfExists('employee_profiles');
        Schema::dropIfExists('customer_profiles');
        Schema::dropIfExists('cinemas');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
    }
};
