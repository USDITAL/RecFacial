<?php

namespace Clases;

class Privilegio {
    // Atributos 
    private bool $empCrear;
    private bool $empModificar;
    private bool $empBaja;
    private bool $usrCrear;
    private bool $usrModificar;
    private bool $usrBaja;
    private bool $usrGenerarPass;
    private bool $marCrearPropio;
    private bool $marConsultarPropio;
    private bool $marCrear;
    private bool $marModificar;
    private bool $marEliminar;
    private bool $marConsultar;
    private bool $marAuth;
    private bool $bioCrear;
    private bool $bioEliminar;
    private bool $rolCrear;
    private bool $rolModificar;
    private bool $rolEliminar;
    private bool $ajustesModificar;

    //Métodos getter
    public function getEmpCrear(): bool {
        return $this->empCrear;
    }
    
    public function getEmpModificar(): bool {
        return $this->empModificar;
    }

    public function getEmpBaja(): bool {
        return $this->empBaja;
    }

    public function getUsrCrear(): bool {
        return $this->usrCrear;
    }

    public function getUsrModificar(): bool {
        return $this->usrModificar;
    }

    public function getUsrBaja(): bool {
        return $this->usrBaja;
    }

    public function getUsrGenerarPass(): bool {
        return $this->usrGenerarPass;
    }

    public function getMarCrearPropio(): bool {
        return $this->marCrearPropio;
    }

    public function getMarConsultarPropio(): bool {
        return $this->marConsultarPropio;
    }

    public function getMarCrear(): bool {
        return $this->marCrear;
    }

    public function getMarModificar(): bool {
        return $this->marModificar;
    }

    public function getMarEliminar(): bool {
        return $this->marEliminar;
    }

    public function getMarConsultar(): bool {
        return $this->marConsultar;
    }

    public function getMarAuth(): bool {
        return $this->marAuth;
    }

    public function getBioCrear(): bool {
        return $this->bioCrear;
    }

    public function getBioEliminar(): bool {
        return $this->bioEliminar;
    }

    public function getRolCrear(): bool {
        return $this->rolCrear;
    }

    public function getRolModificar(): bool {
        return $this->rolModificar;
    }

    public function getRolEliminar(): bool {
        return $this->rolEliminar;
    }

    public function getAjustesModificar(): bool {
        return $this->ajustesModificar;
    }

    //Métodos setter
    public function setEmpCrear(bool $empCrear): void {
        $this->empCrear = $empCrear;
    }

    public function setEmpModificar(bool $empModificar): void {
        $this->empModificar = $empModificar;
    }

    public function setEmpBaja(bool $empBaja): void {
        $this->empBaja = $empBaja;
    }

    public function setUsrCrear(bool $usrCrear): void {
        $this->usrCrear = $usrCrear;
    }

    public function setUsrModificar(bool $usrModificar): void {
        $this->usrModificar = $usrModificar;
    }

    public function setUsrBaja(bool $usrBaja): void {
        $this->usrBaja = $usrBaja;
    }

    public function setUsrGenerarPass(bool $usrGenerarPass): void {
        $this->usrGenerarPass = $usrGenerarPass;
    }

    public function setMarCrearPropio(bool $marCrearPropio): void {
        $this->marCrearPropio = $marCrearPropio;
    }

    public function setMarConsultarPropio(bool $marConsultarPropio): void {
        $this->marConsultarPropio = $marConsultarPropio;
    }

    public function setMarCrear(bool $marCrear): void {
        $this->marCrear = $marCrear;
    }

    public function setMarModificar(bool $marModificar): void {
        $this->marModificar = $marModificar;
    }

    public function setMarEliminar(bool $marEliminar): void {
        $this->marEliminar = $marEliminar;
    }

    public function setMarConsultar(bool $marConsultar): void {
        $this->marConsultar = $marConsultar;
    }

    public function setMarAuth(bool $marAuth): void {
        $this->marAuth = $marAuth;
    }

    public function setBioCrear(bool $bioCrear): void {
        $this->bioCrear = $bioCrear;
    }

    public function setBioEliminar(bool $bioEliminar): void {
        $this->bioEliminar = $bioEliminar;
    }

    public function setRolCrear(bool $rolCrear): void {
        $this->rolCrear = $rolCrear;
    }

    public function setRolModificar(bool $rolModificar): void {
        $this->rolModificar = $rolModificar;
    }

    public function setRolEliminar(bool $rolEliminar): void {
        $this->rolEliminar = $rolEliminar;
    }

    public function setAjustesModificar(bool $ajustesModificar): void {
        $this->ajustesModificar = $ajustesModificar;
    }

    //Método constructor
    public function __construct()
    {
        $this->empCrear = false;
        $this->empModificar = false;
        $this->empBaja = false;
        $this->usrCrear = false;
        $this->usrModificar = false;
        $this->usrBaja = false;
        $this->usrGenerarPass = false;
        $this->marCrearPropio = false;
        $this->marConsultarPropio = false;
        $this->marCrear = false;
        $this->marModificar = false;
        $this->marEliminar = false;
        $this->marConsultar = false;
        $this->marAuth = false;
        $this->bioCrear = false;
        $this->bioEliminar = false;
        $this->rolCrear = false;
        $this->rolModificar = false;
        $this->rolEliminar = false;
        $this->ajustesModificar = false;
    }

    // Método para establecer los privilegios desde un array asociativo
    public function setPrivilegios(array $privilegios): void {
        foreach ($privilegios as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    // Método para obtener los privilegios como un array asociativo
    public function getPrivilegios(): array {
        return [
            'empCrear' => $this->empCrear,
            'empModificar' => $this->empModificar,
            'empBaja' => $this->empBaja,
            'usrCrear' => $this->usrCrear,
            'usrModificar' => $this->usrModificar,
            'usrBaja' => $this->usrBaja,
            'usrGenerarPass' => $this->usrGenerarPass,
            'marCrearPropio' => $this->marCrearPropio,
            'marConsultarPropio' => $this->marConsultarPropio,
            'marCrear' => $this->marCrear,
            'marModificar' => $this->marModificar,
            'marEliminar' => $this->marEliminar,
            'marConsultar' => $this->marConsultar,
            'marAuth' => $this->marAuth,
            'bioCrear' => $this->bioCrear,
            'bioEliminar' => $this->bioEliminar,
            'rolCrear' => $this->rolCrear,
            'rolModificar' => $this->rolModificar,
            'rolEliminar' => $this->rolEliminar,
            'ajustesModificar' => $this->ajustesModificar,
        ];
    }

    // Método destructor
    public function __destruct()
    {
        unset($this->empCrear);
        unset($this->empModificar);
        unset($this->empBaja);
        unset($this->usrCrear);
        unset($this->usrModificar);
        unset($this->usrBaja);
        unset($this->usrGenerarPass);
        unset($this->marCrearPropio);
        unset($this->marConsultarPropio);
        unset($this->marCrear);
        unset($this->marModificar);
        unset($this->marEliminar);
        unset($this->marConsultar);
        unset($this->marAuth);
        unset($this->bioCrear);
        unset($this->bioEliminar);
        unset($this->rolCrear);
        unset($this->rolModificar);
        unset($this->rolEliminar);
        unset($this->ajustesModificar);
    }

}