<?php

namespace App\Commands;

use App\Libraries\StockAlertService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Sweep all in-stock products with pending stock alerts.
 *
 * Usage: php spark stock:dispatch-alerts
 *
 * Production: schedule via cron alongside the abandoned-cart command, e.g.
 * every 15 minutes:
 *
 *   *\/15 * * * * cd /var/www/nexgear && php spark stock:dispatch-alerts
 */
class DispatchStockAlerts extends BaseCommand
{
    protected $group       = 'NexGear';
    protected $name        = 'stock:dispatch-alerts';
    protected $description = 'Notify subscribers of products that are back in stock.';

    public function run(array $params)
    {
        $sent = (new StockAlertService())->sweepAll();
        if ($sent === 0) {
            CLI::write('No pending stock alerts to dispatch.', 'cyan');
        } else {
            CLI::write("Dispatched {$sent} stock alert(s).", 'green');
        }
    }
}
