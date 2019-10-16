 <?php 

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Query;


class Integrantegrupo extends Model
{
    public $Id_Grupo;
    public $Id_Integrante;

    public function getGruposOf($id_integrante){

		$query = $this->modelsManager->createQuery('
			SELECT 	grupo.Id_Grupo, grupo.Nombre_G, grupo.Id_Lider, grupo.Clave_Grupo
			FROM 	grupo, integrantegrupo 
			WHERE 	grupo.Id_Grupo = integrantegrupo.Id_Grupo AND
			        integrantegrupo.Id_Integrante = :id:
			ORDER by grupo.Id_Grupo, grupo.Nombre_G
		');

		return $query->execute(
		    [
		        'id' => $id_integrante,
		    ]
		);
    }

    public function getMiembrosGrupoById($id_grupo){

		$query = $this->modelsManager->createQuery('
			SELECT 	usuario.Matricula, usuario.Nombre, usuario.Ap_Paterno, usuario.Correo
			FROM 	integrantegrupo, usuario, grupo
			WHERE	integrantegrupo.Id_Integrante = usuario.Matricula AND
					integrantegrupo.Id_Grupo = grupo.Id_Grupo AND
			        integrantegrupo.Id_Grupo = :idGrupo: 
			ORDER by grupo.Id_Grupo
		');

		return $query->execute(
		    [
		        'idGrupo' => $id_grupo,
		    ]
		);
    }

    public function getInfoGrupo($id_grupo, $clave){

		$query = $this->modelsManager->createQuery('
			SELECT 	grupo.Id_Grupo, grupo.Nombre_G, AES_DECRYPT(grupo.Clave_Grupo, :llave:) as Clave_Grupo, usuario.Nombre as Anfitrion_Nom, usuario.Ap_Paterno as Anfitrion_Ap
			FROM 	grupo, usuario
			WHERE 	grupo.Id_Lider = usuario.Matricula AND
			        grupo.Id_Grupo = :idGrupo:
		');

		return $query->getSingleResult(
		    [
		        'idGrupo' => $id_grupo,
		        'llave'	  => $clave
		    ]
		);
    }


    //public function insertIntegrante(){}


    public function deleteIntegrantegrupo($id_grupo = null){
    	$query = $this->modelsManager->createQuery("
			DELETE 
			FROM integrantegrupo 
			WHERE Id_Grupo = :id:
		");

		return $query->execute(
			[
		        'id' => $id_grupo,
		    ]
		);
    
    }

    
}

/*
SELECT grupo.Id_Grupo, grupo.Nombre_G, grupo.Id_Lider, grupo.Clave_Grupo, integrantegrupo.Id_Integrante, usuario.Nombre, usuario.Ap_Paterno
FROM grupo,integrantegrupo,usuario
WHERE	grupo.Id_Grupo = integrantegrupo.Id_Grupo AND
		integrantegrupo.Id_Integrante = usuario.Matricula AND
        integrantegrupo.Id_Integrante = 201504691
ORDER by grupo.Nombre_G;


SELECT 	grupo.Nombre_G, usuario.Matricula, usuario.Nombre as Integrante, usuario.Ap_Paterno, usuario.Correo
FROM 	integrantegrupo, usuario, grupo
WHERE	integrantegrupo.Id_Integrante = usuario.Matricula AND
		integrantegrupo.Id_Grupo = grupo.Id_Grupo AND
        integrantegrupo.Id_Grupo = '2' 
ORDER by grupo.Id_Grupo;

getSingleResult();


SELECT grupo.*, AES_DECRYPT(Clave_Grupo, 'ardogs123') as Contraseña_Grupo FROM grupo;
*/