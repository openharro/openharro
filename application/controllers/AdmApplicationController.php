<?php
/**
 * Admin Application Controller
 *
 * @author Terence
 *
 */
class AdmApplicationController extends GD_Controller_Admin
{

	public function init(){
		parent::init();

		$this->getHelper('layout')->setLayout('administration');
	}


	public function indexAction()
	{
		$this->_redirect ( $this->view->myController . '/list' );
	}

	
	public function viewAction(){

		$dao = new GD_DAO_Application();
		$this->view->readArray = $dao->readData($this->getRequest()->getParam('id', 1));

		$this->view->tableMetaData = $dao->getTableMetaData();
		$this->view->tableColumnNames = $dao->getTableColumnNames();
		
		
		//var_dump($this->view->readArray);exit;
		
	}
	
	
	public function editAction(){
		
		$id = $this->getRequest()->getParam('id', 1);
		$form_options = null;
		$dao = new GD_DAO_Application();
		$form_data = $dao->readData($id);
		
		
		// Manage Form Display
		$form = new GD_Form_AdmGist($form_options, $form_data);
		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			if ($form->isValid($formData)) {
				echo 'success';
//				var_dump ($formData); exit;
				$dao->updateData($id, $formData);
				$this->_redirect ( $this->view->myController . '/view/id/' . $id  );
			} else {
				$form->populate($formData);
			}
		}
		$this->view->form = $form;

		//		var_dump ($this->view); exit;
		
		
		
		
	}
	
	

	// -------------------------------------------------------------------------------
	// Data Access Controllers
	// -------------------------------------------------------------------------------
	public function listAction(){
		parent::listAction();
		
//		echo "classification action reached"; exit;

		$dao = new GD_DAO_Application();
		if ($dao->ifExistTable()){
			$this->view->dataArray = $dao->getDataList();
			$this->view->tableMetaData = $dao->getTableMetaData();
			$this->view->tableColumnNames = $dao->getTableColumnNames();

			$paginator = Zend_Paginator::factory($this->view->dataArray);
			$paginator->setItemCountPerPage(10);
			$paginator->setCurrentPageNumber($this->getRequest()->getParam('p', 1));
			$this->view->paginator=$paginator;
		}else{
			$this->view->createTableOption = 1;
		}
			}




	// -------------------------------------------------------------------------------
	// Table Management Controllers
	// -------------------------------------------------------------------------------
	// Should be admin-only
	public function droptableAction() {
		Zend_Registry::get('log_control')->debug('A/C :' . $this->view->myController . '/' . $this->view->myAction . ' reached');

		$dao = new GD_DAO_Application();
		$dao->dropTable();
		echo "drop Table"; exit;
		$this->_redirect ( $this->view->myController . '/list' );
	}

	
	public function createtableAction() {
		Zend_Registry::get('log_control')->debug('A/C :' . $this->view->myController . '/' . $this->view->myAction . ' reached');

		$dao = new GD_DAO_Application();
		$dao->createTable();
		$dao->insertGistData();
		echo "createTable"; exit;
		$this->_redirect ( $this->view->myController . '/list' );
	}


	// -------------------------------------------------------------------------------
	// Javascript Library
	// - eventually move to own classes?
	// -------------------------------------------------------------------------------

	// -------------------------------------------------------------------------------
	// CSS Library
	// -------------------------------------------------------------------------------




}

