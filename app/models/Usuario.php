<?php 

use Phalcon\Mvc\Model;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;
use Phalcon\Validation\Validator\Uniqueness as UniquenessValidator;
use Phalcon\Validation\Validator\Digit as DigitValidator;


class Usuario extends Model
{
    public $Matricula;
    public $Nombre;
    public $Ap_Paterno;
    public $Ap_Materno;
    public $Correo;
    public $Facultad;

    public function validation()
    {
        $validator = new Validation();
        
        $validator->add(
            'Matricula',
            new DigitValidator(
                [
                    'message' => 'La matrícula debe ser un número'
                ]
            )
        );

        $validator->add(
            'Matricula',
            new PresenceOf(
                [
                    'message' => 'Campo \'Matricula\' requerido'
                ]
            )
        );
        
        return $this->validate($validator);
    }

    /*
    if ($this->validationHasFailed() === true) {
            return false;
        }
    */
}
