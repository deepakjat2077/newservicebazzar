<?php
/**
 * Standalone Gupshup callback catcher — deliberately NOT part of Laravel.
 *
 * Purpose: settle whether Gupshup's POSTs are reaching this server at all.
 * Laravel, its routing, middleware, database and config are all bypassed, so
 * if a request lands here it landed on the server, full stop.
 *
 * Point a temporary Gupshup webhook at:
 *     https://servicebazzar.com/gupshup-test.php
 *
 * Then read what arrived at:
 *     https://servicebazzar.com/gupshup-test.php?show=1
 *
 * Delete this file once the webhook problem is solved.
 */

$logFile = __DIR__ . '/gupshup-test-log.txt';

// Viewing mode: show what has been captured so far.
if (isset($_GET['show'])) {
    header('Content-Type: text/plain; charset=utf-8');

    if (!is_file($logFile)) {
        echo "No requests captured yet.\n";
        echo "Nothing has POSTed to https://servicebazzar.com/gupshup-test.php\n";
        exit;
    }

    echo file_get_contents($logFile);
    exit;
}

// Capture mode: record everything about this request.
$entry = [
    'time'         => date('Y-m-d H:i:s'),
    'method'       => $_SERVER['REQUEST_METHOD'] ?? '?',
    'ip'           => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? '?'),
    'user_agent'   => $_SERVER['HTTP_USER_AGENT'] ?? '',
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? '',
    'query'        => $_SERVER['QUERY_STRING'] ?? '',
    'body'         => file_get_contents('php://input'),
];

$line = str_repeat('=', 70) . "\n"
    . $entry['time'] . '  ' . $entry['method'] . '  from ' . $entry['ip'] . "\n"
    . 'User-Agent : ' . $entry['user_agent'] . "\n"
    . 'Content-Type: ' . $entry['content_type'] . "\n"
    . 'Query      : ' . $entry['query'] . "\n"
    . "Body:\n" . $entry['body'] . "\n\n";

// Keep the file from growing without bound if something loops.
if (is_file($logFile) && filesize($logFile) > 512 * 1024) {
    @unlink($logFile);
}

@file_put_contents($logFile, $line, FILE_APPEND);

// Exactly what Gupshup requires: 200 with an empty body.
http_response_code(200);
