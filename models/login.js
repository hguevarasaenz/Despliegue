const fs = require('fs');
const Usuarios = require('./usuarios');
const { leerDB: leerDBUsuarios } = require('../helpers/usuarios/guardarArchivo');
const md5 = require('md5');
const UsuarioSesion = require('./usuarioSesion');
require('colors');


class Login{


    constructor(){
    }


    async autenticar(correo,contrasenia){
        let sesionBoolean = false;
        let user ={};

        try{
            // Se declaran variables que formaran parte de la validación del login
            let contraseniaData = '';
            //Consulta la data que hay dentro de la Base de datos
            const dataUsuarios = await this.traerDataUsarios();
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
                    const isMatch = this.comparePassword(contraseniaData,contrasenia);
                    if(isMatch){
                        const usuario = dataUsuarios.filter(usuario => usuario.correo === correo);
                        const { id, NombreCompletoUsuario, correo:correoUsuario, rol } = usuario[0];
                        // console.log(usuario[0]);
                        const usuarioSesion = new UsuarioSesion();
                        usuarioSesion.userActivo(id, NombreCompletoUsuario, correoUsuario, contrasenia, rol);
                        user = usuarioSesion.getJsonUsuario;
                        
                        // console.log(usuarioSesion);
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
                "message": "Inicio de sesión exitoso",
                "user":user
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

    async traerDataUsarios(){
        const data = await leerDBUsuarios();
        return data;
    }

    comparePassword(contraseniaData,contrasenia){
        return (contraseniaData === md5(contrasenia))? true: false; 
    }

}




module.exports = Login;

// yo:
// asi como se usa return Promise.reject para el catch, tambien se podria poner un return Promise.resolve para el try?
// chatGPT:
// Sí, puedes usar return Promise.resolve() dentro del bloque try para devolver una promesa resuelta con un valor específico. Esto puede ser útil si deseas devolver un valor específico cuando la operación en el bloque try se completa con éxito.

// Aquí tienes un ejemplo de cómo usar Promise.resolve() dentro de un bloque try: