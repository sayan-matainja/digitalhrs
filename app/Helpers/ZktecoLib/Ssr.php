<?php

namespace App\Helpers\ZktecoLib;

use App\Helpers\LaravelZkteco;

class Ssr
{
    /**
     * @return bool|mixed
     */
    public static function get(LaravelZkteco $self)
    {
        $self->_section = __METHOD__;

        $command = Util::CMD_DEVICE;
        $command_string = '~SSR';

        return $self->_command($command, $command_string);
    }
}
