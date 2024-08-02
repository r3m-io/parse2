<?php
namespace Package\R3m\Io\Parse\Service;

use R3m\Io\App;

use R3m\Io\Module\Core;
use R3m\Io\Module\File;

use Exception;
class Cast
{
    public static function define(App $object, $input, $flags, $options){
        $is_collect = false;
        $define = '';
        foreach($input as $nr => $char){
            if(
                is_array($char) &&
                array_key_exists('value', $char) &&
                $char['value'] === '('
            ){
                $is_collect = true;
            }
            elseif(
                is_array($char) &&
                array_key_exists('value', $char) &&
                $char['value'] === ')'
            ){
                if(strlen($define) > 0){
                    ddd($define);
                }
                $is_collect = false;
            }
            elseif(
                $is_collect &&
                !is_array($char)
            ){
                if(
                    in_array(
                        $char,
                        [
                            ' ',
                            "\t",
                            "\n",
                            "\r",
                        ],
                        true
                    )
                ){
                    continue;
                }
                $define .= $char;
            }
        }
        return $input;
    }
}