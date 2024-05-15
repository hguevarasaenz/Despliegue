require('colors');
const inquirer = require('inquirer');
const Menu = require('../models/menu');

const menu = new Menu();
// cambiar preguntas a una funcion para que logre retornar el array
const preguntas = async (rol='0') => {
    const choices = await menu.getMenuPorRol(rol);
    return [
        {
            type: 'list',
            name:'opcion',
            message:'¿Qué desea hacer?',
            // dentro de choices mandará a llamar una  funcion que te traiga los valores
            choices: choices
        }
    ];
} 


const inquirerMenu = async(rol) =>{

    console.clear();
    console.log('======================='.green);
    console.log('  Seleccione una opción'.white);
    console.log('=======================\n'.green);

    console.log();
    const preguntasAsync = await preguntas(rol);
    const { opcion } = await inquirer.prompt(preguntasAsync);
    return opcion;
}


const pausa = async() => {

    const question = [
        {
            type:'input',
            name:'enter',
            message:`Presione ${'ENTER'.green} para continuar`
        }
    ]
    
    await inquirer.prompt(question);

};

const leerInput = async(message, type) => {

    const question = [
        {
            type:type,
            name:'desc',
            message:message,
            validate( value ){
                if(value.length === 0){
                    return `Por favor ingrese un valor`
                }
                return true;
            }
        }
    ];

    const {desc} = await inquirer.prompt(question);
    
    return desc;



}

module.exports = {
    inquirerMenu,
    pausa,
    leerInput
}

