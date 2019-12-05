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
}

/*
SELECT historialg.Tipo_H,historialg.Id_User,historialg.Id_Grupo,historialg.Fecha_Hora,usuario.Nombre 
FROM historialg,usuario 
WHERE historialg.Id_User=usuario.Matricula AND historialg.Id_Grupo=13;
*/

?>