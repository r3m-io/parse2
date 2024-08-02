{{R3M}}
{{$response = Package.R3m.Io.Parse:Main:compile(flags(), options())}}
{{$response|object:'json'}}

if($char === '|' && $next !== '|'){
$is_modifier = true;
}
elseif($is_modifier || $is_argument){
if($char === '\'' && $is_single_quoted === false){
$is_single_quoted = true;
}
elseif($char === '\'' && $is_single_quoted === true){
$is_single_quoted = false;
}
elseif($char === '"' && $is_double_quoted === false){
$is_double_quoted = true;
}
elseif($char === '"' && $is_double_quoted === true){
$is_double_quoted = false;
}
if(
$is_single_quoted === false &&
$is_double_quoted === false &&
$char === '|' &&
$next !== '|'
){
if($modifier){
$modifier_list[] = [
'string' => $modifier,
'array' => $modifier_array
];
$modifier = '';
$modifier_array = [];
}
$is_argument = false;
}
if(
$is_single_quoted === false &&
$is_double_quoted === false &&
$char === ':'
){
if($argument){
$argument_list[] = [
'string' => $argument,
'array' => $argument_array,
];
$argument = '';
$argument_array = [];
}
$is_argument = true;
}
if($is_argument){
$argument .= $char;
$argument_array[] = $char;
} else {
$modifier .= $char;
$modifier_array[] = $char;
}
}