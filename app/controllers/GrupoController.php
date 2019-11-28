
<?php

use Phalcon\Http\Response;
use Phalcon\Crypt;
use Phalcon\Registry;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\File as FileValidator;

class GrupoController extends ControllerBase
{

    public function initialize()
    {
        $this->tag->setTitle(' Inicio');
        parent::initialize();

        // CSS in the header
        $this->assets->addCss('css/estilos_group.css');
        //$this->assets->addJs('js/funcChat.js');

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

        //registro del grupo actualmente elegido desde el panel de control.
        if ((isset($this->Grupo_Act['id_actual']))&&($this->Grupo_Act['id_actual']> 0) && (isset($this->Tipo_Despli)) &&($this->Tipo_Despli > 0) ) {

            $this->session->set('Tipo_Despli',$this->Tipo_Despli);
            $this->session->set('Id_Grupo_Actual',$this->Grupo_Act['id_actual']);
            $this->view->miembros = $grupo->getMiembrosGrupoById($this->Grupo_Act['id_actual']);
            $this->view->infoGrupo = $grupo->getInfoGrupo(
                $this->Grupo_Act['id_actual'],
                $this->Clave_Encriptacion['key']
            );

            $this->view->infoGrupoConf = $grupo->getMiembrosGrupoById($this->Grupo_Act['id_actual']);
        }
    }

//Enviar mensaje desde caja de texto
    public function enviarMenAjaxAction(){

        $this->view->disable();

        if ($this->request->isGet()) {
            if ($this->request->getQuery('mensaje_name') != "") {



                //Preparar la información para guargar mensaje en archivo
                $mensaje = $this->request->getQuery('mensaje_name');

                //si el mensaje tiene saltos de linea, se quitan por conflicto con archivo
                $exp = explode("\n", $mensaje);
                if (count($exp)>1) {
                    $mensaje = "";
                    $mensaje = $exp[0];
                    for ($i=1; $i < count($exp); $i++) {
                         $mensaje.= " ".$exp[$i];
                     }
                }


                $matricula = $this->session->get('user')['Matricula'];
                $tipoM = 0;
                $modelGrupo = new Grupo();
                $fechaHora = $modelGrupo->getFechaNow();

                //Almacenamiento de mensaje
                $pathAux = BASE_PATH."/public/files/Chats/chat".$this->session->get('Id_Grupo_Actual')."/Mensajes.txt";

                $file = fopen($pathAux, "a");
                fwrite($file, $matricula." ".$tipoM." ".$fechaHora." ".$mensaje.PHP_EOL);
                fclose($file);
                $this->Grupo_Act['id_actual'] = $this->session->get('Id_Grupo_Actual');




                echo json_encode("Exito");
            }
            else{
                echo json_encode("Error");
            }
        }
        else{
            echo json_encode("Error");
        }
    }

