<?php

namespace Xenokore\Cache;

return [

    PredisClientFactory::class => function ($container) {
        // $config = $container->get('config');
        // TODO: test loading by array index:
        // other possibility: (($containet->get('config) ?? [])['cache'] ?? [])
        // return new PredisClientFactory($container->get('config')['cache'] ?? []);

        // hopefully the container understands to use the config class as an array
        return new PredisClientFactory($container->get('config')['cache'] ?? null);
    },

    Cache::class => function ($container) {
        return new Cache($container->get(PredisClientFactory::class));
    },

    EventCache::class => function ($container) {
        return new EventCache($container->get(PredisClientFactory::class));
    }
];
