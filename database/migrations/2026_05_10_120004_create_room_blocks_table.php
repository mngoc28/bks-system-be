<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Tạo bảng `room_blocks` cho phép Partner chặn lịch phòng (bảo trì /
     * owner_use / off_market) mà không tạo booking giả. Tham chiếu mục
     * 4.1.4 của `docs/designs/design_001.md` và bảng "room_blocks" trong
     * canonical schema `db_overview_etc_core_schema.md`.
     *
     * Lưu ý: Schema builder của Laravel 9 chưa có method `check()`. CHECK
     * constraints cho `end_date >= start_date` và `block_type IN (...)`
     * được thêm bằng raw SQL qua `DB::statement` để giữ ràng buộc ở DB
     * level (MySQL 8.0+ enforce CHECK).
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('room_blocks', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('room_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('block_type', 30)
                ->comment('maintenance | owner_use | off_market');
            $table->string('reason', 255);
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('room_id', 'fk_rb_room')
                ->references('id')->on('rooms')
                ->cascadeOnDelete();
            $table->foreign('created_by', 'fk_rb_creator')
                ->references('id')->on('users')
                ->nullOnDelete();
            $table->foreign('updated_by', 'fk_rb_updater')
                ->references('id')->on('users')
                ->nullOnDelete();

            $table->index(['room_id', 'start_date', 'end_date'], 'idx_rb_room_dates');
            $table->index('block_type', 'idx_rb_block_type');
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement(
                'ALTER TABLE room_blocks
                    ADD CONSTRAINT chk_rb_dates
                    CHECK (end_date >= start_date)'
            );
            DB::statement(
                "ALTER TABLE room_blocks
                    ADD CONSTRAINT chk_rb_block_type
                    CHECK (block_type IN ('maintenance','owner_use','off_market'))"
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('room_blocks');
    }
};
