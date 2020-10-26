<?php

use Phalcon\Http\Response;
use Phalcon\Crypt;
use Phalcon\Registry;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\File as FileValidator;

/**
 * 
 */
class UserManageController extends ControllerBase
{
	public function initialize()
    {
        $this->tag->setTitle('Gestión User');
        parent::initialize();
    }


//      $this->registroGrupoActual['Id_Grupo_Actual']
    public function indexAction()
    {   
        if (!$this->session->get('user')) {
            $response = new Response();
            $response->redirect("session/index");
            $response->send();
        }

        $grupo = new Integrantegrupo();
        
        $this->view->grupos = $grupo->getGruposOf($this->session->get('user')['Matricula']);
        $this->view->gruposComp = $grupo->getGruposOfComplemento($this->session->get('user')['Matricula']);

        
    }

    public function changeInfoAction()
    {
    	if ($this->request->isPost()) {
    		$band=false;
    		$mensajesRes;
    		if ($this->request->getPost('Correo')!= $this->session->get('user')['Correo']) { //Correo Distinto
    			$updateEmail = Usuario::findFirst("Matricula = ".$this->session->get('user')['Matricula']);
    			$updateEmail->Correo = $this->request->getPost('Correo');
    			if($updateEmail->update()){
    				$this->session->remove('user');
    				$this->session->set('user', [
			            'Matricula' => $updateEmail->Matricula,
			            'Nombre' => $updateEmail->Nombre,
			            'Ap_Paterno' => $updateEmail->Ap_Paterno,
			            'Ap_Materno' => $updateEmail->Ap_Materno,
			            'Correo' => $updateEmail->Correo,
			            'Facultad' => $updateEmail->Facultad
			        ]);
                    $mensajesRes['Cambio_Correo'] = "
                                <div class=\"row center\">    
                                    <span class =\"light-green-text text-accent-3\">
                                        Correo Modificado Exitosamente!
                                    </span>
                                </div>
                                    "; 
                    $band = true;
    			}

    		}
    		

            if ($this->request->hasFiles()) {

            		//echo "var_dump(): ".var_dump($_FILES)."<br><br>";
                    $validation = new Validation();
                    $validation->add(
                        "Nombre_Img",
                        new FileValidator(
                            [
                                "maxSize"              => "50M",
                                "messageSize"          => "El archivo excede el tamaño máximo (:max)",
                                "allowedTypes"         => [
                                    "image/jpeg",
                                    "image/png",
                                ],
                                "messageType"          => "Solo se permiten imagenes en formato :types",
                                "maxResolution"        => "3500x3200",
                                "messageMaxResolution" => "La imagen excede la resolucion máxima :max",
                            ]
                        )
                    );
                    //se hace la validación
                    $messages = $validation->validate($_FILES);


                    if (count($messages)) {
                        //Se guardan los mensajes de error de imagen
                        $ind=0;
                        foreach ($messages as $message) {
                            $dataAux[$ind++]=$message->getMessage();
                            //echo "Mensaje: ".$message->getMessage();
                        }
                        //$band++;
                        //echo $dataAux;
                    }
                    else{
                    	$image = $_FILES['Nombre_Img']['tmp_name'];
        				$imgContenido = addslashes(file_get_contents($image));

                        //$files = $this->request->getUploadedFiles();
                        $userModel = new Usuario();

                        if (($band = $userModel->updateFoto($this->session->get('user')['Matricula'],$imgContenido))) {
                        	//echo "Imagen actualizada con Éxito";
                        	$mensajesRes['Cambio_Foto'] = "
                                    <div class=\"row center\">
                                        <span class =\"light-green-text text-accent-3\">
                                            Foto Modificada exitósamente!!
                                        </span>
                                    </div>";

                            $band = true;
                            $dataU = Usuario::findFirst("Matricula = ".$this->session->get('user')['Matricula']);

                            $this->session->remove('userFoto');
                            $this->session->set('userFoto',$dataU->Foto);
                        	$this->view->Result_UserManage = $mensajesRes;
                        }
                        else{
                        	echo "Error al subir imagen<br><br>";
                        }

                        
                    }

            }
            else{
                echo "Error, No hay archivos";
            }
            //echo "Band: $band";
            if ($band == false) { //Sin cambios
            	//echo "Entro a band = false";
            	$mensajesRes['Sin_Cambio'] = "
                            <div class=\"row center\">
                                <span class =\"orange-text text-darken-2\">
                                    SIN CAMBIOS
                                </span>
                            </div>";
            }

            $this->view->Result_UserManage = $mensajesRes;
            return $this->dispatcher->forward(
		                [
		                    "controller"    => "usermanage",
		                    "action"        => "index"
		                ]
		            );
        }
        else{
        	echo "No es _POST";
        }
    }

    
	
}

?>