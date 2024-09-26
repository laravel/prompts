<?php

arch("Doesn't use collections")
    ->expect('Laravel\Prompts')
    ->not->toUse(['collect']);
