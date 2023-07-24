<?php
class Accion{
    public $id;
    public $activo = false;
    public $descripcion;
    public $coincidencia_url = array();//debe ser un array con preg_match o texto
    public $coincidencia_texto=array();//debe ser un array con preg_match o texto
    public $retornar_texto = "";
    public $retornar_httpcode = 500;
    public $demorar = 0;
    public static function coincide($texto,$buscar){
        $buscar = trim($buscar);
        if($buscar == '.'){
            return true;
        }else if($buscar == ''){
            return false;
        }
        //verificamos si es busqueda normal o por expresion regular
        $simbolo = substr($buscar,0,1);
        //si es expresion regular tiene que empezar con algun simbolo y terminar con el mismo
        if(strpos($buscar,$simbolo,1)>0){
            //si no tiene modificadores le agregamos el global
            $tiene_modificador = substr($buscar,-1) == $simbolo?true:false;
            if(!$tiene_modificador){
                $buscar = $buscar."im";
            }            
            return preg_match($buscar,$texto);
        }else{
            return strpos($texto,$buscar) !== false;
        }
    }
    public function coincide_url($url){
        foreach($this->coincidencia_url as $c){
            if(self::coincide($url,$c)){
                return true;
            }
        }
        return false;
    }
    public function coincide_texto($texto){
        foreach($this->coincidencia_texto as $c){
            if(self::coincide($texto,$c)){
                return true;
            }
        }
        return false;
    }
}