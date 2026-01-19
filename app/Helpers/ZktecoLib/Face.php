<?php

namespace App\Helpers\ZktecoLib;

use App\Helpers\LaravelZkteco;

class Face
{
    /**
     * @return bool|mixed
     */
    public static function on(LaravelZkteco $self)
    {
        $self->_section = __METHOD__;

        $command = Util::CMD_DEVICE;
        $command_string = 'FaceFunOn';

        return $self->_command($command, $command_string);
    }
}
