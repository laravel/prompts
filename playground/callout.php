<?php

use Laravel\Prompts\Elements\Element;

use function Laravel\Prompts\callout;

require __DIR__ . '/../vendor/autoload.php';

callout(
    'Environment Configured',
    'Your application is running in production mode with 4 workers. Logs are being sent to stderr.',
);

callout(
    'Deprecation Notice',
    'The `--prefer-stable` flag will be removed in v4.0. Use `--stability=stable` instead.',
    'warning'
);

callout(
    'Database Connection Failed',
    'Could not connect to MySQL on 127.0.0.1:3306. Check that the service is running and your DB_PASSWORD is correct in .env.',
    'error',
);

callout(
    'Server Health Check',
    [
        'Multiple services are reporting degraded performance.',
        Element::heading('Affected Services'),
        Element::bulletedList([
            'Redis cache hit rate dropped to 42%',
            'Queue worker memory usage exceeding 256MB',
            'Scheduled task runner missed 3 jobs in the last hour',
        ]),
        Element::heading('Recommended Actions'),
        Element::numberedList([
            'Flush the Redis cache with `php artisan cache:clear`',
            'Restart queue workers with `php artisan queue:restart`',
            'Review the schedule output with `php artisan schedule:list`',
        ]),
    ],
    'warning',
);

callout(
    'Database Connection Failed',
    [
        'Could not connect to the database server.',
        Element::keyValueList([
            'Host' => '127.0.0.1',
            'Port' => '3306',
            'Database' => 'forge',
            'Status' => 'Connection refused',
        ]),
    ],
    'error',
    info: 'SQLSTATE[HY000] [2002]',
);

callout(
    'Deployment Summary',
    [
        'Your application was deployed to production at 2024-03-15 14:32 UTC.',
        Element::heading('What Changed'),
        Element::bulletedList([
            'Migrated 3 pending database migrations',
            'Cleared and rebuilt route cache',
            'Restarted 4 queue workers',
            'Invalidated CDN cache for /assets/*',
        ]),
        Element::heading('Next Steps'),
        Element::numberedList([
            'Verify the health check endpoint at /up',
            'Monitor error rates in Sentry for the next 15 minutes',
            'Confirm background jobs are processing in Horizon',
        ]),
    ],
);

callout(
    'Deployment Summary',
    'Your application was deployed to production at 2024-03-15 14:32 UTC.',
    info: 'deploy-id: d4f8a2c',
);

callout(
    'SSL Certificate Expiring',
    [
        'The TLS certificate for api.example.com expires in 7 days.',
        Element::heading('Affected Domains'),
        Element::bulletedList([
            'api.example.com',
            'staging-api.example.com',
            'webhooks.example.com',
        ]),
        Element::heading('To Renew'),
        Element::numberedList([
            'Run `certbot renew --nginx` on the load balancer',
            'Verify the new certificate with `openssl s_client -connect api.example.com:443`',
            'Restart Nginx to pick up the renewed certificate',
        ]),
    ],
    'warning'
);

callout(
    'Migration Failed',
    [
        'Rolling back 2024_03_15_create_invoices_table. The `invoices` table already exists in the target database.',
        Element::heading('Attempted Changes'),
        Element::numberedList([
            'Create `invoices` table with 12 columns',
            'Add foreign key constraint to `users.id`',
            'Create index on `invoices.status` and `invoices.due_date`',
            'Add `currency` column with default `USD`',
            'Create `invoice_items` table with 8 columns',
            'Add foreign key from `invoice_items` to `invoices.id`',
            'Create `invoice_payments` table with 6 columns',
            'Add composite index on `invoice_payments.invoice_id` and `invoice_payments.paid_at`',
            'Seed default tax rate entries into `tax_rates` table',
            'Create `invoice_discounts` table with 5 columns',
            'Add trigger to update `invoices.total` on item changes',
            'Insert default payment terms into `settings` table',
        ]),
        Element::heading('Possible Fixes'),
        Element::bulletedList([
            'Drop the existing table manually if it contains no data',
            'Run `php artisan migrate:fresh` in development environments',
            'Add an `if not exists` check to the migration',
        ]),
    ],
    'error',
);

callout(
    'Server Health Check',
    [
        'Multiple services are reporting degraded performance.',
        Element::heading('Affected Services'),
        Element::bulletedList([
            'Redis cache hit rate dropped to 42%',
            'Queue worker memory usage exceeding 256MB',
            'Scheduled task runner missed 3 jobs in the last hour',
        ], spaced: true),
        Element::heading('Recommended Actions'),
        Element::numberedList([
            'Flush the Redis cache with `php artisan cache:clear`',
            'Restart queue workers with `php artisan queue:restart`',
            'Review the schedule output with `php artisan schedule:list`',
        ], spaced: true),
    ],
    'warning',
);
