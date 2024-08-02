<?php
namespace Package\R3m\Io\Parse\Service;

use R3m\Io\App;

use R3m\Io\Module\Core;
use R3m\Io\Module\File;

use Exception;
class Variable
{
    public static function define(App $object, $input, $flags, $options){
        $count = count($input);
        $is_variable = false;
        foreach($input as $nr => $char){
            $previous = $input[$nr - 1] ?? null;
            if(is_array($char) && array_key_exists('value', $char)){
                if($char['value'] === '$'){
                    $is_variable = $nr;
                    $name = '';
                    for($i = $nr + 1; $i < $count; $i++){
                        if(
                            is_array($input[$i]) &&
                            array_key_exists('value', $input[$i])
                        ){
                            if(
                                in_array(
                                    $input[$i]['value'],
                                    [
                                        '_',
                                        '.'
                                    ]
                                )
                            ){
                                $name .= $input[$i]['value'];
                            } else {
                                break;
                            }
                        } else {
                            $name .= $char;
                        }
                    }
                    if($name){
                        $input[$is_variable] = [
                            'type' => 'variable',
                            'value' => $name
                        ];
                        for($i = $is_variable + 1; $i < $count; $i++){
                            if(
                                is_array($input[$i]) &&
                                array_key_exists('value', $input[$i])
                            ){
                                if(
                                    in_array(
                                        $input[$i]['value'],
                                        [
                                            '_',
                                            '.'
                                        ]
                                    )
                                ){
                                    $input[$i] = null;
                                } else {
                                    break;
                                }
                            } else {
                                $input[$i] = null;
                            }
                        }
                    }
                }
            }

        }
        d($input);

        return $input;
    }
}