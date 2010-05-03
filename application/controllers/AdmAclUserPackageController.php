<?php
/**
 * Admin Admin Acl User Preference Controller
 *
 * @author Terence
 * @package administration
 * @version 
 *
 */
class AdmAclUserPackageController extends GD_Controller_Admin
{

	public function init(){
		parent::init();

		$this->getHelper('layout')->setLayout('administration');
	}

	public function indexAction()
	{
		$this->_redirect ( $this->view->myController . '/list' );
	}

	
	// -------------------------------------------------------------------------------
	// Data Assimilation Controllers
	// -------------------------------------------------------------------------------
	public function generateAction(){
//		echo "classification action reached"; exit;

		$dao = new ADMIN_DAO_ACL_UserPackage();
		// Recreate Table?
		$dao->dropTable();
		$dao->createTable();
		
		$this->view->dataArray = $dao->generateTime();

//		var_dump ($this->view->dataArray);exit;

		exit;
	}
	

	
	// -------------------------------------------------------------------------------
	// Data Access Controllers
	// -------------------------------------------------------------------------------
	public function listAction(){
		parent::listAction();
		
		$tableNS = new Zend_Session_Namespace('TableList');
				
		$dao = new ADMIN_DAO_ACL_UserPackage();
		if (!$dao->ifExistTable()){
			echo "creating table";
			$dao->createTable();
			$dao->insertBaseData();
		}
		
		$search_query = $this->getRequest()->getParam('q', '');
		$page			= $this->getRequest()->getParam('p', 1);
		$page_size		= $this->getRequest()->getParam('ps', 10);
		$filter = $this->getRequest()->getParam('f', COMMON_DAO_Base::FILTER_NONE);
		$order = $this->getRequest()->getParam('o', COMMON_DAO_Base::ORDER_NONE);

		$form_options = null;
		$formData = array();
		$form = new ADMIN_Form_Admin_SearchBox($form_options, $formData);
		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			if ($form->isValid($formData)) {
//				echo 'success';exit;
				if (empty($search_query)){
					$search_query = $formData['q'];
				} elseif ($search_query <> $formData['q']) {
					$search_query = $formData['q'];
				}
			} else {
			}
		} else {
			$formData['q'] = $search_query;
		}
		$form->populate($formData);
		$this->view->searchbox = $form;
				
//		echo "Search :" . $search_query; exit;
		$this->view->page	= $page;
		$this->view->filter	= $filter;
		$this->view->order	= $order;
		$this->view->q		= $search_query;
		$this->view->id		= $id;

		$dao->flag_count			= true;
		$dao->filter				= $filter;
		$dao->order					= $order;
		$dao->search_query			= $search_query;
		
		$this->view->dataArrayCount = $dao->getDataList();
//		echo "Count :" . $this->view->dataArrayCount;
		$local_last_record = $this->view->dataArrayCount;
		$local_last_page = ceil( $local_last_record / $page_size);
		if (($page > $local_last_page) & ($local_last_page > 0)) {	$page = $local_last_page; }
		$local_table_start_record = intval($page - 1) * intval($page_size);
		$local_table_end_record   = $local_table_start_record + $page_size;

		$dao->flag_count			= false;
		$dao->limit_start			= $local_table_start_record;
		$dao->limit_offset			= $page_size;

		$this->view->last_page = $local_last_page;
		echo 'PS: ' . $page_size . 'P:' . $page  . 'SR:' . $local_table_start_record . '<br>';

		$this->view->dataArray = $dao->getDataList();
//		var_dump($this->view->dataArray);
		$this->view->tableMetaData = $dao->getTableMetaData();
//		$this->view->tableColumnNames = $dao->getTableColumnNames();
		$this->view->tableColumnNames = array('id', 'user_id', 'package_id', 'priority', 'creation_date', 'last_updated');

