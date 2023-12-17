import { validarSesion, validar, mostrarMensaje, obtenerDeUrl, obtenerTorneo, gohome, mostrarPantallaCarga, ocultarPantallaCarga, redirect } from './TheController.js';

const urlAPI = "../controladores/php/TorneoAPI.php";
const id_torneo = obtenerDeUrl("id_torneo");

$(document).ready(async function () {
    mostrarPantallaCarga();
    try {
        validarSesion('tecnicoSoftware');
        const torneo = await obtenerTorneo(id_torneo);
        if (torneo.modalidad_torneo == "individual") {
            ocultarBotonEquipos();
        }
        let url = `${urlAPI}?id_torneo=${id_torneo}`;
        await fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        })
            .then(function (respuesta) {
                if (!respuesta.ok) {
                    throw new Error("Error: " + respuesta.status);
                }
                return respuesta.json();
            })
            .then(function (datos) {
                console.log(datos);
                if (datos.error) {
                    let error = datos.error;
                    mostrarMensaje(error, 'rojo');
                } else {
                    agregarIdTorneoAFormularios(datos.retorno.id_torneo);
                    cambiarTitulo(`${datos.retorno.nombre_torneo} - Torneo ${datos.retorno.id_torneo}`);
                    ocultarPantallaCarga();
                }
            })
            .catch(error => console.error(error));
        $(".btn_mostrar").on("click", function() {
            let idBoton = $(this).attr("id");
            redirect(`${idBoton}.html?id_torneo=${id_torneo}`);
        });
    } catch (e) {
        console.error(e);
        if(e === "No se inició sesión"){
            gohome();
        }
    }

});

function cambiarTitulo(titulo) {
    document.title = "KATA POINT - " + titulo;
    $("#subtitulo").html(titulo);
}

function agregarIdTorneoAFormularios(id_torneo) {
    //por cada formulario
    $('form').each(function () {
        //$(this) = 'form' actual
        let action = $(this).attr('action');
        //si ya tiene parametros
        if (action.includes('?')) {
            //concatenamos el nuevo parametro con '&'
            action += '&id_torneo=' + id_torneo;
        } else {
            //concatenamos el nuevo parametro con '?'
            action += '?id_torneo=' + id_torneo;
        }
        $(this).attr('action', action);
    });
}

function ocultarBotonEquipos(){
    $("#form_equipos").css("display", "none");
}