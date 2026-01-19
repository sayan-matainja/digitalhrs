<?php

namespace App\Helpers\ZktecoLib;

use App\Helpers\LaravelZkteco;

class WorkCode
{
    /**
     * @return bool|mixed
     */
    public static function get(LaravelZkteco $self)
    {
        $self->_section = __METHOD__;

        $command = Util::CMD_DEVICE;
        $command_string = 'WorkCode';

        return $self->_command($command, $command_string);
    }
}
