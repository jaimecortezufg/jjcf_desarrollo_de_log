<?php
	ini_set('display_errors', '1');
	ini_set('display_startup_errors', '1');
	error_reporting(E_ALL);
	include("core/inc/funciones.inc.php");
	include("core/secure/ips.php");
	$archivo = "./logs/log.log";
	$ip = ip_in_ranges($_SERVER['REMOTE_ADDR'], $rango);
?>
<!DOCTYPE html>
<html lang="es-SV">
	<head>
		<title>Inicio de sesión</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" href="css/bootstrap.css">
		<link href="fonts/fontawesome/css/all.css" rel="stylesheet" />
		<script type="text/javascript" src="js/jquery-3.7.1.min.js"></script>
		<script type="text/javascript" src="js/bootstrap.js"></script>
		<script type="text/javascript" src="js/sweetalert2.all.js"></script>
		<script type="text/javascript" src="fonts/fontawesome/js/all.js"></script>
	</head>
	<body>
<?php
	$visible = false;
	$texto = "";
	$status = ((array_key_exists("status",$_GET)) ? $_GET["status"] : "");
	switch($status){
		case "1":
			$texto = "Por favor, utilizar el formulario para iniciar sesión";
			$visible = true;
		break;
		case "2":
			$texto = "Las peticiones son aceptadas únicamente desde nuestro dominio oficial";
			$visible = true;
		break;
		case "3":
			$texto = "Lo siento, su dirección IP no está autorizada para continuar";
			$visible = true;
		break;
		case "4":
			$texto = "Se ha detectado un método no válido enviado hacia el servidor";
			$visible = true;
		break;
		case "5":
			$texto = "No se han recibido los datos completos del formulario, al menos un campo está vacío";
			$visible = true;
		break;
		case "6":
			$texto = "Se han detectado caracteres no válidos en los datos enviados";
			$visible = true;
		break;
		case "7":
			$texto = "Las credenciales ingresadas son incorrectas";
			$visible = true;
		break;
		default:
			$texto = "";
			$visible = false;
		break;
	}
?>
	<div class="alert alert-warning <?php echo(($visible === true) ? "" : "d-none") ?>" role="alert">
		<b><?php echo($texto); ?></b>
	</div>
<?php
	if($ip === true){
		crear_editar_log($archivo,"El archivo ".__FILE__." ha sido cargado",0,$_SERVER['REMOTE_ADDR'],((array_key_exists("HTTP_REFERER",$_SERVER)) ? $_SERVER["HTTP_REFERER"] : "NULL REFERER"),$_SERVER["HTTP_USER_AGENT"]);
?>
		<div class="form-row">
			<div class="form-group col-md-5	text-center">
				<img src="media/logo/logo_corporativo.png" class="mx-auto d-block" id="img" width="65%" height="auto"/>
			</div>
			<div class="form-group col-md-5 ml-4 mr-4 justify-content-center align-self-center">
				<h1>Diseñando Estrategias para la Recuperación y Migración de Base de Datos (RBK0)</h1>
				<form name="frm_iniciar_sesion" id="frm_iniciar_sesion" action="core/process.php" method="post">
					<div class="form-group">
						<label for="txt_user">Usuario:</label>
						<input type="text" class="form-control" id="txt_user" name="txt_user" aria-describedby="txt_userHelp" maxlength="10" required>
						<small id="txt_userHelp" class="form-text text-muted">Digite un usuario (campo obligatorio).</small>
					</div>
					<div class="form-group">
						<label for="txt_pass">Contraseña:</label>
						<input type="password" class="form-control" id="txt_pass" name="txt_pass" aria-describedby="txt_passHelp" maxlength="10" required>
						<small id="txt_passHelp" class="form-text text-muted">La contraseña es obligatoria.</small>
					</div>
					<button type="submit" id="btn_ingresar" name="btn_ingresar" class="btn btn-primary mx-auto d-block" value="ingresar">Iniciar sesión</button>
				</form>
			</div>
		</div>
<?php
	}else{
		$redirect = "https://www.ufg.edu.sv/";
		echo("Su dirección IP no tiene permitida la visita a esta página, será redirigido en breves segundos a un sitio seguro");
		crear_editar_log($archivo,"Dirección IP no autorizada ha cargado el archivo ".__FILE__." ha sido redirigido a: $redirect",1,$_SERVER['REMOTE_ADDR'],$_SERVER["HTTP_REFERER"],$_SERVER["HTTP_USER_AGENT"]);
		header( "refresh:5; url=$redirect" ); 
	}
?>
	</body>
</html>