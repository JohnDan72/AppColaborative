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

    public function getGruposOfComplemento($id_integrante){

    	//se consultan primero todos los id de grupo al que pertenece
    	$query = $this->modelsManager->createQuery('
			SELECT 	grupo.Id_Grupo
			FROM 	grupo, integrantegrupo 
			WHERE 	grupo.Id_Grupo = integrantegrupo.Id_Grupo AND
			        integrantegrupo.Id_Integrante = :id:
			ORDER by grupo.Id_Grupo, grupo.Nombre_G
		');

		$gruposOfIntegrant = $query->execute(
		    [
		        'id' => $id_integrante,
		    ]
		);

		if(isset($gruposOfIntegrant[0])){
			$subcadena = ""; $ind = 1;
			//se concatenan las condiciones para poder recuperar los grupos a los que no pertenece
			foreach ($gruposOfIntegrant as $grupo) {
				if ($ind == count($gruposOfIntegrant)) {
					$subcadena.= "grupo.Id_Grupo <> ".$grupo->Id_Grupo." ";
				}
				else{
					$subcadena.= "grupo.Id_Grupo <> ".$grupo->Id_Grupo." AND ";
				}
				$ind++;
			}
			//query para obtener los grupos a los que puede Unirse
			$query2 = $this->modelsManager->createQuery('
				SELECT 	grupo.Id_Grupo, grupo.Nombre_G, grupo.Id_Lider, grupo.Clave_Grupo
				FROM 	grupo 
				WHERE '.$subcadena.' 
				ORDER by grupo.Id_Grupo, grupo.Nombre_G
			');

			$retorno = $query2->execute();
			if($retorno) return $retorno;
			else return false;
		}
		else{
			$query3 = $this->modelsManager->createQuery('
				SELECT 	grupo.Id_Grupo, grupo.Nombre_G, grupo.Id_Lider, grupo.Clave_Grupo
				FROM 	grupo 
				ORDER by grupo.Id_Grupo, grupo.Nombre_G
			');

			$retorno = $query3->execute();
			if($retorno) return $retorno;
			else return false;
		}
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