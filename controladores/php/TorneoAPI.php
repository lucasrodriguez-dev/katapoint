<?php
    require_once(dirname(dirname(__DIR__)) . '/inc/bootstrap.php');
    require_once(RUTA_RAIZ . 'modelos/Torneo.php');

    $respuesta = array();

    try {
        $error = "Sesión no válida";
        $datos = json_decode(file_get_contents("php://input"));
        $respuesta['estado'] = 'datos recibidos';
        $metodoSolicitud = $_SERVER["REQUEST_METHOD"];
        $retorno = null;
        switch(strtoupper($metodoSolicitud)) {
            case 'GET':
                $error = "No se pudo obtener a los torneos";
                $tecnicoSoftware = null;
                if(isset($_COOKIE["sesion_tecnicoSoftware"])){
                    $tecnicoSoftware = $_COOKIE["sesion_tecnicoSoftware"];
                }
                if(isset($_GET['id_torneo'])){
                    $id_torneo = $_GET['id_torneo'];
                    $retorno = Torneo::listar($id_torneo);
                }else{
                    validarTecnicoSoftware();
                    $retorno = Torneo::listarTodos($tecnicoSoftware);
                }
                break;
            case 'POST':
                $error = "No se pudo registrar al torneo";
                validarTecnicoSoftware();
                validarPOST($datos);
                if(!isset($datos->rangoEdad_torneo)){
                    $datos->rangoEdad_torneo = null;
                }
                $torneo = new Torneo($datos->cantidadInscriptos_torneo, $datos->fecha_torneo, $datos->nombre_torneo, $datos->modalidad_torneo, $datos->sexo_torneo, $datos->rangoEdad_torneo, $datos->nombreUsuario_tecnicoSoftware, $datos->cantidadJueces_torneo);
                $torneo->guardar();
                $torneo->generarGrupos();
                $retorno = "Torneo registrado";
                $respuesta['id_torneo'] = $torneo->getId();
                break;
            case 'PUT':
                $error = "No se pudo modificar al torneo";
                validarTecnicoSoftware();
                validarPUT($datos);
                if(!isset($datos->rangoEdad_torneo)){
                    $datos->rangoEdad_torneo = null;
                }
                Torneo::actualizarTorneo($datos->id_torneo, $datos->cantidadInscriptos_torneo, $datos->fecha_torneo, $datos->nombre_torneo, $datos->modalidad_torneo, $datos->sexo_torneo, $datos->rangoEdad_torneo, $datos->nombreUsuario_tecnicoSoftware, $datos->cantidadJueces_torneo);
                $retorno = "Torneo actualizado";
                break;
            case 'DELETE':
                $error = "No se pudo eliminar al torneo";
                validarTecnicoSoftware();
                validarDELETE($datos);
                Torneo::eliminarTorneo($datos->id_torneo);
                $retorno = "Torneo eliminado";
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
            ["nombre" => "nombre_torneo", "obligatorio" => true],
            ["nombre" => "cantidadInscriptos_torneo", "obligatorio" => true],
            ["nombre" => "fecha_torneo", "obligatorio" => true],
            ["nombre" => "modalidad_torneo", "obligatorio" => true],
            ["nombre" => "rangoEdad_torneo", "obligatorio" => false],
            ["nombre" => "sexo_torneo", "obligatorio" => true],
            ["nombre" => "nombreUsuario_tecnicoSoftware", "obligatorio" => true],
            ["nombre" => "cantidadJueces_torneo", "obligatorio" => true]
        ];
        validarParametros2($datos, $parametros);
    }
    
    function validarPUT($datos){
        $parametros = [
            ["nombre" => "id_torneo", "obligatorio" => true],
            ["nombre" => "nombre_torneo", "obligatorio" => true],
            ["nombre" => "cantidadInscriptos_torneo", "obligatorio" => true],
            ["nombre" => "fecha_torneo", "obligatorio" => true],
            ["nombre" => "modalidad_torneo", "obligatorio" => true],
            ["nombre" => "rangoEdad_torneo", "obligatorio" => false],
            ["nombre" => "sexo_torneo", "obligatorio" => true],
            ["nombre" => "nombreUsuario_tecnicoSoftware", "obligatorio" => true],
            ["nombre" => "cantidadJueces_torneo", "obligatorio" => true]
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