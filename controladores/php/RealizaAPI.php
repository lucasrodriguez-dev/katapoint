<?php
    require_once(dirname(dirname(__DIR__)) . '/inc/bootstrap.php');
    require_once(RUTA_RAIZ . 'modelos/Enfrentamiento.php');
    require_once(RUTA_RAIZ . 'modelos/Equipo.php');
    require_once(RUTA_RAIZ . 'modelos/Kata.php');

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
                $error = "No se pudo obtener las realizaciones de kata de los equipos del enfrentamiento";
                if(isset($_GET['id_enfrentamiento'])){
                    $id_enfrentamiento = $_GET['id_enfrentamiento'];
                    if(isset($_GET['nombre_equipo'])){
                        $nombre_equipo = $_GET['nombre_equipo'];
                        $retorno = Equipo::listarKataRealizado($nombre_equipo, $id_enfrentamiento);
                    }else{
                        $retorno = Enfrentamiento::listarKatasRealizados($id_enfrentamiento);
                    }
                }
                break;
            case 'POST':
                $error = "“No se pudo registrar la realización del kata”";
                validarPOST($datos);
                $equipo = Equipo::getByNombre($datos->nombre_equipo);
                if($equipo === null){
                    throw new Exception("Equipo no existente");
                }
                $equipo->realizarKata($datos->id_kata, $datos->id_enfrentamiento, $datos->fecha_ejecucionKata);
                $retorno = "Realización de kata registrada";
                break;
            case 'PUT':
                $error = "No se pudo modificar la realización del kata";
                validarPUT($datos);
                $equipo = Equipo::getByNombre($datos->nombre_equipo);
                if($equipo === null){
                    throw new Exception("Equipo no existente");
                }
                $equipo->actualizarKata($datos->id_kata, $datos->id_enfrentamiento, $datos->fecha_ejecucionKata);
                $retorno = "Realización de kata actualizada";
                break;
            case 'DELETE':
                $error = "No se pudo eliminar la realización del kata";
                validarDELETE($datos);
                $equipo = Equipo::getByNombre($datos->nombre_equipo);
                if($equipo === null){
                    throw new Exception("Equipo no existente");
                }
                $equipo->eliminarKata($datos->id_enfrentamiento);
                $retorno = "Realización de kata eliminada";
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
            ["nombre" => "id_enfrentamiento", "obligatorio" => true],
            ["nombre" => "nombre_equipo", "obligatorio" => true],
            ["nombre" => "id_kata", "obligatorio" => true],
            ["nombre" => "fecha_ejecucionKata", "obligatorio" => true]
        ];
        validarParametros2($datos, $parametros);
    }
    
    function validarPUT($datos){
        $parametros = [
            ["nombre" => "id_enfrentamiento", "obligatorio" => true],
            ["nombre" => "nombre_equipo", "obligatorio" => true],
            ["nombre" => "id_kata", "obligatorio" => true],
            ["nombre" => "fecha_ejecucionKata", "obligatorio" => true]
        ];
        validarParametros2($datos, $parametros);
    }

    function validarDELETE($datos){
        $parametros = [
            ["nombre" => "id_enfrentamiento", "obligatorio" => true],
            ["nombre" => "nombre_equipo", "obligatorio" => true]
        ];
        validarParametros2($datos, $parametros);
    }
?>