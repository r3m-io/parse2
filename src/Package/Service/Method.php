<?php
namespace Package\R3m\Io\Parse\Service;

use R3m\Io\App;

use R3m\Io\Module\Core;
use R3m\Io\Module\File;

use Exception;
class Method
{
    public static function define(App $object, $input, $flags, $options){
        foreach($input as $nr => $char){
            if(
                is_array($char) &&
                array_key_exists('value', $char) &&
                $char['value'] === '('
            ){
                $is_method = $nr;
                $name = '';
                for($i = $nr - 1; $i >= 0; $i--){
                    if($input[$i] !== null){
                        if(is_array($input[$i])){
                            if(
                                array_key_exists('value', $input[$i]) &&
                                $input[$i]['value'] === '.'
                            ){
                                $name .= $input[$i];
                            } else {
                                ddd($name);
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
                                ddd($name);
                            } else {
                                $name .= $input[$i];
                            }

                        }

                    }
                }
                ddd($name);
            }

        }
    }
}