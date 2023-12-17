import { validar, mostrarMensaje } from '../js/TheController.js';

const urlAPI = "../controladores/php/TecnicoSoftwareAPI.php";

$(document).ready(function () {
    $("#btn_ingresar").on("click", function () {
        try {
            let nombreUsuario = $("#nombreUsuarioTecnicoSoftware").val();
            let clave = $("#claveTecnicoSoftware").val();
            validar('Nombre de usuario', nombreUsuario);
            validar('Clave', clave);
            let url = `${urlAPI}?nombreUsuario_tecnicoSoftware=${nombreUsuario}&clave_tecnicoSoftware=${clave}`;
            fetch(url, {
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
                        mostrarMensaje(datos.error, 'rojo');
                    }else{
                        mostrarMensaje(datos.retorno, 'verde');
                        document.cookie = "sesion_tecnicoSoftware=" + datos.sesion + "; path=/";
                        setTimeout(function () {
                            window.location.href = "torneos.html";
                        }, 900);
                    }
                })
                .catch(error => console.error(error));
        } catch (e) {
            mostrarMensaje(e, 'amarillo');
        }
    });
    $("#btn_registrar").on("click", function () {
        try {
            let nombreUsuario = $("#nombreUsuarioTecnicoSoftware").val();
            let clave = $("#claveTecnicoSoftware").val();
            validar('Nombre de usuario', nombreUsuario);
            validar('Clave', clave);
            let tecnicoSoftware = {
                nombreUsuario_tecnicoSoftware: nombreUsuario,
                clave_tecnicoSoftware: clave
            }
            fetch(urlAPI, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(tecnicoSoftware)
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
                        mostrarMensaje(datos.error, 'rojo');
                    }else{
                        mostrarMensaje(datos.retorno, 'verde');
                    }
                })
                .catch(error => console.error(error));
        } catch (e) {
            mostrarMensaje(e, 'amarillo');
        }
    });
});
