<?php
    require_once(dirname(dirname(__DIR__)) . '/inc/bootstrap.php');
    //require_once(RUTA_RAIZ . 'modelos/Clase.php');

    $respuesta = array();

    try {
        $error = "Sesión no válida";
        validarTecnicoSoftware();
        $datos = json_decode(file_get_contents("php://input"));
        $respuesta['estado'] = 'datos recibidos';
        $metodoSolicitud = $_SERVER["REQUEST_METHOD"];
        $retorno = null;
        switch(strtoupper($metodoSolicitud)) {
            case 'GET':
                $error = "No se pudo obtener a los ...";
                if(isset($_GET['parametro'])){
                    $datos = $_GET['parametro'];
                    $parametro = 'parametro';
                }
                //$retorno = Clase::listar($datos, $parametro);
                break;
            case 'POST':
                $error = "No se pudo registrar al ...";
                validarPOST($datos);
                //$objeto = new Clase($datos->parametro, $datos->parametro);
                //$objeto->guardar();
                $retorno = "... registrado";
                break;
            case 'PUT':
                $error = "No se pudo modificar al ...";
                $datos = validarPUT($datos);
                //$objeto = new Clase($datos->parametro, $datos->parametro);
                //$objeto->actualizar();
                $retorno = "... actualizado";
                break;
            case 'DELETE':
                $error = "No se pudo eliminar al ...";
                validarDELETE($datos);
                //Clase::eliminar($datos->parametro);
                $retorno = "... eliminado";
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
            ["nombre" => "parametro1", "obligatorio" => false],
            ["nombre" => "parametro2", "obligatorio" => false],
            ["nombre" => "parametro3", "obligatorio" => false]
        ];
        validarParametros2($datos, $parametros);
    }
    
    function validarPUT($datos){
        $parametros = [
            ["nombre" => "parametro1", "obligatorio" => false],
            ["nombre" => "parametro2", "obligatorio" => false],
            ["nombre" => "parametro3", "obligatorio" => false]
        ];
        validarParametros2($datos, $parametros);
    }

    function validarDELETE($datos){
        $parametros = [
            ["nombre" => "parametro", "obligatorio" => true]
        ];
        validarParametros2($datos, $parametros);
    }
?>