    public function enviarImgAjaxAction(){
        //Funcion para enviar una imagen al grupo
        $this->view->disable();

        if ($this->request->isPost()) {

            if ($this->request->hasFiles()) {

                    $validation = new Validation();
                    $validation->add(
                        "Nombre_Img",
                        new FileValidator(
                            [
                                "maxSize"              => "1000M",
                                "messageSize"          => "El archivo excede el tamaño máximo (:max)",
                                "allowedTypes"         => [
                                    "image/jpeg",
                                    "image/png",
                                ],
                                "messageType"          => "Solo se permiten imagenes en formato :types",
                                "maxResolution"        => "1500x1200",
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
                        }

                        echo json_encode($dataAux);
                    }
                    else{

                        $files = $this->request->getUploadedFiles();

                        $pathMensaje1 = "";
                        // Se guarda las imagenes en su correspondiente carpeta
                        foreach ($files as $file) {
                            // Move the file into the application
                            $pathMensaje2 = BASE_PATH."/public/files/Chats/chat".$this->session->get('Id_Grupo_Actual')."/Imagenes/".$file->getName();
                            $pathMensaje1 = $this->url->get("public/files/Chats/chat".$this->session->get('Id_Grupo_Actual')."/Imagenes/".$file->getName()."");
                            $file->moveTo(
                                $pathMensaje2
                            );
                        }
                        //se almacena la accion en los mensajes.txt del grupo
                        $mensaje = $pathMensaje1;
                        $matricula = $this->session->get('user')['Matricula'];
                        $tipoM = 1;
                        $modelGrupo = new Grupo();
                        $fechaHora = $modelGrupo->getFechaNow();

                        //Almacenamiento de mensaje
                        $pathAux = BASE_PATH."/public/files/Chats/chat".$this->session->get('Id_Grupo_Actual')."/Mensajes.txt";

                        $file = fopen($pathAux, "a");
                        fwrite($file, $matricula." ".$tipoM." ".$fechaHora." ".$mensaje.PHP_EOL);
                        fclose($file);
                        $this->Grupo_Act['id_actual'] = $this->session->get('Id_Grupo_Actual');
                        //se guarda actividad para HISTORIAL de grupo
                        $this->saveHistory(2,$this->session->get('user')['Matricula'],$this->session->get('Id_Grupo_Actual'));
                        echo json_encode("Archivos almacenados con Éxito");
                    }

            }
            else{
                echo json_encode("Error, No hay archivos");
            }
        }
        else{
            echo json_encode("Error, no es post");
        }
    }

    public function enviarDocAjaxAction(){

        //funcion para enviar un archivo (pdf/doc/txt)
        $this->view->disable();
        if ($this->request->isPost()) {

            if ($this->request->hasFiles()) {

                    $validation = new Validation();
                    $validation->add(
                        "Nombre_File",
                        new FileValidator(
                            [
                                "maxSize"              => "5M",
                                "messageSize"          => "El archivo excede el tamaño máximo (:max)",
                                "allowedTypes"         => [
                                    'application/pdf',
                                    'application/msword',
                                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                    'text/plain'
                                ],
                                "messageType"          => "Solo se permiten archivos en formato (pdf / doc / docx / txt)",

                            ]
                        )
                    );
                    //se valida el post
                    $messages = $validation->validate($_FILES);

                    if (count($messages)) {
                        //se guardan los mensajes de error en archivo
                        $ind=0;
                        foreach ($messages as $message) {
                            $dataAux[$ind++]=$message->getMessage();
                        }

                        echo json_encode($dataAux);
                    }
                    else{

                        $files = $this->request->getUploadedFiles();

                        $pathMensaje1 = "";
                        // Se guarda las imagenes en su correspondiente carpeta
                        foreach ($files as $file) {
                            // Move the file into the application
                            $pathMensaje2 = BASE_PATH."/public/files/Chats/chat".$this->session->get('Id_Grupo_Actual')."/Documentos/".$file->getName();
                            $pathMensaje1 = $this->url->get("public/files/Chats/chat".((int)$this->session->get('Id_Grupo_Actual'))."/Documentos/".$file->getName()."");
                            $file->moveTo(
                                $pathMensaje2
                            );
                        }
                        //se almacena la accion en los mensajes.txt del grupo
                        $mensaje = $pathMensaje1;
                        $matricula = $this->session->get('user')['Matricula'];
                        $tipoM = 2;
                        $modelGrupo = new Grupo();
                        $fechaHora = $modelGrupo->getFechaNow();

                        //Almacenamiento de mensaje
                        $pathAux = BASE_PATH."/public/files/Chats/chat".$this->session->get('Id_Grupo_Actual')."/Mensajes.txt";

                        $file = fopen($pathAux, "a");
                        fwrite($file, $matricula." ".$tipoM." ".$fechaHora." ".$mensaje.PHP_EOL);
                        fclose($file);
                        $this->Grupo_Act['id_actual'] = $this->session->get('Id_Grupo_Actual');
                        //se guarda actividad para HISTORIAL de grupo
                        $this->saveHistory(3,$this->session->get('user')['Matricula'],$this->session->get('Id_Grupo_Actual'));
                        echo json_encode("Archivos almacenados con Éxito");
                    }

            }
            else{
                echo json_encode("Error, No hay archivos");
            }
        }
        else{
            echo json_encode("Error, no es post");
        }
    }

    public function enviarMensajeAction(){
        //echo "var_dump:   ".var_dump($this->session->get('Id_Grupo_Actual'))."<br><br>";
        //echo "Registro: ".$this->session->get('Id_Grupo_Actual')."<br><br>";
        if ($this->request->isPost()) {
            if ($this->request->getPost('mensaje_name') != "") {
                //Preparar la información para guargar mensaje en archivo
                $mensaje = $this->request->getPost('mensaje_name');
                $matricula = $this->session->get('user')['Matricula'];
                $tipoM = 0;
                $modelGrupo = new Grupo();
                $fechaHora = $modelGrupo->getFechaNow();

                //Almacenamiento de mensaje
                $pathAux = BASE_PATH."/public/files/Chats/chat".$this->session->get('Id_Grupo_Actual')."/Mensajes.txt";

                $file = fopen($pathAux, "a");
                fwrite($file, $matricula." ".$tipoM." ".$fechaHora." ".$mensaje.PHP_EOL);
                fclose($file);
            }
        }

        $this->Grupo_Act['id_actual'] = $this->session->get('Id_Grupo_Actual');
           return $this->dispatcher->forward([
                'controller'  => 'grupo',
                'action'      => 'index'
            ]);

    }


//Cerrar Sesión
    public function logoutAction()
    {
        $response = new Response();
        $response->redirect("session/end");
        $response->send();
    }

    public function consultarAction()
    {
        $grupo = new Integrantegrupo();
        $post = $grupo->getIntegranteGrupo('201506987');
        echo "Nueva conculta<br><br>";
        foreach ($post as $perro) {
            echo "Id grupo:         " . $perro->Id_Grupo . "<br>";
            echo "Nombre Grupo      " . $perro->Nombre_G . "<br>";
            echo "Id Lider:         " . $perro->Id_Lider . "<br>";
            echo "Clave grupo:      " . $perro->Clave_Grupo . "<br><br>";
        }
    }

//Funciones de Administracion de grupos
    public function dejarGrupoIntegranteAction(){
        if (!$this->session->get('user')) {
            $response = new Response();
            $response->redirect("session/index");
            $response->send();
        }
        if ($this->request->isPost()) {
            $id_grupo_a_dejar = $this->session->get('Id_Grupo_Actual');
            $success = Integrantegrupo::findFirst([
                        'columns'    => '*',
                        'conditions' => 'Id_Grupo = ?1 AND Id_Integrante = ?2',
                        'bind'       => [
                            1 => $id_grupo_a_dejar,
                            2 => $this->session->get('user')['Matricula'],
                        ]
            ]);

            if ($success !== false) {
                if ($success->delete() === false) {
                    $this->flash->error('Lo sentimos, hubo un error a la hora de dejar el grupo');
                } else {
                    $this->flash->success('Haz dejado el grupo!');
                    //se guarda actividad para HISTORIAL de grupo
                    $this->saveHistory(8,$this->session->get('user')['Matricula'],$id_grupo_a_dejar);
                }
            }
            else{
                $this->flash->error('No existe el grupo con ese ID');
            }
            return $this->dispatcher->forward([
                'controller' => 'grupo',
                'action' => 'index'
            ]);
        }
        else{
            $response = new Response();
            $response->redirect("grupo/index");
            $response->send(); 
        }
    }



    public function desplegarGrupoAction($id_grupo_aux = null,$tipo_aux = null)
    {   //tipo_despli=1 ->  'Abrir grupo'  
        //tipo_despli=2 -> 'Abrir administración'
        if($this->request->isGet()){
            $id_grupo = $this->request->getQuery('id_grupo');
            $tipo_despli = $this->request->getQuery('tipo');

            if (($id_grupo != null) && ($tipo_despli != null)) {
                $successModelG = Grupo::findFirst(["Id_Grupo = ".$id_grupo]);
                if($successModelG){
                    if($tipo_despli == 2){
                        $success = Grupo::findFirst([
                                    'columns'    => '*',
                                    'conditions' => 'Id_Grupo = ?1 AND Id_Lider = ?2',
                                    'bind'       => [
                                        1 => $id_grupo,
                                        2 => $this->session->get('user')['Matricula'],
                                    ]
                                                    ]);
                        if (!$success) {
                            $tipo_despli = 3;
                        }
                    }
        
                    $this->Grupo_Act['id_actual'] = $id_grupo;
                    $this->Tipo_Despli = $tipo_despli;

                    //condiciones para guardar en el HISTORIAL de grupo
                    if($this->session->has('Id_Grupo_Actual'))
                    {
                        if ($id_grupo != $this->session->get('Id_Grupo_Actual')) 
                        {
                            $this->saveHistory(1,$this->session->get('user')['Matricula'],$id_grupo);
                        }
                    }
                    else
                    {
                        $this->saveHistory(1,$this->session->get('user')['Matricula'],$id_grupo);
                    }
        
                    return $this->dispatcher->forward([
                        'controller' => 'grupo',
                        'action' => 'index'
                    ]);
                }
                else{
                    //ocurre cuando se ha eliminado un grupo de repente
                    $response = new Response();
                    $response->redirect("grupo/index/#");
                    $response->send();
                }  
            } else {
                //echo "Uno o ambos son null: ". $id_grupo."  ".$tipo_despli;
                $response = new Response();
                $response->redirect("grupo/index/#####");
                $response->send();
            }
        }
        else{
            if (($id_grupo_aux != null) && ($tipo_aux != null)) {
                $successModelG = Grupo::findFirst(["Id_Grupo = ".$id_grupo_aux]);
                if($successModelG){
                    if($tipo_aux == 2){
                        $success = Grupo::findFirst([
                                    'columns'    => '*',
                                    'conditions' => 'Id_Grupo = ?1 AND Id_Lider = ?2',
                                    'bind'       => [
                                        1 => $id_grupo_aux,
                                        2 => $this->session->get('user')['Matricula'],
                                    ]
                                                    ]);
                        if (!$success) {
                            $tipo_aux = 3;
                        }
                    }
        
                    $this->Grupo_Act['id_actual'] = $id_grupo_aux;
                    $this->Tipo_Despli = $tipo_aux;
        
                    return $this->dispatcher->forward([
                        'controller' => 'grupo',
                        'action' => 'index'
                    ]);
                }
                else{
                    //ocurre cuando se ha eliminado un grupo de repente
                    $response = new Response();
                    $response->redirect("grupo/index/#");
                    $response->send();
                }  
            } else {
                //echo "Uno o ambos son null: ". $id_grupo."  ".$tipo_despli;
                $response = new Response();
                $response->redirect("grupo/index/#####");
                $response->send();
            }
        }
    }


//CREAR GRUPO
    public function crearGrupoAction()
    {
        if ($this->request->isPost()) {

            //inicializando modelos
            $grupoAux = new Grupo();


            $idMax = Grupo::findFirst("Id_Grupo = (Select max(grupo.Id_Grupo) from grupo)")->Id_Grupo;
            if ($idMax == null) {
                $idMax = 1;
            } else {
                $idMax = $idMax + 1;
            }

            $post1 = [
                'Id_Grupo' => $idMax,
                'Nombre_G' => $this->request->getPost('name_group'),
                'Id_Lider' => $this->session->get('user')['Matricula'],
                'Clave_Grupo' => $this->request->getPost('clave_group')
            ];

            $success = $grupoAux->saveWithEncrypt($post1, $this->Clave_Encriptacion['key']);

            if ($success) {
                //se guarda actividad para HISTORIAL de grupo
                $this->saveHistory(9,$this->session->get('user')['Matricula'],$idMax);

            //NOTA: Para crear directorios y ficheros es awuevo poner el path fisico pero para auxiliarnos
            //      se ocupa la constante BASE_PATH
                if (mkdir(BASE_PATH."/public/files/Chats/chat".$idMax, 0777, true)) {
                   if (mkdir(BASE_PATH ."/public/files/Chats/chat$idMax/Documentos", 0777, true)) {
                       if (mkdir(BASE_PATH ."/public/files/Chats/chat".$idMax."/Imagenes", 0777, true)) {
                           $my_file = BASE_PATH ."/public/files/Chats/chat".$idMax."/Mensajes.txt";
                           $handle = fopen($my_file, 'w') or die('Cannot open file:  '.$my_file);
                           fclose($handle);
                       }
                   }
                }

                //se almacena en integrante al lider del grupo
                $integrantegrupoAux = new Integrantegrupo();                
                $post3 = [
                    'Id_Grupo' => $idMax,
                    'Id_Integrante' => $this->session->get('user')['Matricula']
                ];
                $integrantegrupoAux->save(
                    $post3,
                    [
                        "Id_Grupo",
                        "Id_Integrante"
                    ]
                );
                

                $this->flash->success('Grupo Creado con Éxito');

                return $this->dispatcher->forward(
                    [
                        "controller" => "grupo",
                        "action"     => "index",
                    ]
                );
            } else {
                echo "Sorry, the following problems were generated: ";
                $messages = $grupoAux->getMessages();
                foreach ($messages as $message) {
                    echo $message->getMessage(), "<br/>";
                }
            }
        }
        $this->flash->error('Fallo al crear Grupo');
        return $this->dispatcher->forward(
            [
                "controller" => "grupo",
                "action"     => "index",
            ]
        );
    }

    public function crearGrupoAnterior() //anterior
    {
        if ($this->request->isPost()) {

            //inicializando modelos
            $grupoAux = new Grupo();


            $idMax = Grupo::findFirst("Id_Grupo = (Select max(grupo.Id_Grupo) from grupo)")->Id_Grupo;
            if ($idMax == null) {
                $idMax = 1;
            } else {
                $idMax = $idMax + 1;
            }

            $post1 = [
                'Id_Grupo' => $idMax,
                'Nombre_G' => $this->request->getPost('name_group'),
                'Id_Lider' => $this->session->get('user')['Matricula'],
                'Clave_Grupo' => $this->generateRandomString('10')
            ];

            $success = $grupoAux->saveWithEncrypt($post1, $this->Clave_Encriptacion['key']);

            if ($success) {

            //NOTA: Para crear directorios y ficheros es awuevo poner el path fisico pero para auxiliarnos
            //      se ocupa la constante BASE_PATH
                if (mkdir(BASE_PATH."/public/files/Chats/chat".$idMax, 0755, true)) {
                   if (mkdir(BASE_PATH ."/public/files/Chats/chat$idMax/Documentos", 0755, true)) {
                       if (mkdir(BASE_PATH ."/public/files/Chats/chat".$idMax."/Imagenes", 0755, true)) {
                           $my_file = BASE_PATH ."/public/files/Chats/chat".$idMax."/Mensajes.txt";
                           $handle = fopen($my_file, 'w') or die('Cannot open file:  '.$my_file);
                           fclose($handle);
                       }
                   }
                }


                //BELLEZA DIVINA (BENDITOS "JSON" xD)
                $chips = json_decode($this->request->getPost('arrayChipPost'), true);
                foreach ($chips as $chip => $campo) {

                    $integrantegrupoAux = new Integrantegrupo();
                    //se almacenan todas las matriculas ingresadas por el lider de grupo
                    $successAux = Usuario::findFirst("Matricula = " . $campo['tag'] . "");
                    if ($successAux) {
                        $post3 = [
                            'Id_Grupo' => $idMax,
                            'Id_Integrante' => $campo['tag']
                        ];
                        $integrantegrupoAux->save(
                            $post3,
                            [
                                "Id_Grupo",
                                "Id_Integrante"
                            ]
                        );
                    }
                    //else{echo "La matricula: ".$campo['tag']." no existe";}
                }

                $this->flash->success('Grupo Creado con Éxito');

                return $this->dispatcher->forward(
                    [
                        "controller" => "grupo",
                        "action"     => "index",
                    ]
                );
            } else {
                echo "Sorry, the following problems were generated: ";
                $messages = $grupoAux->getMessages();
                foreach ($messages as $message) {
                    echo $message->getMessage(), "<br/>";
                }
            }
        }
        $this->flash->error('Fallo al crear Grupo');
        return $this->dispatcher->forward(
            [
                "controller" => "grupo",
                "action"     => "index",
            ]
        );
    }


//UNIRSE A GRUPO POR ID Y PASSWORD
    public function unirseGrupoAction()
    {
        if ($this->request->isPost()) {
            //echo "Var_dum:  ".var_dump($this->request->getPost());
            
            $id_grupo = $this->request->getPost('id_group_name');
            $clave_grupo = $this->request->getPost('cl_group');

            if (is_numeric($id_grupo)) {
                //------------------
                $grupoUnir = Grupo::findFirst("Id_Grupo = $id_grupo");

                if ($grupoUnir != false) {
                    $grupoAuxUnir = new Grupo();
                    $claveAuxDecrypt = $grupoAuxUnir->getPassDecryptById($id_grupo, $this->Clave_Encriptacion['key']);


                    if ($clave_grupo == $claveAuxDecrypt) {

                        $postAuxUnir = [
                            'Id_Grupo' => $id_grupo,
                            'Id_Integrante' => $this->session->get('user')['Matricula']

                        ];
                        $integranteAux = new Integrantegrupo();
                        $success = $integranteAux->save(
                            $postAuxUnir,
                            [
                                "Id_Grupo",
                                "Id_Integrante"
                            ]
                        );

                        if ($success) {
                            //se guarda actividad para HISTORIAL de grupo
                            $this->saveHistory(7,$this->session->get('user')['Matricula'],$id_grupo);
                            $this->flash->success("¡Bienvenido a ".$grupoUnir->Nombre_G."!");

                            return $this->dispatcher->forward(
                                [
                                    "controller" => "grupo",
                                    "action"     => "index"
                                ]
                            );
                        } else {
                            echo "Sorry, the following problems were generated with success1: ";
                            $messages = $grupoAux1->getMessages();
                            foreach ($messages as $message) {
                                echo $message->getMessage(), "<br/>";
                            }
                        }
                    }
                }
                //------------------
            }    
        }

        
        $this->flash->error('Error al unirse a Grupo');
        return $this->dispatcher->forward(
            [
                "controller" => "grupo",
                "action"     => "index"
            ]
        );

        
    }


    public function unirseGrupoAnterior() //anterior
    {
        if ($this->request->isPost()) {

            $id_grupo = $this->request->getPost('id_group');
            $clave_grupo = $this->request->getPost('cl_group');

            if (is_numeric($id_grupo)) {
                //----
                $grupoUnir = Grupo::findFirst("Id_Grupo = $id_grupo");

                if ($grupoUnir != false) {
                    $grupoAuxUnir = new Grupo();
                    $claveAuxDecrypt = $grupoAuxUnir->getPassDecryptById($id_grupo, $this->Clave_Encriptacion['key']);


                    if ($clave_grupo == $claveAuxDecrypt) {

                        $postAuxUnir = [
                            'Id_Grupo' => $id_grupo,
                            'Id_Integrante' => $this->session->get('user')['Matricula']

                        ];
                        $integranteAux = new Integrantegrupo();
                        $success = $integranteAux->save(
                            $postAuxUnir,
                            [
                                "Id_Grupo",
                                "Id_Integrante"
                            ]
                        );

                        if ($success) {
                            //$this->flash->success('¡Bienvenido a un nuevo grupo!');

                            return $this->dispatcher->forward(
                                [
                                    "controller" => "grupo",
                                    "action"     => "index"
                                ]
                            );
                        } else {
                            echo "Sorry, the following problems were generated with success1: ";
                            $messages = $grupoAux1->getMessages();
                            foreach ($messages as $message) {
                                echo $message->getMessage(), "<br/>";
                            }
                        }
                    }
                }
                //----
            }
            //else{$this->flash->error('Campo no numérico');}
            //$this->flash->error('Error al borrar Grupo');
            //$this->view->form_error = "Error en la matricula";
        }
        $this->flash->error('Error al unirse a Grupo');
        return $this->dispatcher->forward(
            [
                "controller" => "grupo",
                "action"     => "index"
            ]
        );
    }


//ELIMINAR UN GRUPO (CORREGIR PARA BORRADO EXCLUSIVO)
    public function eliminarGrupoAction()
    {   
        if (!$this->session->get('user')) {
            $response = new Response();
            $response->redirect("session/index");
            $response->send();
        }
        if ($this->request->isPost()) {
            //obtenemos el id del grupo actual para borrar
            $id_grupo = $this->request->getPost('id_grupo_actual');
            $grupoAux2 = Integrantegrupo::find("Id_Grupo = $id_grupo");

            if ($grupoAux2 != false) {
                foreach ($grupoAux2 as $integrante) { //se borran todos los integrantes del grupo a eliminar
                    $successAux = $integrante->delete();
                }

                //-------------------
                $grupoAux1 = Grupo::findFirst("Id_Grupo = $id_grupo");//se borra grupo en sí

                if ($grupoAux1 != false) {
                    $success1 = $grupoAux1->delete();
                    if ($success1) {
                        //Nota: el id_grupo de castea a (int) porque el parametro String es de (3) con un espacio de sobra
                        if($this->removeDir(BASE_PATH."\\public\\files\\Chats\\chat".((int)$id_grupo))) {

                            $this->flash->success("Grupo eliminado con Éxito");
                            //se elimina todo registro de historial de grupo eliminado
                            $this->deleteHistory($id_grupo);

                            return $this->dispatcher->forward(
                                [
                                    'controller' => 'grupo',
                                    'action'     => 'index'
                                ]
                            );
                        }
                    } else {
                        echo "Sorry, the following problems were generated with success1: ";
                        $messages = $grupoAux1->getMessages();
                        foreach ($messages as $message) {
                            echo $message->getMessage(), "<br/>";
                        }
                    }
                }
                //-------------------
            }
            
        }
        $this->flash->error('Error al borrar Grupo');
        return $this->dispatcher->forward(
            [
                "controller" => "grupo",
                "action"     => "index"
            ]
        );
    }

    public function eliminarGrupAnterior() //funcion anterior
    {
        if ($this->request->isPost()) {

            $id_grupo = $this->request->getPost('dele_group');
            if (is_numeric($id_grupo)) {

                $grupoAux2 = Integrantegrupo::find("Id_Grupo = $id_grupo");

                if ($grupoAux2 != false) {
                    foreach ($grupoAux2 as $integrante) {
                        $successAux = $integrante->delete();
                    }

                    //----
                    $grupoAux1 = Grupo::findFirst("Id_Grupo = $id_grupo");

                    if ($grupoAux1 != false) {
                        $success1 = $grupoAux1->delete();
                        if ($success1) {
                            if($this->removeDir(BASE_PATH."/public/files/Chats/chat".$id_grupo)) {

                                $this->flash->success("Grupo eliminado con Éxito");
                                return $this->dispatcher->forward(
                                    [
                                        'controller' => 'grupo',
                                        'action'     => 'index'
                                    ]
                                );
                            }
                        } else {
                            echo "Sorry, the following problems were generated with success1: ";
                            $messages = $grupoAux1->getMessages();
                            foreach ($messages as $message) {
                                echo $message->getMessage(), "<br/>";
                            }
                        }
                    }
                    //----
                }
            }
            //else{$this->flash->error('Campo no numérico');}
            //$this->flash->error('Error al borrar Grupo');
            //$this->view->form_error = "Error en la matricula";
        }
        $this->flash->error('Error al borrar Grupo');
        return $this->dispatcher->forward(
            [
                "controller" => "grupo",
                "action"     => "index"
            ]
        );
    }


    public function ejemploFechasAction(){
        //setlocale(LC_TIME, 'es_CO.UTF-8');
        //$date = "2019-05-02";
        //echo strftime("%A, %d  de %B del %G", strtotime($date));

                $pathAux = BASE_PATH."/public/files/Chats/chat11/Mensajes.txt";
                $file = fopen($pathAux, "r");
                //se lee la informacion del archivo
                $data;
                $ind = 0;
                while (!feof($file)) {
                    $data[$ind++] = fgets($file);
                }
                fclose($file);

                //se quita la ultima posicion por ser ultimo salto de linea
                unset($data[count($data)-1]);

                //si hay mensajes se cargan en $results
                if ($data) {
                    $ind = 0;
                    $results;
                    list($auxArray[0],$auxArray[1],$auxArray[2],$auxArray[3],$auxArray[4]) = explode(' ',$data[0],5);

                    $fechaActual = $auxArray[2];
                    //echo "Fecha actual inicial: ".$fechaActual."<br><br><br>";

                    foreach ($data as $row) {
                        //echo "Registro:  ".$row."<br>";
                        //se almacena cada $row por secciones (Matricula, tipo, fecha, hora, mensaje)
                        list($results[$ind]['Matricula'], $results[$ind]['Tipo_M'], $results[$ind]['Fecha'], $results[$ind]['Hora'],$results[$ind]['Mensaje']) = explode(' ',$row,5);
                        //se asigna un nuevo elemento, obteniendo el nombre de la persona
                        $results[$ind]['Nombre'] = Usuario::findFirst("Matricula = ".$results[$ind]['Matricula']."")->Nombre;
                        if ($ind == 0) { //dianuevo para el primer mensaje enviado por ser la primera fecha
                            setlocale(LC_TIME, 'es_CO.UTF-8');
                            $results[$ind]['DiaNuevo'] = strftime("%A, %d  de %B del %G", strtotime($results[$ind]['Fecha']));
                            //echo "Dia nuevo:  ".$results[$ind]['DiaNuevo']."<br><br>";
                        }
                        //se calcula la diferencia de fechas para ver si es un dia diferente
                        $date1 = new DateTime($fechaActual);
                        $date2 = new DateTime($results[$ind]['Fecha']);
                        $diff = $date1->diff($date2);
                        //echo $diff->days . ' days '."<br>";

                        if($diff->days > 0){ //si hay diferencia se asigna cadena de nuevo dia

                            //$dateAux1 = new DateTime(date("Y-m-d"));
                            //$dateAux2 = new DateTime($results[$ind]['Fecha']);
                            $modelForFecha = new Grupo();
                            $diffAux = (new DateTime($modelForFecha->getFechaNowDateOnly()))->diff((new DateTime($results[$ind]['Fecha'])));


                            //echo "Date 1: ".$dateAux1->format('Y-m-d')."<br><br>";
                            //echo "Date 2: ".$dateAux2->format('Y-m-d')."<br><br>";
                            //echo "Diff  : ".$diffAux->days."<br><br>";

                            if ($diffAux->days > 0) {
                                setlocale(LC_TIME, 'es_CO.UTF-8');
                                $results[$ind]['DiaNuevo'] = strftime("%A, %d  de %B del %G", strtotime($results[$ind]['Fecha']));
                            }
                            else{
                                $results[$ind]['DiaNuevo'] = "Hoy";
                            }



                            echo "Hoy:  ".(new DateTime($modelForFecha->getFechaNowDateOnly()))->format('Y-m-d')."<br><br>";
                            echo "Dia nuevo:  ".$results[$ind]['DiaNuevo']."<br><br>";
                            $fechaActual = $results[$ind]['Fecha'];
                        }

                        /*
                        echo (isset($results[$ind]['DiaNuevo']))? $results[$ind]['DiaNuevo']."<br><br>":""."<br>";
                        echo "Matricula:  ".$results[$ind]['Matricula']."<br>";
                        echo "Nombre:  ".$results[$ind]['Nombre']."<br>";
                        echo "tipo:  ".$results[$ind]['Tipo_M']."<br>";
                        echo "Fecha:  ".$results[$ind]['Fecha']."<br>";
                        echo "Hora:  ".$results[$ind]['Hora']."<br>";
                        echo "Mensaje:  ".$results[$ind]['Mensaje']."<br>";
                        echo "<br>";
                        */
                        $ind++;
                    }
                }
    }
    public function cargarMensajesAction(){

        $this->view->disable();

        if ($this->request->isGet()) {

            $id_grupo_actual = $this->request->getQuery('id_grupo');
            if ($id_grupo_actual>0) {

                $successModelG = Grupo::findFirst(["Id_Grupo = ".$id_grupo_actual]);
                if($successModelG){
                    $successModelG2 = Integrantegrupo::findFirst([
                                    'columns'    => '*',
                                    'conditions' => 'Id_Grupo = ?1 AND Id_Integrante = ?2',
                                    'bind'       => [
                                        1 => $id_grupo_actual,
                                        2 => $this->session->get('user')['Matricula']
                                    ]    
                                ]);
                    if ($successModelG2) {
                        $pathAux = BASE_PATH."/public/files/Chats/chat".$id_grupo_actual."/Mensajes.txt";
                        $file = fopen($pathAux, "r");
                        //se lee la informacion del archivo
                        $data;
                        $ind = 0;
                        while (!feof($file)) {
                            $data[$ind++] = fgets($file);
                        }
                        fclose($file);

                        //se quita la ultima posicion por ser ultimo salto de linea
                        unset($data[count($data)-1]);

                        //si hay mensajes se cargan en $results
                        if ($data) {
                            $ind = 0;
                            $results;
                            list($auxArray[0],$auxArray[1],$auxArray[2],$auxArray[3],$auxArray[4]) = explode(' ',$data[0],5);

                            $fechaActual = $auxArray[2];

                            foreach ($data as $row) {
                                //se almacena cada $row por secciones (Matricula, tipo, fecha, hora, mensaje)
                                list($results[$ind]['Matricula'], $results[$ind]['Tipo_M'], $results[$ind]['Fecha'], $results[$ind]['Hora'],$results[$ind]['Mensaje']) = explode(' ',$row,5);
                                $results[$ind]['Hora'] = $this->formatearFecha($results[$ind]['Hora']);
                                //se asigna un nuevo elemento, obteniendo el nombre de la persona
                                $results[$ind]['Nombre'] = Usuario::findFirst("Matricula = ".$results[$ind]['Matricula']."")->Nombre;
                                if ($ind == 0) { //dianuevo para el primer mensaje enviado por ser la primera fecha
                                    setlocale(LC_TIME, 'es_CO.UTF-8');
                                    $results[$ind]['DiaNuevo'] = "Inicio de conversación: ".strftime("%A, %d  de %B del %G", strtotime($results[$ind]['Fecha']));
                                }
                                //se calcula la diferencia de fechas para ver si es un dia diferente
                                $date1 = new DateTime($fechaActual);
                                $date2 = new DateTime($results[$ind]['Fecha']);
                                $diff = $date1->diff($date2);

                                if($diff->days > 0){ //si hay diferencia se asigna cadena de nuevo dia
                                    //comprobar si se trata de el dia de hoy

                                    $modelForFecha = new Grupo();
                                    $diffAux = (new DateTime($modelForFecha->getFechaNowDateOnly()))->diff((new DateTime($results[$ind]['Fecha'])));

                                    if ($diffAux->days > 0) {
                                        setlocale(LC_TIME, 'es_CO.UTF-8');
                                        $results[$ind]['DiaNuevo'] = strftime("%A, %d  de %B del %G", strtotime($results[$ind]['Fecha']));
                                        //$results[$ind]['DiaNuevo'] = "Hoy";
                                    }
                                    else{
                                        $results[$ind]['DiaNuevo'] = "Hoy";
                                    }

                                    $fechaActual = $results[$ind]['Fecha'];
                                }

                                $ind++;
                            }
                            echo json_encode ($results);
                        }
                        else{
                            echo json_encode("Error, no hay mensajes que cargar");
                        }
                    }
                    else{
                        //Ocurre cuando el lider elimina al integrante que a la vez es ocupado por un integrante en específico
                        echo json_encode("Fatal Error");
                    }
                }
                else{
                    //Ocurre cuando el lider elimina el grupo actual donde se esta trabajando
                    echo json_encode("Fatal Error");
                    
                }
            }
            else{
                echo json_encode("Error, id de grupo no asignado");
            }
        }
        else{
            echo json_encode("Error, no es un get");
        }
    }

    public function formatearFecha($fecha){
        return date('g:i a', strtotime($fecha));
    }

//GESTIONAR GRUPOS COMO LÍDER
    public function Grupo_ConfAction(){
        if (!$this->session->get('user')) {
            $response = new Response();
            $response->redirect("session/index");
            $response->send();
        }
        if ($this->request->isPost()) {
            //echo "Valores del post:<br>".var_dump($this->request->getPost()); (var_dum para comprobar info)
            //se deben comparar los valores anteriores y los enviados para comprobar si hubo cambios
            $id_grupo       =   $this->request->getPost('id_grupo');
            $nombreG_ant    =   $this->request->getPost('nombreG_anterior');
            $nombreG        =   $this->request->getPost('nombreG');
            $chipsAnterior  =   json_decode($this->request->getPost('arrayChipPost_Ant'), true);
            $chipsNow       =   json_decode($this->request->getPost('arrayChipPost'), true);
            $claveAnterior  =   $this->request->getPost('claveAnterior');
            $claveNow       =   $this->request->getPost('claveG');

            $mensajesRes; $band = 0;
            if ($nombreG != $nombreG_ant) { //cambio en el nombre
                $grupoModel = Grupo::findFirst('Id_Grupo = '.$id_grupo);
                $grupoModel->Nombre_G = $nombreG;
                //se guarda actividad para HISTORIAL de grupo
                $this->saveHistory(4,$this->session->get('user')['Matricula'],$this->session->get('Id_Grupo_Actual'));
                if($grupoModel->update())
                    $mensajesRes['Cambio_Nombre'] = "
                                    <div class=\"row center\">    
                                        <span class =\"light-green-text text-accent-3\">
                                            Nombre Modificado Exitosamente!
                                        </span>
                                    </div>
                                        ";
                else
                    $mensajesRes['Cambio_Nombre'] = "
                                    <div class=\"row center\">
                                        <span class =\"red-text text-darken-1\">
                                            Error al cambiar nombre, intente de nuevo
                                        </span>
                                    </div>
                                        ";
            }
            else{ //sin cambio en el nombre
                $band++;
            }

            if (count($chipsAnterior) > count($chipsNow)) { //cambio en los Integrantes
                //se guarda actividad para HISTORIAL de grupo
                $this->saveHistory(6,$this->session->get('user')['Matricula'],$this->session->get('Id_Grupo_Actual'));
                $chipsAux = array(); 
                foreach ($chipsNow as $chip => $campo) { //guardar chips resultantes en un array
                    array_push($chipsAux, $campo['tag']);
                }

                foreach ($chipsAnterior as $chip => $campo) {//checar cuales integrantes ya NO ESTAN
                    if (!in_array($campo['tag'], $chipsAux)) {// si no esta se borra el integrante
                        $integranteModel = Integrantegrupo::findFirst(
                                [
                                    'columns'    => '*',
                                    'conditions' => 'Id_Grupo = ?1 AND Id_Integrante = ?2',
                                    'bind'       => [
                                        1 => $id_grupo,
                                        2 => $campo['tag']
                                    ]
                                ]
                            );
                        if ($integranteModel) {
                            $integranteModel->delete();
                        }
                    }
                }

                $mensajesRes['Cambio_Chips'] = "
                            <div class=\"row center\">    
                                <span class =\"light-green-text text-accent-3\">
                                    Se han modificado los integrantes del grupo con éxito!
                                </span>
                            </div>
                                ";

            }
            else{//sin cambios en Integrantes
                $band++;
            }

            if ($claveAnterior != $claveNow) { //cambio en la clave
                //se guarda actividad para HISTORIAL de grupo
                $this->saveHistory(5,$this->session->get('user')['Matricula'],$this->session->get('Id_Grupo_Actual'));
                $grupoModel2 = new Grupo();
                $success = $grupoModel2->updatePassword($id_grupo,$claveNow,$this->Clave_Encriptacion['key']);
                if ($success) 
                    $mensajesRes['Cambio_Clave'] = "
                                    <div class=\"row center\">
                                        <span class =\"light-green-text text-accent-3\">
                                            Clave Modificada Exitosamente!
                                        </span>
                                    </div>";
                else
                    $mensajesRes['Cambio_Clave'] = "
                                    <div class=\"row center\">
                                        <span class =\"red-text text-darken-1\">
                                            Error al cambiar la clave del grupo, intente de nuevo
                                        </span>
                                    </div>
                                        ";

            }
            else{ //sin cambio en la clave de grupo
                $band++;
                if($band == 3)
                $mensajesRes['Sin_Cambio'] =    "<div class=\"row center\">
                                                    <span class=\"yellow-text\">
                                                        SIN CAMBIOS
                                                    </span>
                                                </div>";   
            }





            $this->view->Result_Gestion = $mensajesRes;

            return $this->dispatcher->forward(
                [
                    "controller"    => "grupo",
                    "action"        => "desplegarGrupo",
                    'params' => [$id_grupo, 2]
                ]
            );

        }
        else{
            $response = new Response();
            $response->redirect("grupo/index");
            $response->send();
        }
    }


//HISTORIAL DE GRUPO (funciones)
    public function getHistoryAjaxAction(){
        $this->view->disable();
        //echo json_encode("Urraaa");
        if($this->request->isGet()){
            $id_grupo = $this->session->get('Id_Grupo_Actual');
            if($id_grupo != null)
            {
               $historyModel = new Historialg();
               $success = $historyModel->getHistoryByGroup((int)$id_grupo);

                if($success) {
                    $data;
                    $ind = 0;
                    foreach ($success as $row) {
                        $data[$ind]['Tipo_H']       = $success[$ind]['Tipo_H'];
                        $data[$ind]['Id_User']      = $success[$ind]['Id_User'];
                        $data[$ind]['Id_Grupo']     = $success[$ind]['Id_Grupo'];
                        $data[$ind]['Fecha_Hora']   = $success[$ind]['Fecha_Hora'];
                        $data[$ind]['Nombre']       = $success[$ind]['Nombre'];
                        $data[$ind]['Hora'] = $this->formatearFecha($success[$ind]['Fecha_Hora']);
                        $data[$ind]['Fecha'] = date_format(date_create($success[$ind]['Fecha_Hora']), 'd-m-Y');

                        $ind++;
                    }
                    echo json_encode($data);
                }
                else echo json_encode("Error 1"); 
            }
            else echo json_encode("Error 2");
        }
        else echo json_encode("Error 3");
    }

    public function saveHistory($tipo_h, $id_user, $id_grupo){
            $modelGrupo = new Grupo();
            $historialModel = new Historialg();
            $fechaHora = $modelGrupo->getFechaNow();

            $post=[
                "Tipo_H" => $tipo_h,
                "Id_User" => $id_user,
                "Id_Grupo" => $id_grupo,
                "Fecha_Hora" => $fechaHora
            ];
            //echo var_dump($post);
            $historialModel->save(
                $post,
                [
                    "Tipo_H",
                    "Id_User",
                    "Id_Grupo",
                    "Fecha_Hora"
                ]
            );
    }

    public function deleteHistory($id_grupo){ //se elimina historial cuando un grupo se elimina
        $history = Historialg::find("Id_Grupo=".$id_grupo."");

        if($history->delete()){
            return true;
        }
        else return false;
    }

//Funcion pruebas con archivos para manejo de mensajes en Chat
    public function pruebaApendizarFileAction(){
        echo "<p style='color: #000000'>";
        //para escribir algo en negro porque jesus puso el body en blanco
        echo "</p>";
        $pathAux = BASE_PATH."/public/files/Chats/chat11/Mensajes.txt";
        $file = fopen($pathAux, "a");

        $matricula = "201504691";
        $tipoM = 0;
        $mensaje = "Que pedo mamarachos!!";

        fwrite($file, $matricula." ".$tipoM." ".$mensaje. PHP_EOL);
        fwrite($file, "Añadimos línea 2" . PHP_EOL);

        fclose($file);
    }

    public function pruebaLeerFileAction(){
        echo "<p style='color: #000000'>";
        //para escribir algo en negro porque jesus puso el body en blanco

        $pathAux = BASE_PATH."/public/files/Chats/chat11/Mensajes.txt";
        $file = fopen($pathAux, "r");

        $data;
        $ind = 0;
        while (!feof($file)) {
            $data[$ind++] = fgets($file);
        }

        unset($data[count($data)-1]);

        foreach ($data as $row) {
            echo "Registro:  ".$row."<br>";
        }

        fclose($file);

        echo "</p>";
    }

    //Función para borrar carpetas de grupos
    public function removeDir($dir) {
           $files = array_diff(scandir($dir), array('.','..'));
            foreach ($files as $file) {
              (is_dir("$dir/$file")) ? $this->removeDir("$dir/$file") : unlink("$dir/$file");
            }
            return rmdir($dir);
    }
    //funcion Prueba de Cadena Random para clave grupo
    public function cadRandomAction(){
       
        echo "Cadena random: " . $this->generateRandomString('10');
    }
    //funcion para crear una clave random de 10 elementos
    public function generateRandomString($length = 10)
    {
        
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }

    public function pruebaTextAreaAction(){
        $cadena = "Hola amigos como estan\nespero que esten muy bien\nyo estoy fenomenal";

        $exp = explode("\n", $cadena);
        $lineas = count($exp);
        echo $lineas."<br>";
        echo "Cadena: <br>";
        echo $cadena;
    }
}

?>
