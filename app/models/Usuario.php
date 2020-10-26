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
    public $Foto;

    

    public function updateFoto($id_user,$fotoData){
        $conexion = mysqli_connect("localhost","root","");
        mysqli_select_db( $conexion, "appcolaborativo" ) or die ( "Upps! Pues va a ser que no se ha podido conectar a la base de datos" );
        mysqli_set_charset($conexion, "utf8");  //Establecer recuperacion de info en utf8 para acentos y tildes
        
        $sql = "
            UPDATE usuario 
            SET Foto = '".$fotoData."' 
            WHERE usuario.Matricula = $id_user
        ";
        $result = mysqli_query($conexion,$sql);

        mysqli_close($conexion);

        if ($result) {
            //echo "var_dump(expression) :".var_dump($result);
            return true;
        }
        else{
            //echo "var_dump(expression) :".var_dump($result);
            return false;
        }
        
    }
    public function getImage($id_user){
        $conexion = mysqli_connect("localhost","root","");
        mysqli_select_db( $conexion, "appcolaborativo" ) or die ( "Upps! Pues va a ser que no se ha podido conectar a la base de datos" );
        mysqli_set_charset($conexion, "utf8");  //Establecer recuperacion de info en utf8 para acentos y tildes
        
        $sql = "
            SELECT usuario.Foto
            FROM usuario
            WHERE usuario.Matricula = $id_user
        ";
        $result = mysqli_query($conexion,$sql);
        $data = $result->fetch_all(MYSQLI_ASSOC);

        mysqli_close($conexion);
        echo "Result: ".var_dump($data);
        return $data[0];
    }

    /*

    $query = $this->modelsManager->createQuery("
            UPDATE usuario 
            SET Foto = ".$fotoData." 
            WHERE usuario.Matricula = $id_user
        ");
    return $query->execute();


    if ($this->validationHasFailed() === true) {
            return false;
        }
    */
}