			if ($this->_request->isPost()) {
			$formData = $this->getRequest()->getPost(); // Not much use for manual field form management
			echo "Posted<br>";
			var_dump ($formData);
			
			$cbArray = explode(',', $this->getRequest()->getParam('cbArray', ''));
			//	echo $formData->getParam('cdArray');

			echo '<br><br><br>';
			var_dump ($cbArray);
			echo '<br>';
			foreach ($cbArray as $checkbox_id){
				if (!empty($checkbox_id)){
					echo "Check : " . $checkbox_id . '<br>';
					
					unset($actioncb_field_value);
					$actioncb_field = "actioncb_" . $checkbox_id;
					$actioncb_field_value = $this->getRequest()->getParam($actioncb_field, '');
					if ($actioncb_field_value == "on"){
						echo "Delete CB" . $actioncb_field_value . "<br>";
						// $dao->deleteData($checkbox_id);
					}
					
					$record = $dao->readData($checkbox_id);

//					$field = "user_id_" . $checkbox_id;
//					$field_value = $this->getRequest()->getParam($field, '');
//					if ($field_value <> '') {
//						$record['user_id'] = $field_value;
//					}
//					
//					$field = "package_id_" . $checkbox_id;
//					$field_value = $this->getRequest()->getParam($field, '');
//					if ($field_value <> '') {
//						$record['package_id'] = $field_value;
//					}
					
					$field = "priority_" . $checkbox_id;
					$field_value = $this->getRequest()->getParam($field, '');
					if ($field_value <> '') {
						$record['priority'] = $field_value;
					}
					
										
					if (empty($actioncb_field_value)){ // In case the record got deleted, we don't need to update.
						$dao->updateData($record, $checkbox_id);
					}
					
					echo 'Query:' . $search_query . "<br>";
				}				
			}
			$redirect_string  = $this->view->myController . '/' . $this->view->myAction;
						if (!empty($page)){
				echo 'Page:' . $page;
				$redirect_string .= '/p/' . $page;
			}
			if (!empty($filter)){
				echo 'Filter:' . $filter;
				$redirect_string .= '/f/' . $filter;
			}
			if (!empty($order)){
				echo 'Order:' . $order;
				$redirect_string .= '/o/' . $order;
			}
			if (!empty($search_query)){
				echo 'Query:' . $search_query;
				$redirect_string .= '/q/' . $search_query;
			}
			
			// TODO Save search query to session?
			
			echo "Redirect: " . $redirect_string;
//			exit;
			$this->_redirect ($redirect_string);
		}		
		
		
//		$paginator = Zend_Paginator::factory($this->view->dataArray);
////		$adapter = new Zend_Paginator_Adapter_DbSelect($dao->db->select()->from('dbo.location'));
////		$paginator = new Zend_Paginator($adapter);
//		$paginator->setItemCountPerPage(10);
//		$paginator->setCurrentPageNumber($page);
//		$this->view->paginator=$paginator;

