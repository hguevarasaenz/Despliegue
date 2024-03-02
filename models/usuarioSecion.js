const fs = require('fs');
const Usuarios = require('./usuarios');
const { leerDB: leerDBUsuarios } = require('../helpers/usuarios/guardarArchivo');
const md5 = require('md5');
require('colors');


class usuarioSesion{


    constructor(){
    }


    async autenticar(correo,contrasenia){
        let sesionBoolean = false;

        try{
            // Se declaran variables que formaran parte de la validación del login
            let contraseniaData = '';
            //Consulta la data que hay dentro de la Base de datos
            const dataUsuarios = await this.traerDataUusarios();
            //Si hay data valida existencia de usuario y autentica si es correcto
            if(dataUsuarios){
                const validaExistenciaUsuario = dataUsuarios.some(usuario => {
                    let resp = false;
                    if(usuario.correo === correo){
                        contraseniaData = usuario.contrasenia;
                        resp = true;
                    }
                    return resp;
                });
                if(validaExistenciaUsuario){
                    if(contraseniaData === md5(contrasenia)){
                        sesionBoolean= true;
                    }else{
                        throw new Error(`Contraseña incorrecta.`);
                    }
                }else{
                    throw new Error(`El usuario no existe.`);
                }
            }else{
                throw new Error(`No se encontraron datos.`);
            }
            return {
                "sesion":sesionBoolean,
                "status": 200,
                "message": "Inicio de sesión exitoso"
              }
        }catch(err){
            const respErr = {
                "sesion": sesionBoolean,
                "status": 401,
                "message": err.message
            }
            //Validación con BD
            // if(err.message=== 'No se encontraron datos.'){
            //     respErr.message = 500
            // }
            return respErr;
        }

    }

    async traerDataUusarios(){
        const data = await leerDBUsuarios();
        return data;
    }

}




module.exports = usuarioSesion;

// yo:
// asi como se usa return Promise.reject para el catch, tambien se podria poner un return Promise.resolve para el try?
// chatGPT:
// Sí, puedes usar return Promise.resolve() dentro del bloque try para devolver una promesa resuelta con un valor específico. Esto puede ser útil si deseas devolver un valor específico cuando la operación en el bloque try se completa con éxito.

// Aquí tienes un ejemplo de cómo usar Promise.resolve() dentro de un bloque try: