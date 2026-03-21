<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\SupportTicketTypeEnum;
use PHPUnit\Framework\TestCase;

class SupportTicketTypeEnumTest extends TestCase
{
    public function test_all_cases_have_label(): void
    {
        foreach (SupportTicketTypeEnum::cases() as $case) {
            $label = $case->label();
            $this->assertIsString($label);
            $this->assertNotEmpty($label);
        }
    }

    public function test_all_cases_have_badge_class(): void
    {
        foreach (SupportTicketTypeEnum::cases() as $case) {
            $badgeClass = $case->badgeClass();
            $this->assertIsString($badgeClass);
            $this->assertNotEmpty($badgeClass);
        }
    }

    public function test_labels_are_correct(): void
    {
        $this->assertSame('Техническая проблема', SupportTicketTypeEnum::TECHNICAL_ISSUE->label());
        $this->assertSame('Проблема с заказом', SupportTicketTypeEnum::ORDER_ISSUE->label());
        $this->assertSame('Доставка', SupportTicketTypeEnum::DELIVERY->label());
    }
}
