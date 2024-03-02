<?php
//ficheros de variables y conexion
include ('../../parametros/parametros.php');
include ('../../parametros/conexion.php');

$fe_sistema=date('Y-m-d H:i:s');
$date_sistema=date('Y-m-d');
$hr_sistema=date('H:i:s');
$ip_sitio=$_SERVER['REMOTE_ADDR'];

//informacion del cliente
$nro_telf_ref=addslashes($_REQUEST['txt_nro_telf_ref']);
$dni_contacto=addslashes($_REQUEST['txt_dni_cliente']);
$dias_diff=addslashes($_REQUEST['txt_nro_dias']); // JOSEPH MAGALLANES - TRAER DIAS PARA NUEVA LOGICA AT V4.0
$nombre_contacto=TRIM(mysqli_real_escape_string($conn,(strip_tags($_REQUEST['txt_nombre_contacto'], ENT_QUOTES))));

//informacion del asesor a gestionar, logueo y solución
$nro_doc=$_REQUEST['txt_dni_asesor'];
$id_area_sub_det=$_REQUEST['txt_id_area_sub_det'];
$id_caso=addslashes($_REQUEST['txt_id_caso']);
$id_reg=$_REQUEST['txt_id_reg'];

//tipificacion de la gestión
$tipi_id_medio=$_REQUEST['cb_tipi_caso_soporte'];
$tipi_id_tipo=$_REQUEST['cb_tipo_tipi_cerrado'];
$tipi_id_motivo=$_REQUEST['cb_motivo_tipi_cerrado'];

/* print_r($tipi_id_medio);
print_r($tipi_id_tipo);
print_r($tipi_id_motivo);
die(); */
$descrip_cierre=TRIM(mysqli_real_escape_string($conn,(strip_tags($_REQUEST['txt_descripcion_caso'], ENT_QUOTES))));

//Alerta id_caso =0

