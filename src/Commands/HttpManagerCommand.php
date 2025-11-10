<?php

namespace NckRtl\HttpManager\Commands;

use Illuminate\Console\Command;

class HttpManagerCommand extends Command
{
    public $signature = 'http-manager';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
