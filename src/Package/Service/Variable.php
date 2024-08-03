<?php
namespace Package\R3m\Io\Parse\Service;

use R3m\Io\App;

use R3m\Io\Module\Core;
use R3m\Io\Module\File;

use Exception;
class Variable
{
    public static function define(App $object, $input, $flags, $options){
        $count = count($input['array']);
        $is_variable = false;
        foreach($input['array'] as $nr => $char){
            $previous = $input['array'][$nr - 1] ?? null;
            if(
                is_array($char) &&
                array_key_exists('value', $char)
            ){
                if($char['value'] === '$'){
                    $is_variable = $nr;
                    $name = '$';
                    for($i = $nr + 1; $i < $count; $i++){
                        if(
                            is_array($input['array'][$i]) &&
                            array_key_exists('value', $input['array'][$i])
                        ){
                            if(
                                in_array(
                                    $input['array'][$i]['value'],
                                    [
                                        '_',
                                        '.'
                                    ]
                                )
                            ){
                                $name .= $input['array'][$i]['value'];
                            } else {
                                break;
                            }
                        } else {
                            if(
                                !in_array(
                                    $input['array'][$i],
                                    [
                                        ' ',
                                        "\n",
                                        "\r",
                                        "\t"
                                    ]
                                )
                            ){
                                $name .= $input['array'][$i];
                            }

                        }
                    }
                    if($name){
                        $is_reference = false;
                        if(
                            $previous !== null &&
                            is_array($previous) &&
                            array_key_exists('value', $previous)
                        ){
                            $is_reference = $previous['value'] === '&';
                            if($is_reference){
                                $input['array'][$nr - 1] = null;
                            }
                        }
                        $input['array'][$is_variable] = [
                            'type' => 'variable',
                            'value' => $name,
                            'name' => substr($name, 1),
                            'is_reference' => $is_reference
                        ];
                        $has_modifier = false;
                        $has_name = false;
                        $argument = [];
                        $argument_list = [];
                        $modifier_name = '';
                        for($i = $is_variable + 1; $i < $count; $i++){
                            $previous = $input['array'][$i - 1] ?? null;
                            $next = $input['array'][$i + 1] ?? null;
                            if(
                                is_array($input['array'][$i]) &&
                                array_key_exists('value', $input['array'][$i])
                            ){
                                if(
                                    in_array(
                                        $input['array'][$i]['value'],
                                        [
                                            '_',
                                            '.'
                                        ]
                                    ) &&
                                    $has_modifier === false
                                ){
                                    $input['array'][$i] = null;
                                }
                                elseif(
                                    $input['array'][$i]['value'] === '|' &&
                                    $previous !== '|' &&
                                    $next !== '|'
                                ){
                                    $has_modifier = true;
                                    /*
                                    if(array_key_exists(0, $argument)){
                                        $argument_list[] = Parse::value_split(
                                            $object,
                                            $argument,
                                            $flags,
                                            $options
                                        );
                                        $argument = [];
                                    }
                                    if(array_key_exists(0, $argument_list)){
                                        $input[$is_variable]['modifier'][] = [
                                            'name' => $modifier_name,
                                            'argument' => $argument_list
                                        ];
                                        ddd($input[$is_variable]);
                                        $argument_list = [];
                                        $argument = [];
                                    }
                                    */
                                }
                                elseif($has_modifier !== true) {
                                    break;
                                }
                                /*
                                if($has_modifier === true){
                                    $argument[] = $input[$i];
                                }
                                */
                            }
                            elseif($has_modifier !== true) {
                                $input['array'][$i] = null;
                            }
                            elseif($has_modifier === true){
                                if(is_array($input['array'][$i])){
                                    d($input['array'][$i]);
                                    d($modifier_name);
                                    /*
                                    if($input[$i]['value'] === ':'){
                                        if($has_name === true) {
                                            $argument_list[] = Parse::value_split(
                                                $object,
                                                $argument,
                                                $flags,
                                                $options
                                            );
                                            ddd($argument_list);
                                            $argument = [];
                                        } else {
                                            $has_name = true;
                                        }
                                    }
                                    if($has_name === false){
                                        $modifier_name .= $input[$i]['value'];
                                    }
                                    */
                                }
                                /*
                                elseif($has_name === false) {
                                    $modifier_name .= $input[$i];
                                } else {
                                    $argument[] = $input[$i];
                                }
                                */
                            }
                        }
                        /*
                        if(array_key_exists(0, $argument)){
                            ddd($argument);
                            $argument_list[] = Parse::value_split(
                                $object,
                                $argument,
                                $flags,
                                $options
                            );
                            ddd($argument_list);
                            $argument = [];
                        }
                        */
//                        d($input[$is_variable]);
                    }
                }
            }

        }
//        $input = Parse::remove_whitespace($object, $input, $flags, $options);
        return $input;
    }
}