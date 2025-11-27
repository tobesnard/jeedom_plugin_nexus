<?php

require_once __DIR__ . '/../vendor/autoload.php';

function hello_from_sync_inc()
{
    return Nexus\Syncbox::test();
}
