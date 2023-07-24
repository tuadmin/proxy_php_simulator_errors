<?php
class Respuesta{
    public $exitoso = false;
    public $error = "";
    public $dato = null;
    public function __construct(bool $exitoso,$error,$dato){
        $this->exitoso = $exitoso;
        $this->error = $error;
        $this->dato = $dato;
    }
    public function __toString(){
        return json_encode($this,JSON_PRETTY_PRINT);
    }
}