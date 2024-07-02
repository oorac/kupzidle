<?php declare(strict_types=1);

namespace App\Providers;

use Jenssegers\Agent\Agent;

class AgentProvider
{
    /**
     * @var Agent|null
     */
    private static ?Agent $agent = null;

    /**
     * @return Agent
     */
    public static function getAgent(): Agent
    {
        if (! static::$agent) {
            static::$agent = new Agent();
        }

        return static::$agent;
    }
}
