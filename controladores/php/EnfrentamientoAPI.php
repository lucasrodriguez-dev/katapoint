<?php
    require_once(dirname(dirname(__DIR__)) . '/inc/bootstrap.php');
    require_once(RUTA_RAIZ . 'modelos/Enfrentamiento.php');
    require_once(RUTA_RAIZ . 'modelos/Torneo.php');

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
                $error = "No se pudo obtener a los enfrentamientos";
                if(isset($_GET['id_torneo']) && isset($_GET['ronda_enfrentamiento'])){
                    $id_torneo = $_GET['id_torneo'];
                    $ronda_enfrentamiento = $_GET['ronda_enfrentamiento'];
                    $retorno = Enfrentamiento::listarRondaTorneo($id_torneo, $ronda_enfrentamiento);
                }
                else if(isset($_GET['id_torneo']) && isset($_GET['clasificados'])){
                    $id_torneo = $_GET['id_torneo'];
                    $torneo = Torneo::getById($id_torneo);
                    $retorno = $torneo === null ? null : $torneo->listarClasificados();
                }
                else{
                    if(isset($_GET['id_torneo'])){
                        $datos = $_GET['id_torneo'];
                        $parametro = 'id_torneo';
                    }
                    if(isset($_GET['ronda_enfrentamiento'])){
                        $datos = $_GET['ronda_enfrentamiento'];
                        $parametro = 'ronda_enfrentamiento';

                    }
                    if(isset($_GET['id_enfrentamiento'])){
                        $datos = $_GET['id_enfrentamiento'];
                        $parametro = 'id_enfrentamiento';       
                    }
                    $retorno = Enfrentamiento::listar($datos, $parametro);
                }
                break;
            case 'POST':
                $error = "No se pudo generar los enfrentamientos";
                validarPOST($datos);
                $id_torneo = $datos->id_torneo;
                $torneo = Torneo::getById($id_torneo);
                if($torneo === null){
                    throw new Exception("Torneo no existente");
                }
                $torneo->registrarEnfrentamientos();
                $retorno = "Enfrentamientos generados";
                break;
            case 'PUT':
                $error = "No se pudo modificar al enfrentamiento";
                validarPUT($datos);
                $enfrentamiento = Enfrentamiento::getById($datos->id_enfrentamiento);
                if($enfrentamiento === null){
                    throw new Exception("Enfrentamiento no existente");
                }
                $enfrentamiento->setFecha($datos->fecha_enfrentamiento);
                $enfrentamiento->actualizar();
                $retorno = "Enfrentamiento actualizado";
                break;
            case 'DELETE':
                $error = "No se pudo eliminar a los enfrentamientos";
                validarDELETE($datos);
                Enfrentamiento::eliminarDeTorneo($datos->id_torneo);
                $retorno = "Enfrentamientos eliminados";
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
            ["nombre" => "id_torneo", "obligatorio" => true]
        ];
        validarParametros2($datos, $parametros);
    }
    
    function validarPUT($datos){
        $parametros = [
            ["nombre" => "id_enfrentamiento", "obligatorio" => true],
            ["nombre" => "fecha_enfrentamiento", "obligatorio" => true]
        ];
        validarParametros2($datos, $parametros);
    }

    function validarDELETE($datos){
        $parametros = [
            ["nombre" => "id_torneo", "obligatorio" => true]
        ];
        validarParametros2($datos, $parametros);
    }
?>