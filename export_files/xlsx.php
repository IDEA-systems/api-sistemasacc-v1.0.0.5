<?php

    session_start();
    $clientes = $_SESSION["CLIENTS_EXPORTS"];
    header("Pragma: public");
    header("Expires: 0");
    $filename = "clientes.xls";
    header("Content-type: application/x-msdownload");
    header("Content-Disposition: attachment; filename=$filename");
    header("Pragma: no-cache");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

    /************************************
     * FUNCION QUE REMUEVE LOS ACENTOS  *
    *************************************/
    function eliminar_acentos($cadena){
        //Reemplazamos la A y a
        $cadena = str_replace(
        array('Á', 'À', 'Â', 'Ä', 'á', 'à', 'ä', 'â', 'ª'),
        array('A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a'),
        $cadena
        );

        //Reemplazamos la E y e
        $cadena = str_replace(
        array('É', 'È', 'Ê', 'Ë', 'é', 'è', 'ë', 'ê'),
        array('E', 'E', 'E', 'E', 'e', 'e', 'e', 'e'),
        $cadena );

        //Reemplazamos la I y i
        $cadena = str_replace(
        array('Í', 'Ì', 'Ï', 'Î', 'í', 'ì', 'ï', 'î'),
        array('I', 'I', 'I', 'I', 'i', 'i', 'i', 'i'),
        $cadena );

        //Reemplazamos la O y o
        $cadena = str_replace(
        array('Ó', 'Ò', 'Ö', 'Ô', 'ó', 'ò', 'ö', 'ô'),
        array('O', 'O', 'O', 'O', 'o', 'o', 'o', 'o'),
        $cadena );

        //Reemplazamos la U y u
        $cadena = str_replace(
        array('Ú', 'Ù', 'Û', 'Ü', 'ú', 'ù', 'ü', 'û'),
        array('U', 'U', 'U', 'U', 'u', 'u', 'u', 'u'),
        $cadena );

        //Reemplazamos la N, n, C y c
        $cadena = str_replace(
        array('Ñ', 'ñ', 'Ç', 'ç'),
        array('N', 'n', 'C', 'c'),
        $cadena
        );

        return $cadena;
    }

?>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>NOMBRES</th>
            <th>EMAIL</th>
            <th>TELEFONO</th>
            <th>DOMICILIO</th>
            <th>CLIENTE_IP</th>
            <th>MAC</th>
            <th>MENSUALIDAD</th>
            <th>PAQUETE</th>
            <th>DIA_CORTE</th>
            <th>COLONIA</th>
            <th>PAQUETE</th>
        </tr>
    </thead>
    <tbody>
        <?php
            foreach($clientes as $cliente) {
                echo 
                "<tr>
                    <td>". $cliente['cliente_id'] ."</td>
                    <td>". eliminar_acentos($cliente['nombres']) ."</td>
                    <td>". $cliente['cliente_email'] ."</td>
                    <td>". $cliente['cliente_telefono'] ."</td>
                    <td>". eliminar_acentos($cliente['cliente_domicilio']) ."</td>
                    <td>". $cliente['cliente_ip'] ."</td>
                    <td>". $cliente['cliente_mac'] ."</td>
                    <td>". $cliente['mensualidad'] ."</td>
                    <td>". $cliente['nombre_paquete'] ."</td>
                    <td>". $cliente['cliente_corte'] ."</td>
                    <td>". eliminar_acentos($cliente['nombre_colonia']) ."</td>
                    <td>". eliminar_acentos($cliente['nombre_paquete']) ."</td>
                </tr>";
            }
        ?>
    </tbody>
</table>