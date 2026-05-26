<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partner_info', function (Blueprint $table) {
            $table->enum('partner_type', ['hotel', 'guesthouse', 'apartment', 'homestay'])
                ->default('hotel')
                ->after('user_id');
            $table->string('tax_code', 50)->nullable()->after('company_name');
            $table->string('representative_name', 100)->nullable()->after('tax_code');
            
            // Legal documents (Secure private paths)
            $table->string('id_card_front', 255)->nullable()->after('image_3');
            $table->string('id_card_back', 255)->nullable()->after('id_card_front');
            $table->string('business_license', 255)->nullable()->after('id_card_back');
            $table->string('ownership_document', 255)->nullable()->after('business_license');
            
            // Banking information
            $table->string('bank_name', 150)->nullable()->after('website');
            $table->string('bank_account_number', 50)->nullable()->after('bank_name');
            $table->string('bank_account_holder', 150)->nullable()->after('bank_account_number');
            $table->string('bank_statement_image', 255)->nullable()->after('bank_account_holder');
            
            // Contract & Approval details
            $table->string('contract_pdf_path', 255)->nullable()->after('bank_statement_image');
            $table->text('rejection_reason')->nullable()->after('contract_pdf_path');
            
            $table->timestamp('approved_at')->nullable()->after('rejection_reason');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approved_at');
            
            // Foreign key to users for approved_by
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('partner_info', function (Blueprint $table) {
                $table->dropForeign(['approved_by']);
                $table->dropColumn([
                    'partner_type',
                    'tax_code',
                    'representative_name',
                    'id_card_front',
                    'id_card_back',
                    'business_license',
                    'ownership_document',
                    'bank_name',
                    'bank_account_number',
                    'bank_account_holder',
                    'bank_statement_image',
                    'contract_pdf_path',
                    'rejection_reason',
                    'approved_at',
                    'approved_by'
                ]);
            });
        }
    }
};
