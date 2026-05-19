<?php
/**
 * Variables:
 *   $confirmUrl (string)
 */
$body = '<p>Confirm your subscription so we can send you drops, restocks, and rare finds.</p>';

echo view('emails/_layout', [
    'title'    => 'Confirm your NexGear subscription',
    'heading'  => 'One Last Click',
    'intro'    => 'You are almost subscribed. Tap the button below to confirm.',
    'body'     => $body,
    'cta'      => ['label' => 'Confirm Subscription', 'url' => $confirmUrl],
    'footnote' => 'If you did not request this, you can safely ignore this email.',
]);
