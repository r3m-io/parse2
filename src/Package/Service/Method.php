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
        $has_name = false;
        $name = false;
        $is_method = false;
        $set_depth = 0;
        foreach($input as $nr => $char){
            if(
                is_array($char) &&
                array_key_exists('value', $char) &&
                $char['value'] === '(' &&
                $is_method === false
            ){
                $set_depth++;
                $name = '';
                $is_method = $nr;
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
                                break;
                            }
                        } else {
                            if(
                                in_array(
                                    $input[$i],
                                    [
                                        null,
                                        ' ',
                                        "\n",
                                        "\r",
                                        "\t"
                                    ]
                                ) === true
                            ){
                                break;
                            } else {
                                $name .= $input[$i];
                            }
                        }
                    }
                }
                if($name && $has_name === false){
                    $name = strrev($name);
                    $has_name = true;
                }
            }
            if(
                $is_method !== false &&
                $name &&
                $has_name === true
            ){
                d($char);
                if(
                    is_array($char) &&
                    array_key_exists('value', $char) &&
                    $char['value'] === '('
                ) {
                    $set_depth++;
                }
                elseif(
                    is_array($char) &&
                    array_key_exists('value', $char) &&
                    $char['value'] === ')'
                ){
                    $set_depth--;
                    if($set_depth === 0){
                        $is_method = false;
                        ddd($name);
                    }
                }
            }
        }
    }
}