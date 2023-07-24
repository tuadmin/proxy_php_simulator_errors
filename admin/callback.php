<?php
require_once __DIR__ .'/../_main.php';

class funcionesJs{
    static public $lista=[];
    static public $inicio = null;
    public function __construct(){
        funcionesJs::$lista = array();
        funcionesJs::$inicio = new Inicio();
        if(file_exists(FILE_ACCIONES)){
            $lista = json_decode(file_get_contents(FILE_ACCIONES),true);
            //var_dump($lista);
            foreach($lista as $linea){
                funcionesJs::$lista[] = jsonToAccion($linea);
            }            
        }
        if(file_get_contents(FILE_INICIO)){
            $inicio = json_decode(file_get_contents(FILE_INICIO),true);
            foreach($inicio as $k=>$v){
                funcionesJs::$inicio->$k = $v;
            }
        }
    }
    function listar(){        
        return self::$lista;
    }
    function obtener($id){
        foreach(self::$lista as $c){
            if($c->id == $id){
                return $c;
            }
        }
        throw new Exception("no se encontro la accion con id: $id");
    }
    function obtenerUrl(){
        return static::$inicio->redirige;
    }
    function eliminar($id){
        foreach(self::$lista as $k=>$c){
            if($c->id == $id){
                unset(self::$lista[$k]);
                file_put_contents(FILE_ACCIONES,json_encode(self::$lista,JSON_PRETTY_PRINT));                
            }
        }
        return true;
    }
    function editarOAgregar(Accion $coincidencia){
        $encontrado = false;
        foreach(self::$lista as $k=> $c){
            if($c->id == $coincidencia->id){
                self::$lista[$k] = $coincidencia;
                $encontrado = true;
            }
        }
        if(!$encontrado){
            self::$lista[] = $coincidencia;
        }
        file_put_contents(FILE_ACCIONES,json_encode(self::$lista,JSON_PRETTY_PRINT));
        return true;
    }
    function editarUrl($url){
        static::$inicio->redirige = $url;
        file_put_contents(FILE_INICIO,json_encode(static::$inicio,JSON_PRETTY_PRINT));
        return true;        
    }
    function activarAccion($id,$onOff){
        switch(strtolower($onOff)){
            case "true":
            case "on":
            case "1":
                $onOff = true;
                break;
            case "false":
            case "off":
            case "0":
                $onOff = false;
                break;
            default:
                throw new Exception("el parametro \$onOff no es valido");
        }
        foreach(self::$lista as $k=>$c){
            if($c->id == $id){
                self::$lista[$k]->activo = $onOff;
                file_put_contents(FILE_ACCIONES,json_encode(self::$lista,JSON_PRETTY_PRINT));
                return true;
            }
        }
        throw new Exception("no se encontro la accion");
    }
}
try{
    //$_SERVER['REQUEST_METHOD'] == 'POST' or throw new Exception("solo se aceptan peticiones POST");
    if(!isset($_GET['funcion'])){
        throw new Exception("no se especifico la funcion");
    }
    
    $body = null;
    $instancia = new funcionesJs();
    $argumentos = array();
    if(!method_exists($instancia,$_GET['funcion'])){
        throw new Exception("la funcion no existe");
    }
    if($_SERVER['REQUEST_METHOD']=='GET'){
        if(isset($_GET['arg'])){
            //$body = $_GET['arg'];
            $argumentos[] = $_GET['arg'];
        }
        if(isset($_GET['arg2'])){
            //$body = $_GET['arg'];
            $argumentos[] = $_GET['arg2'];
        }
    }else{
        $body_raw = file_get_contents('php://input');
        $body = json_decode($body_raw);
        if(!$body){
            throw new Exception("no se pudo decodificar el json");
        }
        switch($_GET['funcion']){
            case "editarOAgregar":
                $body = jsonToAccion(json_encode($body));
                break;                        
        }
        if($body!=null){
            $argumentos[] = $body;
        }
    }
    
    $respuesta = new Respuesta(true,'',
        call_user_func_array(array($instancia,$_GET['funcion']),$argumentos)
    );   
}
catch(Exception $e){
    $respuesta = new Respuesta(false,$e->getMessage(),null);
    echo $respuesta;
    exit;
}
echo $respuesta;