if ($id_caso!=0){

//1- busca si la tipificacion enviada cierra el ticket y queda como SOLUCIONADO
$query_busca_cierre="
SELECT 
	s.id, 
	s.cierra_caso, 
	UPPER(a.desc_area_sub) as 'desc_area_sub', 
	s.area_sub_derivacion as 'area_derivacion' 
FROM 
	select_1 s LEFT JOIN
	area_sub a ON s.area_sub_derivacion=a.id_area_sub
WHERE 
	s.id='$tipi_id_medio'; ";
// echo $query_busca_cierre;
$busca_cierre=mysqli_query($conn,$query_busca_cierre);	
$rs_cierre = mysqli_fetch_assoc($busca_cierre);
if($rs_cierre['cierra_caso']==1)
{
	$estado_cierre=1;//si solucionado
	$estado_cierre2=1;
	$area_derivacion='NULL';
	$mensaje_atencion="Se procedio a cerrar el ticket";
}
else
{	
	if ($dias_diff <= 30 && (($tipi_id_medio == 33 && $tipi_id_tipo == 206 && $tipi_id_motivo=='631' ) || ($tipi_id_medio == 34 && $tipi_id_tipo == 207 && in_array($tipi_id_motivo,[624,621,619,623,620,617,618,622,627,625,626]) ))) {
		// SOLUCION PARA GARANTIZAR QUE PASE A GARANTIA
		$estado_cierre=0;//no solucionado
		$estado_cierre2='NULL';
		$area_derivacion = 23;
		$mensaje_atencion="Se procedio a derivar el ticket a GARANTÍA, pendiente ";				
	} else {
		$estado_cierre=0;//no solucionado
		$estado_cierre2='NULL';
		$area_derivacion=$rs_cierre['area_derivacion'];
		$mensaje_atencion="Se procedio a derivar el ticket a ".$rs_cierre['desc_area_sub'].", pendiente ";		
	}
}


//2-opciones subir al inicio de las querys
if($tipi_id_medio==36)
{//caso AGENDADO
	$agendado_fecha=addslashes($_REQUEST['txt_agenda_fecha']);
	$agendado_hora=addslashes($_REQUEST['txt_agenda_hora']);
	$agenda_fecha=$agendado_fecha." ".$agendado_hora.":00";
	
	$query_regcaso_agenda=" agenda_fecha_hora= '$agenda_fecha', ";
	$query_regcasodetalle_agenda=" descrip_derivacion= 'Se agenda el caso : (".$agenda_fecha.") - ".$descrip_cierre."', ";
	$query_soportegeneral_agenda=" fecha_agenda_atiende='$agenda_fecha', ";
}
elseif($tipi_id_medio==33)
{//visita tecnica interna
	$fe_visita_int=addslashes($_REQUEST['txt_fe_visita_int']);
	$hora_visita_int=addslashes($_REQUEST['txt_hora_visita_int']);
	$fe_visita_int=$fe_visita_int." ".$hora_visita_int.":00";
	$tecnicos=addslashes($_REQUEST['cb_tecnicos']);
	
	$query_regcaso_agenda=" agenda_fecha_hora= '$fe_visita_int', ";
	$query_regcasodetalle_agenda=" descrip_derivacion= 'Se agenda el caso : (".$fe_visita_int.") - ".$descrip_cierre."', tecnico_contrata= '$tecnicos', ";
	$query_soportegeneral_agenda=" fecha_agenda_atiende='$fe_visita_int', ";
}
elseif($tipi_id_medio==34)
{//visita tecnica externa
	$fe_visita_ext=addslashes($_REQUEST['txt_fe_visita_ext']);
	$hora_visita_ext=addslashes($_REQUEST['txt_hora_visita_ext']);
	$fe_visita_ext=$fe_visita_ext." ".$hora_visita_ext.":00";
	$tecnicos=addslashes($_REQUEST['cb_tecnicos']);
	
	$query_regcaso_agenda=" agenda_fecha_hora= '$fe_visita_ext', ";
	$query_regcasodetalle_agenda=" descrip_derivacion= 'Se agenda el caso : (".$fe_visita_ext.") - ".$descrip_cierre."', tecnico_contrata= '$tecnicos', ";
	$query_soportegeneral_agenda=" fecha_agenda_atiende='$fe_visita_ext', ";
}
else
{//otros
	$query_regcaso_agenda=" ";
	$query_regcasodetalle_agenda=" descrip_derivacion='$descrip_cierre', ";
	$query_soportegeneral_agenda=" ";
}

//3- actualiza la atencion del ticket
$query_update_caso="
UPDATE 
	reg_caso 
SET 
	$query_regcaso_agenda
	
	solucionado='$estado_cierre',
	fue_atendido='1',
	fe_cierre= NOW(),
	fe_reg= NOW(),
	dni_cierre= '$nro_doc'
WHERE 
	id_caso= '$id_caso';";
// echo $query_update_caso; // JOSEPH MAGALLANES --- VISITA TECNICA INTERNA
if(!mysqli_query($conn,$query_update_caso))
{
	$mensaje_atencion='';
	echo "
	<script>
		showSuccessMessageCancel('No se pudo guardar el caso','Error: update reg caso');
	</script>";
}
	
//4- actualiza la atencion del ticket tipificacion
$query_update_caso_detalle="
UPDATE 
	reg_caso_detalle 
SET 
	$query_regcasodetalle_agenda
	
	id_medio='$tipi_id_medio',
	id_tipo='$tipi_id_tipo',
	id_motivo='$tipi_id_motivo'
WHERE 
	id_caso='$id_caso';";
// echo $query_update_caso_detalle; // JOSEPH MAGALLANES --- VISITA TECNICA INTERNA
if(!mysqli_query($conn,$query_update_caso_detalle))
{$mensaje_atencion='';
	echo "
	<script>
		showSuccessMessageCancel('No se pudo guardar el caso tipificacion','Error: UP reg casotipi');
	</script>";
}


//5- crea registro de futura atencion cuando no se cierra el caso
if($estado_cierre==0)
{//cuando el caso no se cierra y se deriva
	$query_insert_caso_derivado="
	INSERT INTO reg_caso
		(id_reg, nro_ticket, fe_reg, id_area_sub, dni_asesor, 
		id_area_sub_atiende_caso, solucionado, fue_atendido, 
		estado_caso,agenda_fecha_hora,dni_cierre) 

	SELECT
		id_reg, nro_ticket,NOW(), '$area_derivacion', dni_asesor, 
		'$area_derivacion', $estado_cierre2, '$estado_cierre', 
		estado_caso,agenda_fecha_hora,'$nro_doc'
	FROM reg_caso 
	WHERE id_caso='$id_caso';";
	if(!mysqli_query($conn,$query_insert_caso_derivado))
	{
		$mensaje_atencion='';
		echo "
		<script>
			showSuccessMessageCancel('No se pudo crear su siguiente caso de cola','Error: IN reg caso');
		</script>";
	}
	$id_correlativo_caso= mysqli_insert_id($conn);
	
	$query_insert_caso_detalle_derivado="
	INSERT INTO reg_caso_detalle
		(id_reg, id_caso,
		nro_telf_ref, dni_contacto, nombre_contacto, estado_deri,tecnico_contrata)
	 
	SELECT 
		id_reg,$id_correlativo_caso,
		nro_telf_ref, dni_contacto, nombre_contacto, estado_deri,tecnico_contrata
	FROM reg_caso_detalle 
	WHERE id_caso='$id_caso';";
	if(!mysqli_query($conn,$query_insert_caso_detalle_derivado))
	{	
		$mensaje_atencion='';
		echo "
		<script>
			showSuccessMessageCancel('No se pudo crear su siguiente caso de cola','Error: IN reg caso detalle');
		</script>";
	}
	
	// JOSEPH MAGALLANES (<=30 días ---> Visita Técnica Interna ó Visita Técnica Externa van a GARANTÍA

	if ($dias_diff <= 30 && (($tipi_id_medio == 33 && $tipi_id_tipo == 206) || ($tipi_id_medio == 34 && $tipi_id_tipo == 207))) {

		//Si es AT menor a 30 dias, pasa a Bandeja Garantia
		if($id_area_sub_det == 8){
			$area_derivacion = 23;
			$mensaje_atencion="Se procedio a derivar el ticket a GARANTÍA, pendiente ";
		}

		$update_soporte_general="
		UPDATE 
			soporte_casos_general 
		SET 
			$query_soportegeneral_agenda
			
			estado_atiende=1,
			id_caso='$id_correlativo_caso',
			fecha_atiende='$fe_sistema',
			dni_asesor_atiende='$nro_doc',
			id_area_atiende='$area_derivacion',
			id_medio_atiende ='$tipi_id_medio',
			id_tipo_atiende ='$tipi_id_tipo',
			id_motivo_atiende ='$tipi_id_motivo',
			solucionado=$estado_cierre2,
			comentario_cierre='$descrip_cierre'
		WHERE 
			id_reg='$id_reg';";
		//echo "entro a logica <= 30";
		if(!mysqli_query($conn,$update_soporte_general))
		{
			$mensaje_atencion='';
			echo "
			<script>
				showSuccessMessageCancel('No se pudo guardar el caso','Error: update soporte casos');
			</script>";
		}	
	} else {
		$update_soporte_general="
		UPDATE 
			soporte_casos_general 
		SET 
			$query_soportegeneral_agenda
			
			estado_atiende=1,
			id_caso='$id_correlativo_caso',
			fecha_atiende='$fe_sistema',
			dni_asesor_atiende='$nro_doc',
			id_area_atiende='$area_derivacion',
			id_medio_atiende ='$tipi_id_medio',
			id_tipo_atiende ='$tipi_id_tipo',
			id_motivo_atiende ='$tipi_id_motivo',
			solucionado=$estado_cierre2,
			comentario_cierre='$descrip_cierre'
		WHERE 
			id_reg='$id_reg';";
		if(!mysqli_query($conn,$update_soporte_general))
		{
			$mensaje_atencion='';
			echo "
			<script>
				showSuccessMessageCancel('No se pudo guardar el caso','Error: update soporte casos');
			</script>";
		}		
	}
}
else
{//cuando se cierra finalmente
	//4- modifica el registro 
	$update_soporte_general="
	UPDATE 
		soporte_casos_general 
	SET 
		estado_atiende=1,
		fecha_atiende='$fe_sistema',
		dni_asesor_atiende='$nro_doc',
		id_area_atiende=$area_derivacion,
		id_medio_atiende ='$tipi_id_medio',
		id_tipo_atiende ='$tipi_id_tipo',
		id_motivo_atiende ='$tipi_id_motivo',
		fecha_cierre='$fe_sistema', 
		dni_asesor_cierre='$nro_doc',
		id_area_cierre='$id_area_sub_det', 
		tf1='$tipi_id_medio',
		tf2='$tipi_id_tipo',
		tf3='$tipi_id_motivo',
		solucionado=$estado_cierre2,
		comentario_cierre='$descrip_cierre'
	WHERE 
		id_reg='$id_reg';";
	if(!mysqli_query($conn,$update_soporte_general))
	{ 
		$mensaje_atencion='';
		echo "
		<script>
			showSuccessMessageCancel('No se pudo guardar el caso','Error: update soporte casos');
		</script>";
	}

	// validara los 15 minutos cuando cerramos un ticket
	caso_visita_tecnica($id_reg);

	//----------------------------------------------------
	/* $estado_programado = 2;
    $update_estado_programado="
        UPDATE
            AT_gestion_registro
        SET
            situacion='$estado_programado'
        WHERE
            id_reg='$id_reg';";
    if(!mysqli_query($conn,$update_estado_programado))
	{
		$mensaje_atencion='';
		echo "
		<script>
			showSuccessMessageCancel('No se pudo guardar el caso_1','Error: update estado programado');
		</script>";
	} */
    
}

if (in_array($area_derivacion,[10,11,64])){
//ENVIA DE BANDEJA DE CASOS A BANDEJA GAR
$query_busca_gar="
SELECT 	* FROM soporte_casos_general as s WHERE s.id_reg='$id_reg'; ";
// echo $query_busca_cierre;
$busca_gar=mysqli_query($conn,$query_busca_gar);	
$rs_gar = mysqli_fetch_assoc($busca_gar);

if($rs_gar['id_area_asignado'] == 23){
	$mensaje_atencion="Se procedio a derivar el ticket a GARANTÍA, pendiente ";
	$area_derivacion=23;
	$query_update_gar="
	UPDATE 
		soporte_casos_general 
	SET 
		id_area_atiende='$area_derivacion'
	WHERE 
		id_reg= '$id_reg';";
	// echo $query_update_caso; // JOSEPH MAGALLANES --- VISITA TECNICA INTERNA
	if(!mysqli_query($conn,$query_update_gar))
	{
		$mensaje_atencion='';
		echo "
		<script>
			showSuccessMessageCancel('No se pudo guardar el caso','Error: update reg caso');
		</script>";
	}
}
}

/* 
if($tipi_id_medio==35 || $tipi_id_medio==118){
	$estado_programado = 2;
        $update_estado_programado="
        UPDATE
            AT_gestion_registro
        SET
            situacion='$estado_programado'
        WHERE
            id_reg='$id_reg';";
    if(!mysqli_query($conn,$update_estado_programado))
		{
			$mensaje_atencion='';
			echo "
			<script>
				showSuccessMessageCancel('No se pudo guardar el caso_1','Error: update estado programado');
			</script>";
		}
} */



$query_tipi_correo=" SELECT envio_correo from AT_envio_correo where tipi_id_medio='$tipi_id_medio' and tipi_id_tipo='$tipi_id_tipo' and tipi_id_motivo='$tipi_id_motivo';";
$busca_tipi_correo=mysqli_query($conn,$query_tipi_correo);	
$rs_tipi_correo = mysqli_fetch_assoc($busca_tipi_correo);


if ($rs_tipi_correo !== null && $rs_tipi_correo['envio_correo'] == 1) {
    $valor_visita_tecnica = caso_visita_tecnica($id_reg);
    if ($valor_visita_tecnica == true) {
        envio_de_correo($id_reg);
    }
} elseif (is_null($rs_tipi_correo)) {
    caso_visita_tecnica($id_reg);
}

if($mensaje_atencion!=''){
	echo "
	<script>
		showSuccessMessage('$mensaje_atencion',' Atencion OK');
	</script>";
}


}else{
	echo "<script>
	showSuccessMessageCancel('Error en el id_caso : ','Error BD');
	</script>";
}

