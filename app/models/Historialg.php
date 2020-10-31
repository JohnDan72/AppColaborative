 <?php

use Phalcon\Mvc\Model;
use Phalcon\Validation;
use Phalcon\Mvc\Model\Query;


class Historialg extends Model
{
    public $Id_H;
    public $Tipo_H;
    public $Id_User;
    public $Id_Grupo;
    public $Fecha_Hora;
    public $Biblioteca;
    public $Archivo_Biblio;

  	public function getHistoryByGroup($id_grupo = null){
  		if($id_grupo == null) return false;

  		$query = $this->modelsManager->createQuery('
  				SELECT historialg.Tipo_H,historialg.Id_User,historialg.Id_Grupo,historialg.Fecha_Hora,historialg.Biblioteca,historialg.Archivo_Biblio,usuario.Nombre 
				FROM historialg,usuario 
				WHERE historialg.Id_User = usuario.Matricula AND historialg.Id_Grupo = :id:
        ORDER BY historialg.Fecha_Hora
  			');
  		$success = $query->execute([
  			'id' => $id_grupo
  		]);

  		if(isset($success[0])){
  			return $success;
  		}
  		else{
  			return false;
  		}
  	}

    //get user history info across id group and id user
    public function getHistoryOfUser($id_grupo=null,$id_user=null){
      if ($id_grupo == null || $id_user == null) 
        return false;

      $query = $this->modelsManager->createQuery(
                  "
                    SELECT historialg.Tipo_H,historialg.Id_User,historialg.Id_Grupo,historialg.Fecha_Hora,historialg.Biblioteca,historialg.Archivo_Biblio,usuario.Nombre
                    FROM historialg
                    LEFT JOIN usuario on usuario.Matricula = historialg.Id_User
                    WHERE historialg.Id_Grupo = :id_grupo: AND historialg.Id_User = :id_user:
                    ORDER BY historialg.Fecha_Hora DESC
                  "
                );
      $success = $query->execute([
          'id_grupo' => $id_grupo,
          'id_user'  => $id_user
      ]);

      if(isset($success[0])){
        return $success;
      }
      else{
        return false;
      }
    }

    //get history of each users of an especific group
    public function getHistoryIndividualUsers($id_grupo=null){
      if ($id_grupo == null) 
        return false;
      //query para obtener todos los integrantes/exintegrantes del grupo
      $integrantes = $this->modelsManager->createQuery(
                  "
                    SELECT DISTINCT historialg.Id_User 
                    FROM historialg
                    WHERE historialg.Id_Grupo = :id_grupo:
                    ORDER BY historialg.Id_User
                  "
                );
      $integrantes = $integrantes->execute(['id_grupo' => $id_grupo]);

      if(isset($integrantes[0])){
        $historiesIndividuals = [];
        foreach ($integrantes as $persona) {
          $auxHistory = $this->getHistoryOfUser($id_grupo,$persona['Id_User']);

          if ($auxHistory) {
            array_push($historiesIndividuals, $auxHistory);
          }
          //else return false;
        }
        return $historiesIndividuals;
      }
      else{
        return false;
      }

    }  
}

/*
SELECT historialg.Tipo_H,historialg.Id_User,historialg.Id_Grupo,historialg.Fecha_Hora,usuario.Nombre 
FROM historialg,usuario 
WHERE historialg.Id_User=usuario.Matricula AND historialg.Id_Grupo=13;
*/

?>