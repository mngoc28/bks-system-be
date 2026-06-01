<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\PartnerSettlementPeriod;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable gửi email thông báo đối soát công nợ phát hành.
 */
class SettlementPeriodIssuedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @var string
     */
    protected string $partnerName;

    /**
     * @var \App\Models\PartnerSettlementPeriod
     */
    protected PartnerSettlementPeriod $period;

    /**
     * @var array
     */
    protected array $bankInfo;

    /**
     * Khởi tạo class.
     *
     * @param string $partnerName
     * @param \App\Models\PartnerSettlementPeriod $period
     * @param array $bankInfo
     */
    public function __construct(string $partnerName, PartnerSettlementPeriod $period, array $bankInfo)
    {
        $this->partnerName = $partnerName;
        $this->period = $period;
        $this->bankInfo = $bankInfo;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): self
    {
        $subject = sprintf(
            '[BKS Stay] Thông báo phát hành kỳ đối soát hoa hồng kỳ %s - %s',
            $this->period->period_start->format('d/m/Y'),
            $this->period->period_end->format('d/m/Y')
        );

        return $this->from(
            config('mail.from.address', env('MAIL_FROM_ADDRESS')),
            config('mail.from.name', env('MAIL_FROM_NAME'))
        )
            ->subject($subject)
            ->view('emails.settlement_issued')
            ->with([
                'partnerName' => $this->partnerName,
                'period'      => $this->period,
                'bankInfo'    => $this->bankInfo,
                'dueDate'     => $this->period->issue_date->copy()->addDays(config('billing.due_days', 5))->format('d/m/Y'),
                'transferSyntax' => sprintf('%s%d', $this->bankInfo['transfer_syntax_prefix'], $this->period->id),
            ]);
    }
}