function caso_visita_tecnica($id_reg) {
	include ('../../parametros/parametros.php');
    include ('../../parametros/conexion.php');

    $query_estado_programacion="select agr.date_programado,ath.hora_inicio,ath.hora_fin,agr.situacion
    from AT_gestion_registro agr
    INNER JOIN AT_tramo_horario ath ON ath.id = cast(right(agr.id_visita,4) AS SIGNED INTEGER)                      
    where agr.id_reg='$id_reg'; ";
    $busca_estado_programacion=mysqli_query($conn,$query_estado_programacion);  
    $rs_estado_programacion = mysqli_fetch_assoc($busca_estado_programacion);

	if(empty($rs_estado_programacion['date_programado'])  && empty($rs_estado_programacion['hora_inicio'])){
		mysqli_close($conn);
		//echo 'entro aca';
		return false;
	}

    //horario del tramo - horario actual
    $rs_estado_programacion['hora_inicio'];
    $hora_actual = date("G:i:s");
    $estado_programado = $rs_estado_programacion['situacion'];
    $rs_concat = $rs_estado_programacion['date_programado'].' '.$rs_estado_programacion['hora_inicio'];

    //diferencia horaria
    $date_1 = new DateTime($rs_concat);
    $date_2 = new DateTime($hora_actual);
    $diff = date_diff($date_1, $date_2);

    /* $diff_dia = $diff->format('%d');
    $diff_dia_min = $diff_dia*1440;
    $diff_hor = $diff->format('%h');
    $diff_hor_min = $diff_hor*60;
    $diff_min = $diff->format('%i'); */

	$diff_dia = $diff->format('%d');
    $diff_dia_sec = $diff_dia*86400;
    $diff_hor = $diff->format('%h');
    $diff_hor_sec = $diff_hor*3600;
    $diff_min = $diff->format('%i');
	$diff_min_sec = $diff_min*60;
	$diff_sec = $diff->format('%s');


    $diff_total = $diff_dia_sec + $diff_hor_sec + $diff_min_sec + $diff_sec;
	$hoy = date("Y-m-d");
	$hora_hoy = date("H:i:s");   
	
	
	//validador de 15 minutos antes del tramo horario
    if($diff_total >= 840)
    {
        $estado_programado = 1;
        $update_estado_programado="
        UPDATE
            AT_gestion_registro
        SET
            situacion='$estado_programado'
        WHERE
            id_reg='$id_reg';";
    if(!mysqli_query($conn,$update_estado_programado))
		{
			$mensaje_atencion='';
			echo "
			<script>
				showSuccessMessageCancel('No se pudo guardar el caso_1','Error: update estado programado');
			</script>";
		}
    }

	//validador dentro del tramo horario
	if(($rs_estado_programacion['date_programado'] == $hoy) || ($rs_estado_programacion['date_programado'] < $hoy))
    {
		if(($rs_estado_programacion['hora_inicio'] <= $hora_hoy && $hora_hoy <= $rs_estado_programacion['hora_fin']) || ($hora_hoy > $rs_estado_programacion['hora_fin'])){
			$estado_programado = 1;
			$update_estado_programado="
			UPDATE
				AT_gestion_registro
			SET
				situacion='$estado_programado'
			WHERE
				id_reg='$id_reg';";
		if(!mysqli_query($conn,$update_estado_programado))
			{
				$mensaje_atencion='';
				echo "
				<script>
					showSuccessMessageCancel('No se pudo guardar el caso_1','Error: update estado programado');
				</script>";
		}
			/* print_r('***exito***');
			exit; */
		}
	}

	mysqli_close($conn);
	return true;
}


