<?php
use Phalcon\Http\Response;

class IndexController extends ControllerBase
{

    public function indexAction()
    {	
    	$response = new Response();
    	$response->redirect('Session/index');
    	$response->send();
    	//return $this->dispatcher->forward([
    	//	"controller" => "session",
    	//	"action" => "index"
    	//]);
    }

}

