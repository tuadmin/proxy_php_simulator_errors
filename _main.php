<?php
define("ROOT_DIR",__DIR__);
define("FILE_ACCIONES",ROOT_DIR."/acciones.json");
define("FILE_INICIO",ROOT_DIR."/inicio.json");
require_once __DIR__ .'/_funciones.php';
class Autoloader
{
    public static function register()
    {
        spl_autoload_register(function ($class) {
            
            $file = __DIR__ ."/src/" . str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
            $file = str_replace("//","/",$file);
            if (file_exists($file)) {
                require $file;
                return true;
            }
            return false;
        });
    }
}
Autoloader::register();