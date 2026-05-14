<?php

namespace App\Support\Database;

trait Connections
{
    public function getConnections()
    {
        $connections = config('database.connections');

        $filtered = array_values(array_diff(
            array_keys($connections),
            ['sqlite', 'mysql', 'pgsql', 'sqlsrv', 'bussolastaging', 'mariadb', 'audit']
        ));

        return $filtered ?: [config('database.default')];
    }
}
