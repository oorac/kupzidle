<?php declare(strict_types=1);

namespace App\Jobs;

use DateTime;

final class DailyExportJob
{
    public function __construct(
    ) {}

    public function run(): void
    {
        $dateTime = new DateTime('-1 day');
    }
}
