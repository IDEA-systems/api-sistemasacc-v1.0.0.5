<?php

class Cuts 
{

    public $corte_id;
    public $dia_pago;
    public $dia_comienzo;
    public $dia_terminacion;
    public $string;
    public $comentarios;

    public $error;
    public $conflict;
    public $error_message;


    public function __construct($request = [])
    {
        $this->corte_id = isset($request->corte_id) ? $request->corte_id : null;
        $this->dia_pago = isset($request->corte_id) ? $request->corte_id : null;
        $this->dia_comienzo = isset($request->dia_comienzo) ? $request->dia_comienzo : null;
        $this->dia_terminacion = isset($request->dia_terminacion) ? $request->dia_terminacion : null;
        $this->comentarios = isset($request->comentarios) ? $request->comentarios : null;
        $this->string = isset($request->string) ? $request->string : "";
    }

    
    /**
     * update
     *
     * @return void
     */
    public function update() : void
    {
        $this->update_cortes_servicio();
        // $is_conflict_comienzo = $this->dia_pago <= 5 && 
        //     $this->dia_pago < $this->dia_comienzo || 
        //     $this->dia_comienzo == 0;

        // $is_conflict_terminacion = $this->dia_pago > 25 && 
        //     $this->dia_pago <= 30 && 
        //     $this->dia_pago > $this->dia_terminacion || 
        //     $this->dia_comienzo > 30;

        // if ($is_conflict_comienzo) {
        //     $this->conflict = true;
        //     $this->error_message = "EL dia de recordatorio no debe ser despues del día de pago!";
        // }
        
        // if ($is_conflict_terminacion) {
        //     $this->conflict = true;
        //     $this->error_message = "EL dia de suspencion no debe ser antes del día de pago!";
        // }

        // if (!$is_conflict_comienzo && !$is_conflict_terminacion) {
        //     $this->update_cortes_servicio();
        // }
    }


    public function update_cortes_servicio()
    {
        try {
            $query = Flight::gnconn()->prepare("
                UPDATE cortes_servicio 
                SET dia_comienzo = ?,
                dia_terminacion = ?,
                string = ?,
                comentarios = ?
                WHERE corte_id = ?
            ");
            $query->execute([
                $this->dia_comienzo,
                $this->dia_terminacion,
                $this->string,
                $this->comentarios,
                $this->corte_id
            ]);
        } catch (Exception $error) {
            $this->error = true;
            $this->error_message = $error->getMessage();//"Error al actualizar el corte!";
        }
    }

    public function get_cut_by_id($corte_id) 
    {
        $query = Flight::gnconn()->prepare("
            SELECT * FROM cortes_servicio 
            WHERE corte_id = ?
        ");
        $query->execute([ $this->corte_id ]);
        $rows = $query->fetchAll();
        return $rows;
    }

}