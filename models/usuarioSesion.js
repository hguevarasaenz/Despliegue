const fs = require('fs');
const Usuarios = require('./usuarios');
const { leerDB: leerDBUsuarios } = require('../helpers/usuarios/guardarArchivo');
const md5 = require('md5');
require('colors');


class UsuarioSesion{
    _ususarioActivo={};
    // id='';
    // nombre='';
    // correo='';
    // _contrasenia='';
    // rol='';

    // constructor(id, nombre, correo, contrasenia, rol){
    //     this.id=id;
    //     this.nombre=nombre;
    //     this.correo=correo;
    //     this.contrasenia=contrasenia;
    //     this.rol=rol;
    // }
    constructor(){
    }

    get getJsonUsuario(){
        return this._ususarioActivo;
    }



    userActivo(id, NombreCompletoUsuario, correoUsuario, contrasenia, rol){
        this._ususarioActivo = {
            id:id,
            nombre:NombreCompletoUsuario,
            correo:correoUsuario,
            contrasenia:contrasenia,
            rol:rol
        }
    }




    

}




module.exports = UsuarioSesion;

// yo:
// asi como se usa return Promise.reject para el catch, tambien se podria poner un return Promise.resolve para el try?
// chatGPT:
// Sí, puedes usar return Promise.resolve() dentro del bloque try para devolver una promesa resuelta con un valor específico. Esto puede ser útil si deseas devolver un valor específico cuando la operación en el bloque try se completa con éxito.

// Aquí tienes un ejemplo de cómo usar Promise.resolve() dentro de un bloque try: