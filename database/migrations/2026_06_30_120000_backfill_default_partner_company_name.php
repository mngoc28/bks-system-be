<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill company_name for demo/default partner when onboarding left it empty.
     */
    public function up(): void
    {
        $defaultPartnerUserId = DB::table('users')
            ->where('email', 'partner@gmail.com')
            ->value('id');

        if ($defaultPartnerUserId === null) {
            return;
        }

        DB::table('partner_info')
            ->where('user_id', $defaultPartnerUserId)
            ->where(function ($query): void {
                $query->whereNull('company_name')
                    ->orWhere('company_name', '');
            })
            ->update([
                'company_name' => 'Aman Resorts & Villas',
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data backfill — no rollback.
    }
};
