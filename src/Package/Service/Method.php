<?php
namespace Package\R3m\Io\Parse\Service;

use R3m\Io\App;

use R3m\Io\Module\Core;
use R3m\Io\Module\File;

use Exception;
class Method
{
    public static function define(App $object, $input, $flags, $options){
        d($input);
        foreach($input as $nr => $char){
            if(
                is_array($char) &&
                array_key_exists('value', $char) &&
                $char['value'] === '('
            ){
                $is_method = $nr;
                $name = '';
                $has_name = false;
                for($i = $nr - 1; $i >= 0; $i--){
                    if($input[$i] !== null){
                        if(is_array($input[$i])){
                            if(
                                array_key_exists('value', $input[$i]) &&
                                in_array(
                                    $input[$i]['value'],
                                    [
                                        '.',
                                        "_",
                                    ]
                                )
                            ){
                                $name .= $input[$i]['value'];
                            } else {
                                $has_name = true;
                                break;
                            }
                        } else {
                            if(
                                in_array(
                                    $input[$i],
                                    [
                                        ' ',
                                        "\n",
                                        "\r",
                                        "\t"
                                    ]
                                ) === true
                            ){
                                $has_name = true;
                                break;
                            } else {
                                $name .= $input[$i];
                            }
                        }
                    }
                }
                if($has_name){
                    $name = strrev($name);
                    ddd($name);
                }
            }
        }
    }
}