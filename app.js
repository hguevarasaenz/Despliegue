require('dotenv').config();
require('colors');
const { guardarDB, leerDB: leerDBUsuarios } = require('./helpers/usuarios/guardarArchivo');
const { inquirerMenu, pausa , leerInput } = require('./helpers/inquirer');
const Tareas = require('./models/tareas');
const Usuarios = require('./models/usuarios');
const Login = require('./models/login');


// const { mostrarMenu, pausa } = require('./helpers/chat');

console.clear();



const main = async() => {
    // console.log('Hola mundo');
    let sesion = false;

    let opt = '';
    // const tareas = new Tareas();
    const usuarios = new Usuarios();

    const usuariosDB = await leerDBUsuarios();
    if(usuariosDB){
        // console.log('se llenara la data');
        // console.log(usuariosDB);
        usuarios.cargarUsuariosFromArray(usuariosDB);
    }

    const respCorreo = await leerInput('Ingrese su correo para loguearse: ','input');
    const respContrasenia = await leerInput('Ingrese su contrasenia para loguearse: ','password');

    const login = new Login();
    const respAuth = await login.autenticar(respCorreo,respContrasenia);
    // console.log('yujuuuu');
    // console.log(respAuth);
    // return;
    sesion = respAuth.sesion;
    
    if(!sesion){
        console.clear();
        console.log(respAuth.message);
        await pausa();
        main();
    }

    // { sesion: true, status: 200, message: 'Inicio de sesión exitoso' }
    // { sesion: false, status: 401, message: 'El usuario no existe.' }
    // return;


    while(opt !== '0' && sesion === true){
        //Imprimir el menú
        opt = await inquirerMenu(respAuth.user.rol);
        

        switch(opt){
            case '1':
                const NombreCompletoUsuario = await leerInput('Nombre Completo del usuario: ','input');
                const correo = await leerInput('Correo del usuario: ','input');
                const contrasenia = await leerInput('Contraseña del usuario: ','input');
                const rol = await leerInput('Rol del usuario: ','input');
                // tareas.crearTarea(desc);
                usuarios.crearUsuarios(NombreCompletoUsuario, correo, contrasenia, rol);
            break;

            case '2':
                console.log(usuarios.listadoArr);

            break;
            case '3':
                console.log(usuarios.listadoArr);
            break;
            case '4':
                console.log(usuarios.listadoArr);
            break;
            case '5':
                console.log(usuarios.listadoArr);
            break;
            case '6':
                console.log(usuarios.listadoArr);
            break;

            default:
            break;
    

        }
    
        guardarDB(usuarios.listadoArr);

        await pausa(); 

    }

    
}


main();