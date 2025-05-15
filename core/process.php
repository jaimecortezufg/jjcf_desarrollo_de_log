<?php
ini_set('display_error',1);
ini_set('display_startup_error',1);
include('inc/funciones.inc.php');
include('secure/ips.php');

$metodo_permitido = "POST";
$archivo = "../logs/log.log";
$dominio_autorizado = "localhost";
$ip = ip_in_ranges($_SERVER["REMOTE_ADDR"],$rango);
$txt_usuario_autorizado = "admin";
$txt_password_autorizado = "admin";

//SE VERIFICA EL USUARIO HAYA NAVEGADO EN NUESTRO SISTEMA PARA LLEGAR AQUI A ESTE ARCHIVO
if(array_key_exists("HTTP_REFERER",$_SERVER)){
    //VIENE DE UNA PAGINA DENTRO DEL SISTEMA
    //SE VERIFICA QUE LA DIRECCION DE ORIGEN SEA AUTORIZADA
    if(strpos($_SERVER["HTTP_REFERER"],$dominio_autorizado)){
        //EL REFERER DE DONDE VIENE LA PETICIÓN ESTÁ AUTORIZADO
        //SE VERIFICA QUE LA DIRECCIÓN IP ESTÉ AUTORIZADA
        if($ip === true){
            //LA DIRECCIÓN IP DEL USUARIO SÍ ESTÁ AUTORIZADA
            //SE VERIFICA QUE EL USUARIO HAYA ENVIADO UNA PETICIÓN AUTORIZADA
            if($_SERVER["REQUEST_METHOD"] == $metodo_permitido){
                //EL METODO ENVIADO POR EL USUARIO SÍ ESTÁ AUTORIZADO
                //LIMPIEZA DE VALORES QUE VIENEN DESDE EL FORMULARIO
                $valor_campo_usuario = (     (array_key_exists("txt_user",$_POST)) ? htmlspecialchars(stripslashes(trim($_POST["txt_user"])),ENT_QUOTES) : ""   );
                $valor_campo_password = (     (array_key_exists("txt_pass",$_POST)) ? htmlspecialchars(stripslashes(trim($_POST["txt_pass"])),ENT_QUOTES) : ""   );
                //SE VERIFICA QUE LOS VALORES DE LOS CAMPOS SEAN DIFERENTES DE VACÍO
                if(  ($valor_campo_usuario!="" || strlen($valor_campo_usuario) > 0)  and   ($valor_campo_password!="" || strlen($valor_campo_password) > 0) ){
                    //LAS VARIABLES SÍ TIENEN VALORES
                    $usuario = preg_match('/^[a-zA-Z0-9]{1,10}+$/',$valor_campo_usuario); //SE VERIFICA CON UN PATRÓN SI EL VALOR DEL CAMPO "usuario" CUMPLE CON LAS CONDICIONES ACEPTABLES (SE ACEPTAN NÚMEROS, LETRAS MAYÚSCULAS Y LETRAS MINÚSCULAS, EN UN MÁXIMO DE 10 CARACTERES Y UN MÍNIMO DE 1 CARACTER)
                    $password = preg_match('/^[a-zA-Z0-9]{1,10}+$/',$valor_campo_password); //SE VERIFICA CON UN PATRÓN SI EL VALOR DEL CAMPO "contraseña" CUMPLE CON LAS CONDICIONES ACEPTABLES (SE ACEPTAN NÚMEROS, LETRAS MAYÚSCULAS Y LETRAS MINÚSCULAS, EN UN MÁXIMO DE 10 CARACTERES Y UN MÍNIMO DE 1 CARACTER)
                    //SE VERIFICA QUE LOS RESULTADOS DEL PATRÓN SEAN EXCLUSIVAMENTE POSITIVOS O SATISFACTORIOS
                    if($usuario !== false and $usuario !== 0 and $password !== false and $password !== 0){
                        //EL USUARIO Y LA CONTRASEÑA SÍ POSEEN VALORES ACEPTADOS
                        if($valor_campo_usuario === $txt_usuario_autorizado and $valor_campo_password === $txt_password_autorizado){
                            //EL USUARIO INGRESÓN LAS CREDENCIALES CORRECTAS
                            echo("HOLA MUNDO");
                            crear_editar_log($archivo,"EL CLIENTE INICIÓ SESIÓN SATISFACTORIAMENTE",1,$_SERVER["REMOTE_ADDR"],$_SERVER["HTTP_REFERER"],$_SERVER["HTTP_USER_AGENT"]);
                        }else{
                            //EL USUARIO NO INGRESÓN LAS CREDENCIALES CORRECTAS
                            crear_editar_log($archivo,"CREDENCIALES INCORRECTAS ENVIADAS HACIA //$_SERVER[HTTP_HOST]
                            $_SERVER[HTTP_REQUEST_URI]",2,$_SERVER["REMOTE_ADDR"],$_SERVER["HTTP_REFERER"],$_SERVER["HTTP_USER_AGENT"]);
                            header("HTTP/1.1 301 Moved Permanently");
                            header("Location: ../?status=7");
                        }
                    }else{
                        //LOS VALORES INGRESADOS EN LOS CAMPOS POSEEN CARACTERES NO SOPORTADOS
                        crear_editar_log($archivo,"ENVIO DE DATOS DEL FORMULARIO CON CARACTERES NO SOPORTADOS",3,$_SERVER["REMOTE_ADDR"],$_SERVER["HTTP_REFERER"],$_SERVER["HTTP_USER_AGENT"]);
                        header("HTTP/1.1 301 Moved Permanently");
                        header("Location: ../?status=6");
                    }
                }else{                    
                    //LAS VARIABLES ESTÁN VACÍAS
                    crear_editar_log($archivo,"ENVÍO DE CAMPOS VACÍOS AL SERVIDOR",2,$_SERVER["REMOTE_ADDR"],$_SERVER["HTTP_REFERER"],$_SERVER["HTTP_USER_AGENT"]);
                    header("HTTP/1.1 301 Moved Permanently");
                    header("Location: ../?status=5");
                }
            }else{
                //EL MÉTODO ENVIADO POR EL USUARIO NO ESTÁ AUTORIZADO
                crear_editar_log($archivo,"ENVÍO DE MÉTODO NO AUTORIZADO",2,$_SERVER["REMOTE_ADDR"],$_SERVER["HTTP_REFERER"],$_SERVER["HTTP_USER_AGENT"]);
                header("HTTP/1.1 301 Moved Permanently");
                header("Location: ../?status=4");
            }
        }else{
            //LA DIRECCIÓN IP DEL USUARIO NO ESTÁ AUTORIZADA
            crear_editar_log($archivo,"DIRECCIÓN IP NO AUTORIZADA",2,$_SERVER["REMOTE_ADDR"],$_SERVER["HTTP_REFERER"],$_SERVER["HTTP_USER_AGENT"]);
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: ../?status=3");
        }
    }else{
        //EL REFERER DE DONDE VIENE LA PETICIÓN ES DE UN ORIGEN DESCONOCIDO
        crear_editar_log($archivo,"HA INTENTADO SUPLANTAR UN REFERER QUE NO ESTÁ AUTORIZADO",2,$_SERVER["REMOTE_ADDR"],$_SERVER["HTTP_REFERER"],$_SERVER["HTTP_USER_AGENT"]);
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: ../?status=2");
    }
}else{
    //EL USUARIO DIGITÓ LA URL DESDE EL NAVEGADOR SIN PASAR POR EL FORMULARIO
    crear_editar_log($archivo,"EL USUARIO HA INTENTADO INGRESAR AL SISTEMA DE UNA MANERA INCORRECTA",2,$_SERVER["REMOTE_ADDR"],$_SERVER["HTTP_REFERER"],$_SERVER["HTTP_USER_AGENT"]);
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: ../?status=1");
}
?>