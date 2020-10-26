<?php
use Phalcon\Http\Response;
use Phalcon\Registry;

class SessionController extends ControllerBase
{

   	public function initialize()
    {
        $this->tag->setTitle(' Inicio de Sesión');
        parent::initialize();

        // CSS in the header
        //$headerCollection1 = $this->assets->collection('header');
        $this->assets->addCss('css/login.css');
        $this->assets->addCss('css/estilos_login.css');
        
        // JS in the header
        //$headerCollection2 = $this->assets->collection('header');
        $this->assets->addJs('js/script.js');
    }

    public function indexAction()
    {
    	if($this->session->get('user')){
            $response = new Response();
            $response->redirect("grupo/index");
            $response->send();
        }
    }


    private function _registerSession(Usuario $user)
    {
        $this->session->set('user', [
            'Matricula' => $user->Matricula,
            'Nombre' => $user->Nombre,
            'Ap_Paterno' => $user->Ap_Paterno,
            'Ap_Materno' => $user->Ap_Materno,
            'Correo' => $user->Correo,
            'Facultad' => $user->Facultad
        ]);

        $this->session->set('userFoto',$user->Foto);
        //$this->session->set('Id_Grupo_Actual',"-3");
    }

    public function loginAction(){
    	//echo "Contenido  ";
    	//echo var_dump($this->request->getPost());

    	if ($this->request->isPost()) {

            $matricula = $this->request->getPost('Matricula');
            $user = Usuario::findFirst([
            	"Matricula = '$matricula'"
			]);

            if ($user != false) {
                $this->_registerSession($user);
                $this->flash->success('Welcome ' . $user->Nombre);

                $response = new Response();
		    	$response->redirect('Grupo/index');
		    	$response->send();
                
            }

           // $this->flash->error('Error en la matricula');
            $this->view->form_error = "La matrícula no existe";
        }

        return $this->dispatcher->forward(
            [
                "controller" => "session",
                "action"     => "index"
            ]
        );

    }

    public function endAction()
    {
        $this->session->remove('user');
        $this->session->remove('userFoto');
        $this->session->remove('Id_Grupo_Actual');
        $this->session->remove('Tipo_Despli');
        //$this->flash->success('Goodbye!');

        return $this->dispatcher->forward(
            [
                "controller" => "session",
                "action"     => "index",
            ]
        );
    }    


}

