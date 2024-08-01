<?php
namespace Package\R3m\Io\Parse\Service;

use R3m\Io\App;

use R3m\Io\Module\File;

use Exception;
class Parse
{

    /**
     * @throws Exception
     */
    public static function compile(App $object, $flags, $options){
        if(!property_exists($options, 'source')){
            throw new Exception('Source not found');
        }
        if(File::exist($options->source) === false){
            throw new Exception('Source not found');
        }
        // Step 1: Read the template file
        $template = File::read($options->source);

        $tags = Parse::tags($object, $template, $flags, $options);
        $tags = Parse::tags_remove($object, $tags, $flags, $options);
        $tags = Parse::assign($object, $tags, $flags, $options);
        ddd($tags);

        // Step 2: Define the placeholder values
        $placeholders = [
                'name' => 'John Doe',
                'age' => '30',
                // Add more placeholders and their replacements as needed
        ];
        // Step 3: Replace placeholders with actual values
        foreach ($placeholders as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        // Step 4: Output the processed template
        dd($template);
    }

    public static function tags(App $object, $string=''): array
    {
        $split = mb_str_split($string, 1);
        $token = [];
        $curly_count = 0;
        $line = 1;
        $column = [];
        $column[$line] = 1;
        $row = '';
        $tag = false;
        $tag_list = [];
        $is_literal = false;
        foreach($split as $nr => $char){
            if($char === '{'){
                $curly_count++;
            }
            elseif($char === '}'){
                $curly_count--;
            }
            elseif($char === "\n"){
                $line++;
                $column[$line] = 1;
                if($curly_count === 0){
                    $row = '';
                }
            }
            if(
                $curly_count === 2 &&
                $char == '{'
            ){
                $tag = '{';
            }
            elseif($curly_count === 0){
                if($tag){
                    $tag .= $char;
                    $column[$line]++;
                    if(empty($tag_list[$line])){
                        $tag_list[$line] = [];
                    }
                    $explode = explode("\n", $tag);
                    $count = count($explode);
                    if($count > 1){
                        $content = trim(substr($tag, 2, -2));
                        $length = strlen($explode[0]);
                        $record = [
                            'tag' => $tag,
                            'is_multiline' => true,
                            'line' => [
                                'start' => $line - $count + 1,
                                'end' => $line
                            ],
                            'length' => [
                                'start' => $length,
                                'end' => strlen($explode[$count - 1])
                            ],
                            'column' => [
                                ($line - $count + 1) => [
                                    'start' => $column[$line - $count + 1] - $length,
                                    'end' => $column[$line - $count + 1]
                                ],
                                $line => [
                                    'start' => $column[$line] - strlen($explode[$count - 1]),
                                    'end' => $column[$line]
                                ]
                            ]
                        ];
                    } else {
                        $length = strlen($explode[0]);
                        $record = [
                            'tag' => $tag,
                            'line' => $line,
                            'length' => $length,
                            'column' => [
                                'start' => $column[$line] - $length,
                                'end' => $column[$line]
                            ]
                        ];
                        $content = trim(substr($tag, 2, -2));
                        if(strtoupper(substr($content, 0, 3)) === 'R3M'){
                            $record['is_header'] = true;
                            $record['content'] = $content;
                        }
                        elseif(
                            strtoupper($content) === 'LITERAL' ||
                            $is_literal === true
                        ){
                            $is_literal = true;
                            $record['is_literal'] = true;
                            $record['is_literal_start'] = true;
                        }
                        elseif(
                            strtoupper($content) === '/LITERAL' ||
                            $is_literal === true
                        ){
                            $is_literal = false;
                            $record['is_literal'] = true;
                            $record['is_literal_end'] = true;
                        }
                    }
                    $tag_list[$line][] = $record;
                    $tag = false;
                    $column[$line]--;
                }
            }
            $row .= $char;
            if($tag){
                $tag .= $char;
            }
            if($char !== "\n"){
                $column[$line]++;
            }
        }
        return $tag_list;
    }

    public static function tags_remove(App $object, $tags, $flags, $options): array
    {
        foreach($tags as $line => $tag){
            foreach($tag as $nr => $record){
                if(
                    array_key_exists('is_header', $record) ||
                    array_key_exists('is_literal', $record) &&
                    !array_key_exists('is_literal_start', $record) &&
                    !array_key_exists('is_literal_end', $record)
                ){
                    unset($tags[$line][$nr]);
                    if(empty($tags[$line])){
                        unset($tags[$line]);
                    }
                }
            }
        }
        return $tags;
    }

    public static function assign(App $object, $tags, $flags, $options): array
    {
        foreach($tags as $line => $tag){
            foreach($tag as $nr => $record){
                if(
                    array_key_exists('tag', $record)
                ){
                    $content = trim(substr($record['tag'], 2, -2));
                    if(substr($content, 0, 1) === '$'){
                        //we have a variable assign
                        $length = strlen($content);
                        $data = mb_str_split($content, 1);
                        $operator = false;
                        $before = '';
                        $after = '';
                        $is_after = false;
                        $after_array = [];
                        for($i=0; $i < $length; $i++){
                            $char = $data[$i];
                            if(
                                !$operator &&
                                in_array(
                                    $char,
                                    [
                                        '=',
                                        '.',
                                        '+',
                                        '-'
                                    ],
                                    true
                                )
                            ){
                                $operator = $char;
                                continue;
                            }
                            if($operator && $is_after === false){
                                if(
                                    $char === ' ' ||
                                    $char === "\t" ||
                                    $char === "\n" ||
                                    $char === "\r"
                                ) {
                                    $is_after = true;
                                } else {
                                    if($operator === '.' && $char === '='){
                                        //fix false positives
                                    } elseif($operator === '.'){
                                        $before .= $operator . $char;
                                        $operator = false;
                                    }
                                }
                            }
                            elseif($is_after) {
                                if(
                                    (
                                        $char === ' ' ||
                                        $char === "\t" ||
                                        $char === "\n" ||
                                        $char === "\r"
                                    ) &&
                                    $after === ''
                                ) {
                                    continue;
                                }
                                $after .= $char;
                                $after_array[] = $char;
                            }
                            elseif(
                                $char !== ' ' &&
                                $char !== "\t" &&
                                $char !== "\r" &&
                                $char !== "\n"
                            ){
                                $before .= $char;
                            }
                        }
                        $list = Parse::value(
                            $object,
                            [
                                'string' => $after,
                                'array' => $after_array
                            ],
                            $flags,
                            $options
                        );
                        $record['variable'] = [
                            'is_assign' => true,
                            'operator' => $operator,
                            'name' => substr($before, 1),
                            'value' => $list,
                        ];
                        ddd($record);
                    }
                }
            }
        }
        return $tags;
    }

    public static function value(App $object, $input, $flags, $options): mixed
    {
        $value = $input['string'] ?? null;
        switch($value){
            case '[]':
                return [[
                    'execute' => [],
                    'is_array' => true
                ]];
            case 'true':
                return [[
                    'execute' => true,
                    'is_boolean' => true
                ]];
            case 'false':
                return [[
                    'execute' => false,
                    'is_boolean' => true
                ]];
            case 'null':
                return [[
                    'execute' => null,
                    'is_null' => true
                ]];
            default:
                if(
                    substr($value, 0, 1) === '\'' &&
                    substr($value, -1) === '\''
                ){
                    return [[
                        'execute' => substr($value, 1, -1),
                        'is_single_quoted' => true
                    ]];
                }
                d($input['array']);
                return Parse::value_split($object, $input['array'], $flags, $options);
        }
    }

    public static function set_has(App $object, $input, $flags, $options){
        foreach($input as $nr => $char){
            if($char === '('){
                return true;
            }
        }
    }

    public static function set_highest(App $object, $input, $flags, $options){
        $highest = 0;
        $depth = 0;
        foreach($input as $nr => $char){
            if($char === '('){
                $depth++;
                if($depth > $highest) {
                    $highest = $depth;
                }
            }
            elseif($char === ')'){
                $depth--;
            }
        }
        return $highest;
    }

    public static function set_get(App $object, $input, $flags, $options){
        $highest = Parse::set_highest($object, $input, $flags, $options);
        $set = [];
        $is_collect = false;
        $depth = 0;
        $is_single_quoted = false;
        $is_double_quoted = false;
        foreach($input as $nr => $char){
            if($char === '('){
                $is_collect = true;
                $depth++;
            }
            elseif($char === ')'){
                $is_collect = false;
                $depth--;
                break;
            }
            elseif($is_collect && $depth === $highest){
                if(
                    $char === '\'' &&
                    $is_single_quoted === false
                ){
                    $is_single_quoted = true;
                }
                if(
                    $char === '"' &&
                    $is_double_quoted === false
                ){
                    $is_double_quoted = true;
                }
                if(
                    $is_single_quoted === false &&
                    $is_double_quoted === false &&
                    in_array(
                        $char,
                        [
                            " ",
                            "\t",
                            "\n",
                            "\r"
                        ],
                        true
                    )
                ){
                    continue;
                }
                $set[$nr] = $char;
            }
        }
        return $set;
    }


    public static function value_split(App $object, $input, $flags, $options){
        $set_depth = 0;
        $array_depth = 0;
        $collect = [];
        $list = [];

        if(Parse::set_has($object, $input, $flags, $options)){
            $set = Parse::set_get($object, $input, $flags, $options);
            ddd($set);
        }

//        $set = Parse::set_get($object, $input, $flags, $options);

        ddd($input);




        foreach($input as $nr => $char){
            if($char === '('){
                $set_depth++;
            }
            elseif($char === ')'){
                $set_depth--;
                if($set_depth === 0){
                    $collect[] = $char;
                    $cast = implode('', $collect);
                    switch($cast){
                        case '(bool)' :
                        case '(boolean)' :
                            $list[] = [
                                'value' => $collect,
                                'is_cast' => true,
                                'cast' => 'boolean'
                            ];
                        break;
                        case '(object)' :
                            $list[] = [
                                'value' => $collect,
                                'is_cast' => true,
                                'cast' => 'object'
                            ];
                        break;
                        case '(array)' :
                            $list[] = [
                                'value' => $collect,
                                'is_cast' => true,
                                'cast' => 'array'
                            ];
                        break;
                        case '(int)' :
                        case '(integer)' :
                            $list[] = [
                                'value' => $collect,
                                'is_cast' => true,
                                'cast' => 'integer'
                            ];
                        break;
                        case '(float)' :
                        case '(double)' :
                            $list[] = [
                                'value' => $collect,
                                'is_cast' => true,
                                'cast' => 'float'
                            ];
                        break;
                        case '(clone)' :
                            $list[] = [
                                'value' => $collect,
                                'is_cast' => true,
                                'cast' => 'clone'
                            ];
                        break;
                    }
                    $collect = [];
                    $has_cast = true;
                }
            }
            elseif($char === '['){
                $array_depth++;
            }
            elseif($char === ']'){
                $array_depth--;
                if($array_depth === 0){
                    $collect[] = $char;
                    $collect = Parse::value_array($object, $collect, $flags, $options);
                    $list[] = [
                        'value' => $collect,
                        'is_array' => true
                    ];
                    $collect = [];
                }
            }
            if($set_depth >= 1){
                $collect[] = $char;
            }
            elseif($array_depth >= 1){
                $collect[] = $char;
            }
        }
        return $list;
    }

    public static function value_array(App $object, $input, $flags, $options){
        $array_depth = 0;
        $array = [];
        $key = [];
        $counter = 0;
        $value = [];
        $is_single_quoted = false;
        $is_double_quoted = false;
        $is_value = false;
        $previous_char = false;
        foreach($input as $nr => $char){
            if(
                $is_single_quoted === false &&
                $is_double_quoted === false &&
                $char == '['
            ){
                if($array_depth > 0){
                    if($is_value === false){
                        $key[] = $char;
                    } else {
                        $value[] = $char;
                    }
                }
                $array_depth++;
            }
            elseif(
                $is_single_quoted === false &&
                $is_double_quoted === false &&
                $char == ']'
            ){
                $array_depth--;
                if($array_depth === 0){
                    d($array);
                } else {
                    if($is_value === false){
                        $key[] = $char;
                    } else {
                        $value[] = $char;
                    }
                }
                /*
                if($array_depth === 0){
                    $array[] = $char;
                    $array = Parse::value_split($object, $array, $flags, $options);
                    return $array;
                }
                */
            }
            elseif($array_depth >= 1){
                if(
                    $previous_char !== '\\' &&
                    $char === '\'' &&
                    $is_single_quoted === false
                ){
                    $is_single_quoted = true;
                }
                elseif(
                    $previous_char !== '\\' &&
                    $char === '\'' &&
                    $is_single_quoted === true
                ){
                    $is_single_quoted = false;
                }
                elseif(
                    $previous_char !== '\\' &&
                    $char === '"' &&
                    $is_double_quoted === false
                ){
                    $is_double_quoted = true;
                }
                elseif(
                    $previous_char !== '\\' &&
                    $char === '"' &&
                    $is_double_quoted === true
                ){
                    $is_double_quoted = false;
                }
                if($is_value === false){
                    if(
                        $is_single_quoted === false &&
                        $is_double_quoted === false &&
                        in_array(
                            $char,
                            [
                                ' ',
                                "\n",
                                "\t"
                            ],
                            true
                        )
                    ){
                        //nothing
                    } else {
                        $key[] = $char;
                    }

                } else {
                    if(
                        $is_single_quoted === false &&
                        $is_double_quoted === false &&
                        in_array(
                            $char,
                            [
                                ' ',
                                "\n",
                                "\t"
                            ],
                            true
                        )
                    ){
                        //nothing
                    } else {
                        $value[] = $char;
                    }
                }
                if($previous_char === '=' && $char === '>'){
                    $is_value = true;
                }
                if(
                    $is_value &&
                    $is_single_quoted === false &&
                    $is_double_quoted === false &&
                    $char === ','
                ){
                    array_pop($value);
                    array_pop($key);
                    array_pop($key);

                    $array[] = [
                        'key' => Parse::value(
                            $object,
                            [
                            'string' => implode('', $key),
                            'array' => $key
                            ],
                            $flags,
                            $options
                        ),
                        'value' => Parse::value(
                            $object,
                            [
                                'string' => implode('', $value),
                                'array' => $value
                            ],
                            $flags,
                            $options
                        )
                    ];
                    $key = [];
                    $value = [];
                    $is_value = false;
                }
            }
            $previous_char = $char;
        }
        if(
            $is_value &&
            $is_single_quoted === false &&
            $is_double_quoted === false
        ){
            array_pop($key);
            array_pop($key);
            $array[] = [
                'key' => Parse::value(
                    $object,
                    [
                        'string' => implode('', $key),
                        'array' => $key
                    ],
                    $flags,
                    $options
                ),
                'value' => Parse::value(
                    $object,
                    [
                        'string' => implode('', $value),
                        'array' => $value
                    ],
                    $flags,
                    $options
                )
            ];
        }
        return $array;
    }
}