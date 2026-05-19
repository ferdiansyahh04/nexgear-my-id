<?php
$showTrace = defined('ENVIRONMENT') && ENVIRONMENT === 'development';

$extra = '';
if ($showTrace && isset($exception)) {
    $where  = esc($exception->getFile()) . ':' . esc($exception->getLine());
    $traceHtml = esc($exception->getTraceAsString());
    $extra = '
        <div class="err-trace">
            <div class="err-trace-label">Where</div>
            <pre>' . $where . '</pre>
            <div class="err-trace-label">Stack Trace</div>
            <pre>' . $traceHtml . '</pre>
        </div>';
}

echo view('errors/html/_error_shell', [
    'code'    => '500',
    'title'   => 'An Error Occurred',
    'message' => $message ?? 'Something went wrong on our end. Our team has been notified.',
    'extra'   => $extra,
]);
?>
