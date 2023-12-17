<?php
    require_once(dirname(dirname(__DIR__)) . '/inc/bootstrap.php');
    require_once(RUTA_RAIZ . 'modelos/TecnicoSoftware.php');

    $respuesta = array();
    $error = "";

    try {
        $datos = json_decode(file_get_contents("php://input"));
        $respuesta['estado'] = 'datos recibidos';
        $metodoSolicitud = $_SERVER["REQUEST_METHOD"];
        $retorno = null;
        switch(strtoupper($metodoSolicitud)) {
            case 'GET':
                $error = "No se pudo iniciar sesión";
                if(isset($_GET['nombreUsuario_tecnicoSoftware']) && isset($_GET['clave_tecnicoSoftware'])){
                    $nombreUsuario_tecnicoSoftware = $_GET['nombreUsuario_tecnicoSoftware'];
                    $clave_tecnicoSoftware = $_GET['clave_tecnicoSoftware'];
                    $tecnicoSoftware = new TecnicoSoftware($nombreUsuario_tecnicoSoftware, $clave_tecnicoSoftware);
                    if($tecnicoSoftware->tieneClave($clave_tecnicoSoftware)){
                        $respuesta['sesion'] = $nombreUsuario_tecnicoSoftware;
                    }else{
                        throw new Exception("Nombre de usuario o clave incorrectos");
                    }
                    $retorno = "Se inició sesión";
                }
                else{
                    throw new Exception("Parámetros obligatorios: 'nombreUsuario_tecnicoSoftware', 'clave_tecnicoSoftware'");
                }
                break;
            case 'POST':
                $error = "No se pudo registrar al técnico de software";
                validarPOST($datos);
                $tecnicoSoftware = new TecnicoSoftware($datos->nombreUsuario_tecnicoSoftware, $datos->clave_tecnicoSoftware);
                $tecnicoSoftware->guardar();
                $retorno = "Técnico de software registrado";
                break;
            case 'PUT':
                $error = "No se pudo modificar al técnico de software";
                validarTecnicoSoftware();
                validarPUT($datos);
                $tecnicoSoftware = new TecnicoSoftware($datos->nombreUsuario_tecnicoSoftware, $datos->clave_tecnicoSoftware);
                $tecnicoSoftware->actualizar();
                $retorno = "Técnico de software actualizado";
                break;
            default:
                throw new Exception("Método no permitido");  
        }
        $respuesta['retorno'] = $retorno;
    } catch (Exception $e) {
        $respuesta['error'] = $error . ": " . $e->getMessage();
    } finally {
        echo json_encode($respuesta);
    }

    function validarPOST($datos){
        $parametros = [
            ["nombre" => "nombreUsuario_tecnicoSoftware", "obligatorio" => true],
            ["nombre" => "clave_tecnicoSoftware", "obligatorio" => true]
        ];
        validarParametros2($datos, $parametros);
    }
    
    function validarPUT($datos){
        $parametros = [
            ["nombre" => "nombreUsuario_tecnicoSoftware", "obligatorio" => true],
            ["nombre" => "clave_tecnicoSoftware", "obligatorio" => true]
        ];
        validarParametros2($datos, $parametros);
    }
?>