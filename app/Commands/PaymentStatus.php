<?php

namespace App\Commands;

use App\Libraries\DuitkuService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Duitku as DuitkuConfig;

/**
 * Diagnostics for the Duitku payment integration.
 *
 *   php spark payment:status         # show config state (no secrets leaked)
 *   php spark payment:status --ping  # also fire a tiny live createInvoice test
 *
 * Use this on the server after editing .env to confirm the keys are actually
 * loaded and the gateway accepts our signature.
 */
class PaymentStatus extends BaseCommand
{
    protected $group       = 'NexGear';
    protected $name        = 'payment:status';
    protected $description = 'Show Duitku payment config status and optionally ping the gateway.';
    protected $usage       = 'payment:status [--ping]';
    protected $options     = ['--ping' => 'Send a small live createInvoice test to Duitku.'];

    public function run(array $params)
    {
        $cfg = config(DuitkuConfig::class);

        CLI::write('Duitku configuration', 'yellow');
        CLI::write('  merchantCode : ' . ($cfg->merchantCode !== '' ? $cfg->merchantCode : '(empty)'),
            $cfg->merchantCode !== '' ? 'green' : 'red');
        // Never print the key — just whether it is present and its length.
        CLI::write('  apiKey       : ' . ($cfg->apiKey !== '' ? '(set, ' . strlen($cfg->apiKey) . ' chars)' : '(empty)'),
            $cfg->apiKey !== '' ? 'green' : 'red');
        CLI::write('  production   : ' . ($cfg->production ? 'true (LIVE)' : 'false (sandbox)'));
        CLI::write('  apiBase      : ' . $cfg->apiBase());
        CLI::write('  isEnabled    : ' . ($cfg->isEnabled() ? 'YES' : 'NO'),
            $cfg->isEnabled() ? 'green' : 'red');

        if (! $cfg->isEnabled()) {
            CLI::newLine();
            CLI::error('Payments are DISABLED — set duitku.merchantCode and duitku.apiKey in .env, then reload PHP-FPM.');
            return EXIT_ERROR;
        }

        if (CLI::getOption('ping') === null) {
            CLI::newLine();
            CLI::write('Add --ping to send a live test invoice to Duitku.', 'yellow');
            return EXIT_SUCCESS;
        }

        CLI::newLine();
        CLI::write('Pinging Duitku createInvoice (Rp 10.000 test)…', 'yellow');

        try {
            $invoice = (new DuitkuService($cfg))->createInvoice(
                [
                    'merchantOrderId' => 'PING-' . time(),
                    'paymentAmount'   => 10000,
                    'productDetails'  => 'NexGear connectivity test',
                ],
                ['name' => 'Ping Test', 'email' => 'ping@nexgear.my.id', 'phone' => '08123456789'],
                [['name' => 'Ping', 'price' => 10000, 'quantity' => 1]],
                [
                    'callbackUrl' => base_url('/payment/callback'),
                    'returnUrl'   => base_url('/payment/return'),
                ]
            );
            CLI::write('  [OK] Gateway accepted the request.', 'green');
            CLI::write('  reference  : ' . $invoice['reference']);
            CLI::write('  paymentUrl : ' . $invoice['paymentUrl']);
            CLI::newLine();
            CLI::write('Signature + keys are correct. Payments should work.', 'green');
            return EXIT_SUCCESS;
        } catch (\Throwable $e) {
            CLI::newLine();
            CLI::error('Gateway rejected the request: ' . $e->getMessage());
            CLI::write('Common causes:', 'yellow');
            CLI::write('  - "Wrong signature"  → apiKey is wrong or has stray spaces/newlines');
            CLI::write('  - "Merchant not found"→ merchantCode is wrong or wrong environment');
            CLI::write('  - production flag does not match the key environment (SB vs live)');
            return EXIT_ERROR;
        }
    }
}
