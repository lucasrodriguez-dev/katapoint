import { validarSesion, validar, mostrarMensaje, obtenerTorneo, obtenerDeUrl, gohome, limpiarCampos, print } from './TheController.js';

const urlAPI = "../controladores/php/EquipoAPI.php";

const id_torneo = obtenerDeUrl('id_torneo');

$(document).ready(async function () {
    try {
        validarSesion('tecnicoSoftware');
        const torneo = await obtenerTorneo(obtenerDeUrl('id_torneo'));
        print("#subtitulo", torneo.nombre_torneo);
        let rangoEdad = null;
        if (torneo.rangoEdad_torneo === null) {
            rangoEdad = "";
        } else {
            let texto = "";
            if (torneo.rangoEdad_torneo !== "mayores") {
                texto = "años";
            }
            rangoEdad = `${torneo.rangoEdad_torneo} ${texto} - `;
        }
        print("#categoria", `${rangoEdad}${torneo.sexo_torneo}`);
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
    let equipos = datos.retorno;

    let contenedor = $("#equipos_container");
    let tabla = $("<table class='tabla'>");
    let cuerpo = $("<tbody>");

    if (torneo.modalidad_torneo == 'equipo') {
        //por cada equipo
        for (let y = 0; y < equipos.length; y++) {
            let fila = $("<tr>");
            fila.append(`<td>${equipos[y].cinturon_equipo}</td>`);
            fila.append(`<td>${equipos[y].nombre_equipo}</td>`);
            cuerpo.append(fila);
        }
    } else {
        //por cada equipo
        for (let y = 0; y < equipos.length; y++) {
            let fila = $("<tr>");
            fila.append(`<td>${equipos[y].cinturon_equipo.toUpperCase()}</td>`);
            fila.append(`<td>${equipos[y].competidores[0].nombreCompleto_competidor}</td>`);
            cuerpo.append(fila);

        }
    }
    cuerpo.append("</tbody>");
    tabla.append(cuerpo);
    contenedor.append(tabla);
}
