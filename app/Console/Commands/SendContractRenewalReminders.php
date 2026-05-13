<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ContractService;
use Illuminate\Console\Command;
use Throwable;

/**
 * Daily scheduler for Partner Portal 360 Phase 5.
 *
 * Picks every `LEASE_AGREEMENT` contract whose underlying booking ends in the
 * next 30 days, that has not been terminated and has not been tagged yet, and
 * sets `renewal_reminder_at = now()`. Each tagging dispatches
 * `ContractRenewalReminderQueued`, which feeds the Partner Alert Center.
 *
 * Idempotent — re-running on the same day after success is a no-op since the
 * reminder slot is already filled.
 */
final class SendContractRenewalReminders extends Command
{
    /** @var string */
    protected $signature = 'partner:send-contract-renewal-reminders
                            {--days=30 : Window in days before end_date to start reminding}';

    /** @var string */
    protected $description = 'Tag long-term contracts approaching expiry and broadcast renewal reminders.';

    public function __construct(
        private readonly ContractService $contractService,
    ) {
        parent::__construct();
    }

    /**
     * @return int
     */
    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));

        try {
            $count = $this->contractService->processDueReminders($days);
            $this->info(sprintf('Tagged %d contracts as expiring within %d days.', $count, $days));

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Failed to process renewal reminders: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
