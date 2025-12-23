<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Add indexes for booking conflict checking
            $table->index(['apartment_id', 'status']);
            $table->index('start_date');
            $table->index('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Drop non-FK indexes only
            $table->dropIndex(['start_date']); // bookings_start_date_index
            $table->dropIndex(['end_date']);   // bookings_end_date_index
            
            // NOTE: We don't drop the ['apartment_id', 'status'] index
            // because it's automatically created by the foreign key constraint
            // and will be removed when the table is dropped
        });
    }
};