		$this->view->addHelperPath('ADMIN/View/Helper/Admin', 'ADMIN_View_Helper_Admin');
		$this->view->addHelperPath('ADMIN/View/Helper/Admin/ACL/User/', 'ADMIN_View_Helper_Admin_ACL_User');
		$this->view->addHelperPath('ADMIN/View/Helper/Admin/ACL/Package/', 'ADMIN_View_Helper_Admin_ACL_Package');
//		exit;
		
		
	}


	public function addAction(){

		// Manage Form Display
		$form = new ADMIN_Form_Admin_ACL_UserPackage_Add();
		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			if ($form->isValid($formData)) {
				echo 'success';
//				var_dump ($formData); exit;
				$dao = new ADMIN_DAO_ACL_UserPackage();
				$dao->insertData($formData);
				$this->_redirect ( $this->view->myController . '/list' );
			} else {
				$form->populate($formData);
			}
		}
		$this->view->form = $form;

		//		var_dump ($this->view); exit;

	}

	
	public function viewAction(){
		$id = $this->getRequest()->getParam('id', 1);

		$dao = new ADMIN_DAO_ACL_UserPackage();
		//	$this->view->dataArray = $dao->getDataList();
		$this->view->readArray = $dao->readData($id);

		$this->view->tableMetaData = $dao->getTableMetaData();
		$this->view->tableColumnNames = $dao->getTableColumnNames();
	}

	
	/**
	 * Shows list of Objects associated with this user
	 * @return unknown_type
	 */
	public function viewobjectsAction(){
		parent::listAction();

		$id = $this->getRequest()->getParam('id', 1);
		$dao = new ADMIN_DAO_ACL_UserObject();
		if (!$dao->ifExistTable()){
			echo "creating table";
			$dao->createTable();
			$dao->insertBaseData();
		}
		
		$search_query = $this->getRequest()->getParam('q', '');
		$page = $this->getRequest()->getParam('p', 1);
		$filter = $this->getRequest()->getParam('f', COMMON_DAO_Base::FILTER_NONE);
		$order = $this->getRequest()->getParam('o', COMMON_DAO_Base::ORDER_NONE);

		$this->view->dataArray = $dao->getDataListByUserID($id);
		$this->view->tableMetaData = $dao->getTableMetaData();
		$this->view->tableColumnNames = $dao->getTableColumnNames();

		$paginator = Zend_Paginator::factory($this->view->dataArray);
//		$adapter = new Zend_Paginator_Adapter_DbSelect($dao->db->select()->from('dbo.location'));
//		$paginator = new Zend_Paginator($adapter);
		$paginator->setItemCountPerPage(10);
		$paginator->setCurrentPageNumber($page);
		$this->view->paginator=$paginator;
		
	}
	
	
	/**
	 * Shows list of Roles associated with this user
	 * @return unknown_type
	 */
	public function viewrolesAction(){
		parent::listAction();

		$id = $this->getRequest()->getParam('id', 1);
		$dao = new ADMIN_DAO_ACL_UserRole();
		if (!$dao->ifExistTable()){
			echo "creating table";
			$dao->createTable();
			$dao->insertBaseData();
		}
				
		
	}
	
	
	public function editAction(){

		$id = $this->getRequest()->getParam('id', 1);
		$form_options = null;
		$dao = new ADMIN_DAO_ACL_UserPackage();
		$form_data = $dao->readData($id);
		
		
		$form = new ADMIN_Form_Admin_ACL_UserPackage_Edit($form_options, $form_data);
		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			if ($form->isValid($formData)) {
				echo 'success';
//				var_dump ($formData);
//				var_dump ($formData); exit;
				$dao->updateData($id, $formData);
				$this->_redirect ( $this->view->myController . '/view/id/' . $id  );
			} else {
				$form->populate($formData);
			}
		}
		$this->view->id = $id;
		$this->view->form = $form;
		
		//		var_dump ($this->view); exit;

	}
	

	public function deleteAction(){
		// We should maintain a delete counter to check if excessive deletes are done?

		$id = $this->getRequest()->getParam('id', 1);
		$dao = new ADMIN_DAO_ACL_UserPackage();
		$dao->deleteData($id);
		$this->_redirect ( $this->view->myController . '/list' );

	}
	


	// -------------------------------------------------------------------------------
	// Table Management Controllers
	// -------------------------------------------------------------------------------
	// Should be admin-only
	public function droptableAction() {
		Zend_Registry::get('log_control')->debug('A/C :' . $this->view->myController . '/' . $this->view->myAction . ' reached');

		$dao = new ADMIN_DAO_ACL_UserPackage();
				$dao->dropTable();
//		echo "drop Table"; exit;
		$this->_redirect ( $this->view->myController . '/list' );


	}

	public function createtableAction() {
		Zend_Registry::get('log_control')->debug('A/C :' . $this->view->myController . '/' . $this->view->myAction . ' reached');

		$dao = new ADMIN_DAO_ACL_UserPackage();
		$dao->createTable();
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
