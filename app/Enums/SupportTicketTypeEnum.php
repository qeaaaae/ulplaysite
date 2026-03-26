<?php

declare(strict_types=1);

namespace App\Enums;

enum SupportTicketTypeEnum: string
{
    case TECHNICAL_ISSUE = 'technical_issue';
    case ORDER_ISSUE = 'order_issue';
    case DELIVERY = 'delivery';
    case SERVICE_REPAIR = 'service_repair';
    case RETURN_EXCHANGE = 'return_exchange';
    case SUGGESTION = 'suggestion';
    case SERVICE_INQUIRY = 'service_inquiry';

    public function label(): string
    {
        return match ($this) {
            self::TECHNICAL_ISSUE => 'Техническая проблема',
            self::ORDER_ISSUE => 'Проблема с заказом',
            self::DELIVERY => 'Доставка',
            self::SERVICE_REPAIR => 'Услуги/ремонт',
            self::RETURN_EXCHANGE => 'Возврат/обмен',
            self::SUGGESTION => 'Предложение/пожелание',
            self::SERVICE_INQUIRY => 'Вопрос по услуге',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::TECHNICAL_ISSUE => 'bg-rose-100 text-rose-700',
            self::ORDER_ISSUE => 'bg-amber-100 text-amber-700',
            self::DELIVERY => 'bg-cyan-100 text-cyan-700',
            self::SERVICE_REPAIR => 'bg-violet-100 text-violet-700',
            self::RETURN_EXCHANGE => 'bg-orange-100 text-orange-700',
            self::SUGGESTION => 'bg-emerald-100 text-emerald-700',
            self::SERVICE_INQUIRY => 'bg-indigo-100 text-indigo-800',
        };
    }

    public function selectColor(): string
    {
        return match ($this) {
            self::TECHNICAL_ISSUE => '#be123c',
            self::ORDER_ISSUE => '#b45309',
            self::DELIVERY => '#0e7490',
            self::SERVICE_REPAIR => '#7c3aed',
            self::RETURN_EXCHANGE => '#c2410c',
            self::SUGGESTION => '#047857',
            self::SERVICE_INQUIRY => '#3730a3',
        };
    }
}

