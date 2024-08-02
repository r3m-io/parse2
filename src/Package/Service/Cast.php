<?php
namespace Package\R3m\Io\Parse\Service;

use R3m\Io\App;

use R3m\Io\Module\Core;
use R3m\Io\Module\File;

use Exception;
class Cast
{
    public static function define(App $object, $input, $flags, $options){
        ddd($input);
        return $input;
    }
}