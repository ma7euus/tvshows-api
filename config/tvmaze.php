<?php

return [
    'queue' => [
        'max_attempts' => (int) env('TVMAZE_QUEUE_MAX_ATTEMPTS', 6),
        'backoff_seconds' => array_values(array_filter(array_map(
            static fn (string $value): int => max(0, (int) trim($value)),
            explode(',', (string) env('TVMAZE_QUEUE_BACKOFF_SECONDS', '5,15,30,60,120')),
        ), static fn (int $value): bool => $value >= 0)),
        'retry_until_minutes' => (int) env('TVMAZE_QUEUE_RETRY_UNTIL_MINUTES', 30),
    ],
];
