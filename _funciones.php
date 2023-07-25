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
function guardarRespuestaServidor($urlPeticion,$respuesta,array $otros = array()){
    if(!file_exists(__DIR__.'/logs')){
        mkdir(__DIR__.'/logs',0777,true);
    }
    $data = array(
        'tiempo'=>time(),
        "url"=>$urlPeticion,
        "respuesta"=>$respuesta,
    );
    foreach($otros as $k=>$v){
        $data[$k] = $v;
    }
    $maximos_archivos = 10;
    $archivo_tpl = __DIR__.'/logs/%s.json';
    $id_ultimo = 0;
    clearstatcache();
    for($i=0;$i<=$maximos_archivos;$i++){
        $archivo = sprintf($archivo_tpl,$i);
        if(!file_exists($archivo)){
            $id_ultimo = $i;
            break;
        }
    }
    if($id_ultimo == $maximos_archivos){
        $id_usar = 0;
    }else{
        $id_usar = $id_ultimo;
    }
    file_put_contents(sprintf($archivo_tpl,$id_usar),json_encode($data));
    @unlink(sprintf($archivo_tpl,$id_ultimo+1));   
}
function obtenerRespuestaServidor(){
    $array = array();
    if(file_exists(__DIR__.'/logs')){
        foreach(new DirectoryIterator(__DIR__.'/logs') as $fileInfo) {
            if($fileInfo->isDot()) continue;
            if($fileInfo->getExtension() != 'json') continue;
            $archivo = $fileInfo->getPathname();
            $data = json_decode(file_get_contents($archivo),true);
            $array[] = $data;
        }
        //ordenamos por el ultimo
        usort($array,function($a,$b){
            if($a['tiempo'] == $b['tiempo']) return 0;
            return ($a['tiempo'] > $b['tiempo']) ? -1 : 1;
        });
    }
    return $array;
}