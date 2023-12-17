import { validarSesion, mostrarMensaje, obtenerTorneo, obtenerDeUrl, gohome } from './TheController.js';

const urlAPI = "../controladores/php/GrupoAPI.php";

const id_torneo = obtenerDeUrl('id_torneo');

$(document).ready(async function () {
    try {
        validarSesion('tecnicoSoftware');
        $("#btn_ingresar").on("click", function () {
            let objeto = {
                id_torneo: id_torneo
            }
            fetch(urlAPI, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(objeto)
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

                    }
                })
                .catch(error => console.error(error));
        });
        await construirTabla();
    } catch (e) {
        console.error(e);
        if(e === "No se inició sesión"){
            gohome();
        }
    }
});

async function construirTabla() {
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
                armarTabla(datos);
            }
        })
        .catch(error => console.error(error));
}

async function armarTabla(datos) {
    let torneo = await obtenerTorneo(id_torneo);
    let grupos = datos.retorno;

    //por cada grupo
    let contenedorGrupos = $("#grupos_container");
    for (let i = 0; i < grupos.length; i++) {
        let tablaGrupo = $("<table class='tabla'>");
        let encabezado = $(`<thead><th>Grupo ${grupos[i].id_grupo}</th></thead>`);
        let cuerpo = $("<tbody>");
        
        //por cada equipo
        for (let y = 0; y < grupos[i].equipos.length; y++) {
            let nombreEquipo = "";
            if (torneo.modalidad_torneo == 'individual') {
                nombreEquipo = grupos[i].equipos[y].competidores[0].nombreCompleto_competidor;
            } else {
                nombreEquipo = grupos[i].equipos[y].nombre_equipo;
            }
            cuerpo.append(`<tr><td>${nombreEquipo}</td><tr>`);
        }
        cuerpo.append("</tbody>");
        tablaGrupo.append(encabezado);
        tablaGrupo.append(cuerpo);
        contenedorGrupos.append(tablaGrupo);
    }
}