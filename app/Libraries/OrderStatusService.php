<?php

namespace App\Libraries;

/**
 * Single source of truth for the order lifecycle.
 *
 * Statuses (in workflow order):
 *   checked_out → paid → processing → shipped → delivered
 *   any → cancelled (from checked_out / paid only)
 */
class OrderStatusService
{
    /**
     * Human-readable label per status.
     *
     * @return array<string, array{label: string, tone: string, description: string}>
     */
    public static function labels(): array
    {
        return [
            'active'      => ['label' => 'Cart',       'tone' => 'muted',   'description' => 'Still in cart, not placed.'],
            'checked_out' => ['label' => 'Placed',     'tone' => 'info',    'description' => 'Order received, awaiting payment confirmation.'],
            'paid'        => ['label' => 'Paid',       'tone' => 'success', 'description' => 'Payment confirmed.'],
            'processing'  => ['label' => 'Processing', 'tone' => 'warning', 'description' => 'Being prepared for shipment.'],
            'shipped'     => ['label' => 'Shipped',    'tone' => 'warning', 'description' => 'On the way to you.'],
            'delivered'   => ['label' => 'Delivered',  'tone' => 'success', 'description' => 'Order completed.'],
            'cancelled'   => ['label' => 'Cancelled',  'tone' => 'danger',  'description' => 'Order was cancelled.'],
        ];
    }

    /**
     * Allowed forward transitions from a given status.
     *
     * @return string[]
     */
    public static function allowedTransitions(string $current): array
    {
        return match ($current) {
            'checked_out' => ['paid', 'cancelled'],
            'paid'        => ['processing', 'cancelled'],
            'processing'  => ['shipped'],
            'shipped'     => ['delivered'],
            default       => [],
        };
    }

    public static function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::allowedTransitions($from), true);
    }

    /**
     * Linear timeline used for the customer-facing progress stepper.
     * Each stage carries a reached/active/pending state.
     *
     * @return array<int, array{key: string, label: string, state: string}>
     */
    public static function timelineFor(string $current): array
    {
        $stages = ['checked_out', 'paid', 'processing', 'shipped', 'delivered'];
        $labels = self::labels();

        if ($current === 'cancelled') {
            return [[
                'key'   => 'cancelled',
                'label' => $labels['cancelled']['label'],
                'state' => 'cancelled',
            ]];
        }

        $currentIndex = array_search($current, $stages, true);
        $timeline     = [];
        foreach ($stages as $i => $stage) {
            if ($currentIndex === false) {
                $state = 'pending';
            } elseif ($i < $currentIndex) {
                $state = 'reached';
            } elseif ($i === $currentIndex) {
                $state = 'active';
            } else {
                $state = 'pending';
            }

            $timeline[] = [
                'key'   => $stage,
                'label' => $labels[$stage]['label'],
                'state' => $state,
            ];
        }
        return $timeline;
    }
}
