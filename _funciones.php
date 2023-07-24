<?php
function cuando_tiene_texto($raw_body){

}
function cuando_tiene_archivos(array $archivos){

}
function jsonToAccion($json){
    if(is_string($json)){
        $json = json_decode($json,true);
    }    
    $class = new Accion();
    foreach($json as $k=>$v){
        $class->$k = $v;
    }
    return $class;
}