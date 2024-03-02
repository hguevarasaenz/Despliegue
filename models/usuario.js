const { v4:uuidv4 } = require('uuid');
const md5 = require('md5');


class Usuario{
    id = uuidv4();
    NombreCompletoUsuario = '';
    correo = '';
    rol = '';
    contrasenia = '';

    constructor(nombreCompleto, correo, contrasenia, rol){
        this.id = uuidv4();
        this.NombreCompletoUsuario = nombreCompleto;
        this.correo = correo;
        this.contrasenia = md5(contrasenia);
        this.rol = rol;
    }

}


module.exports = Usuario;