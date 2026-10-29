<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('confirmada');
            $table->string('confirmation_code', 20)->unique();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });
        DB::statement("ALTER TABLE reservations ADD CONSTRAINT reservations_status_check CHECK (status IN ('confirmada', 'cancelada'))");

        Schema::create('reservation_seats', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('reservation_id');
            $table->foreignId('screening_id');
            $table->foreignId('seat_id');
            $table->decimal('unit_price', 10, 2);
            $table->timestamps();
            $table->unique(['screening_id', 'seat_id']);
            $table->foreign('reservation_id')->references('id')->on('reservations')->cascadeOnDelete();
            $table->foreign('screening_id')->references('id')->on('screenings')->restrictOnDelete();
            $table->foreign('seat_id')->references('id')->on('seats')->restrictOnDelete();
        });
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION validate_reservation_seat_screen()
            RETURNS trigger AS $$
            BEGIN
                IF NOT EXISTS (
                    SELECT 1
                    FROM screenings
                    JOIN seats ON seats.screen_id = screenings.screen_id
                    WHERE screenings.id = NEW.screening_id
                      AND seats.id = NEW.seat_id
                ) THEN
                    RAISE EXCEPTION 'Seat does not belong to the screening room'
                        USING ERRCODE = '23514';
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
            CREATE TRIGGER reservation_seats_screen_check
                BEFORE INSERT OR UPDATE OF screening_id, seat_id ON reservation_seats
                FOR EACH ROW EXECUTE FUNCTION validate_reservation_seat_screen();
            SQL);

        Schema::create('sessions', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        DB::unprepared('DROP TRIGGER IF EXISTS reservation_seats_screen_check ON reservation_seats');
        DB::unprepared('DROP FUNCTION IF EXISTS validate_reservation_seat_screen()');
        Schema::dropIfExists('reservation_seats');
        Schema::dropIfExists('reservations');
    }
};
