<?php

namespace Xenokore\Cache;

return [

    PredisClientFactory::class => function ($container) {
        return new PredisClientFactory();
    },

    Cache::class => function ($container) {
        return new Cache($container->get(PredisClientFactory::class));
    },

    EventCache::class => function ($container) {
        return new EventCache($container->get(PredisClientFactory::class));
    },
];
