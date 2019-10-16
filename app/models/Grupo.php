 <?php

use Phalcon\Mvc\Model;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;
use Phalcon\Validation\Validator\Uniqueness as UniquenessValidator;
use Phalcon\Validation\Validator\Digit as DigitValidator;
use Phalcon\Mvc\Model\Query;


class Grupo extends Model
{
    public $Id_Grupo;
    public $Nombre_G;
    public $Id_Lider;
    public $Clave_Grupo;

    public function saveWithEncrypt($post, $claveEncriptacion){
    	$query = $this->modelsManager->createQuery("
			INSERT INTO grupo VALUES (
								".$post['Id_Grupo'].",
								'".$post['Nombre_G']."',
								'".$post['Id_Lider']."',
								 AES_ENCRYPT('".$post['Clave_Grupo']."','".$claveEncriptacion."'))
		");

		return $query->execute();
    }

    public function getPassDecryptById($id_grupo = null, $claveEncriptacion = null){
    	if(($id_grupo != null) && ($claveEncriptacion != null)){

    		$query = $this->modelsManager->createQuery("
				SELECT AES_DECRYPT(grupo.Clave_Grupo, '$claveEncriptacion') as Passw_Grupo
				FROM grupo
				WHERE grupo.Id_Grupo = $id_grupo
			");

			return $query->execute()[0]['Passw_Grupo'];
	    	//SELECT grupo.*, AES_DECRYPT(Clave_Grupo, 'ardogs123') as Contraseña_Grupo FROM grupo;
    	}
    	return false;
    }

    public function deleteGrupo($id_grupo = null){
    	$query = $this->modelsManager->createQuery("
			DELETE
			FROM grupo
			WHERE Id_Grupo = :id:
		");

		return $query->execute(
			[
		        'id' => $id_grupo,
		    ]
		);

    }

  public function getFechaNow(){
		$query = $this->modelsManager->createQuery("SELECT now() as fecha from Grupo LIMIT 1");
		//$result = $query->execute()[0]['fecha'];
		return $query->execute()[0]['fecha'];
	}

	public function getFechaNowDateOnly(){
		$query = $this->modelsManager->createQuery("SELECT CURDATE() as fecha from Grupo LIMIT 1");
		//$result = $query->execute()[0]['fecha'];
		return $query->execute()[0]['fecha'];
	}


}

/*
DELETE
FROM grupo
WHERE Id_Grupo = 2;

DELETE
FROM integrantegrupo
WHERE Id_Grupo = 2;
*/
