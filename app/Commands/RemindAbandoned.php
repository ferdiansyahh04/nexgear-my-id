<?php

namespace App\Commands;

use App\Libraries\AbandonedCartService;
use App\Libraries\AuditLogService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Dispatch reminders for carts inactive longer than the threshold.
 *
 * Usage:
 *   php spark cart:remind-abandoned         (default: 1 hour)
 *   php spark cart:remind-abandoned 24      (look back 24 hours)
 *
 * Production: schedule via cron, e.g.
 *   0 * * * * cd /var/www/nexgear && php spark cart:remind-abandoned
 *
 * Email delivery is left as a hook — wire CodeIgniter's Email service
 * inside the loop below when ready.
 */
class RemindAbandoned extends BaseCommand
{
    protected $group       = 'NexGear';
    protected $name        = 'cart:remind-abandoned';
    protected $description = 'Notify users about carts left inactive past the threshold.';
    protected $usage       = 'cart:remind-abandoned [hours]';
    protected $arguments   = ['hours' => 'Inactivity threshold in hours (default: 1)'];

    public function run(array $params)
    {
        $hours = isset($params[0]) ? max(1, (int) $params[0]) : AbandonedCartService::REMIND_AFTER_HOURS;

        $service  = new AbandonedCartService();
        $audit    = new AuditLogService();
        $pending  = $service->pendingReminders($hours);
        $count    = 0;

        if ($pending === []) {
            CLI::write("No abandoned carts older than {$hours}h.", 'cyan');
            return;
        }

        CLI::write("Processing " . count($pending) . " abandoned snapshots…", 'yellow');

        foreach ($pending as $row) {
            $email = $service->markReminded((int) $row['id']);

            if ($email) {
                (new \App\Libraries\MailerService())->send(
                    $email,
                    'You left items in your bag',
                    'emails/abandoned_cart',
                    [
                        'items'    => json_decode((string) $row['items_json'], true) ?: [],
                        'total'    => (float) $row['total'],
                        'userName' => '',
                    ]
                );
            }

            $audit->log('cart.reminder_dispatched', [
                'actor_label' => 'system',
                'target_type' => 'abandoned_cart',
                'target_id'   => (int) $row['id'],
                'meta'        => [
                    'user_id'    => (int) $row['user_id'],
                    'email'      => $email,
                    'item_count' => (int) $row['item_count'],
                    'total'      => (float) $row['total'],
                    'idle_hours' => $hours,
                ],
            ]);

            CLI::write("  ✓ Reminded user_id={$row['user_id']} ({$email}) — {$row['item_count']} items / Rp " . number_format((float) $row['total'], 0, ',', '.'), 'green');
            $count++;
        }

        CLI::write("Done. Sent {$count} reminder(s).", 'green');
    }
}