function enviarCorreo($url,$authorization,$nombre_cliente,$tipo_documento,$numero_documento,$cod_pedido,$correo,$id_reg,$num_telefono) {
	//CORREO PARA PRUEBAS
    $correo="qati@winempresas.pe";

    $data = array(
        "DERIVACION_PLANTA_E1_NOMBRE" => $nombre_cliente,
        "DERIVACION_PLANTA_E1_TIPO_DOCUMENTO"=>$tipo_documento,
        "DERIVACION_PLANTA_E1_NUMERO_DOCUMENTO"=>$numero_documento,
        "DERIVACION_PLANTA_E1_ID_PEDIDO"=>$cod_pedido,
        "DERIVACION_PLANTA_E1_EMAIL"=>$correo,
        "DERIVACION_PLANTA_E1_ID_TICKET"=>$id_reg,
        "DERIVACION_PLANTA_E1_CELULAR"=>$num_telefono   
    );

    $payload = json_encode($data);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/json",
        "Authorization: Basic " . $authorization
    ));

    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        return "Error al enviar el correo electrónico: " . $error;
    }else {
        $data = json_decode($response, true);
        $resultDescription = $data['wsResult']['resultDescription'];
    }
    curl_close($ch);
    return $resultDescription;

}


function envio_de_correo($id_reg){
	include ('../../parametros/parametros.php');
    include ('../../parametros/conexion.php');
	include ('../../parametros/config.php');

	$query_danna_correo="SELECT * FROM danna_correo WHERE alias= 'visita_tecnica' LIMIT 1";
	$resultDANA = mysqli_query($conn,$query_danna_correo);
	
	$conversation_id = 0;

	if ($resultDANA) {
		$rowdana = mysqli_fetch_assoc($resultDANA);
		$conversation_id = $rowdana["conversation_id"];
	}

	$_URL_DANNA = $GLOBALS['WS_URL_DANNA_CORREO'].$conversation_id."/start/data";
	

	$query_datos="select atg.dni_cliente, atg.telf_cliente, dat.Cod_de_pedido, dat.Nombres_y_Apellidos
	from AT_gestion_registro atg
	inner join data_clientes as dat on atg.dni_cliente=dat.Nro_Doc
	where atg.id_reg='$id_reg'; ";

	$busca_datos=mysqli_query($conn,$query_datos);	
	$rs_datos = mysqli_fetch_assoc($busca_datos);

	$dni_cliente = $rs_datos['dni_cliente'];
	$cod_pedido = $rs_datos['Cod_de_pedido'];
	$num_telefono = $rs_datos['telf_cliente'];  
	$nombre_cliente = $rs_datos['Nombres_y_Apellidos'];  
	$numero_documento = $rs_datos['dni_cliente'];

	$sql_correo= "SELECT dc.email,wp.tipo_doc from data_clientes dc INNER JOIN winpe_registro_llamada wp on wp.cod_pedido=dc.Cod_de_pedido WHERE Nro_doc2= '".$dni_cliente."' AND Cod_de_pedido='".$cod_pedido."';";
	$result = mysqli_query($conn, $sql_correo);


	if (mysqli_num_rows($result) > 0) {
		$rs_bandeja_origen = mysqli_fetch_assoc($result);
		$correo = $rs_bandeja_origen['email'];
		$tipo_documento =$rs_bandeja_origen['tipo_doc'];

		$resultado =  enviarCorreo($_URL_DANNA, $GLOBALS['authorization_DANNA_CORREO'],$nombre_cliente,$tipo_documento,$numero_documento,$cod_pedido,$correo,$id_reg,$num_telefono);

		if (strtoupper($resultado) == 'OK') {

			echo "
			<script>
				showSuccessMessage('Atencion OK',' Atencion OK');
			</script>";

		} else {
			
			echo "
			<script>
				showSuccessMessage('Error en el Envio de Correo','OK');
			</script>";
		}
	}

}

?>