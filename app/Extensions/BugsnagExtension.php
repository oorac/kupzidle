<?php declare(strict_types=1);

namespace App\Extensions;

use Nette\DI\CompilerExtension;
use Bugsnag\Client;
use Bugsnag\Handler;

class BugsnagExtension extends CompilerExtension
{
    public function beforeCompile(): void
    {
        $builder = $this->getContainerBuilder();
        $config = $this->getConfig();

        $bugsnag = Client::make($config['apiKey']);

        Handler::register($bugsnag);
    }
}
