<?php

namespace ATernovtsii\SearchBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ATSearchBundle extends Bundle
{
    public function boot(): void
    {
        if ($this->container->has('at_search.cache_compiler')) {
            $this->container->get('at_search.cache_compiler')?->loadClasses();
        }
    }
}
