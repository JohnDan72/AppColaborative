<?php

use Phalcon\Mvc\Controller;

class ControllerBase extends Controller
{
		public function initialize(){
			$this->tag->prependTitle("Colaborative |");
			$this->view->setTemplateAfter('main');
		}
}
