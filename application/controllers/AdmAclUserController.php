<?php
/**
 * Admin Admin Acl User Role Controller
 *
 * @author Terence
 * @package administration
 * @version 3.0
 *
 */
class AdmAclUserController extends GD_Controller_Base
{
	// TODO : Consider extending GD_Controller_Admin class soon.

	public function init(){
		parent::init();

		$this->getHelper('layout')->setLayout('administration');

		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/global_menu.css');
		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/global_menu.js');

		$this->checkAdmin();

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

		$dao = new ADMIN_DAO_ACL_User();
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
		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/jquery-1.3.2.min.js');
		parent::listAction();

		$tableNS = new Zend_Session_Namespace('TableList');

		$this->view->headScript()->appendScript($this->jsjQuery_List());


		$dao = new ADMIN_DAO_ACL_User();
		if (!$dao->ifExistTable()){
			echo "creating table";
			$dao->createTable();
			$dao->insertBaseData();
		}

		$search_query	= $this->getRequest()->getParam('q'	, '');
		$page			= $this->getRequest()->getParam('p'	, 1);
		$page_size		= $this->getRequest()->getParam('ps', 10);
		$filter			= $this->getRequest()->getParam('f'	, COMMON_DAO_Base::FILTER_NONE);
		$order			= $this->getRequest()->getParam('o'	, COMMON_DAO_Base::ORDER_NONE);

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
		$this->view->tableColumnNames = array('id', 'login', 'password', 'title', 'fullname', 'firstname', 'familyname', 'initials', 'photo_path', 'website', 'package_id', 'status', 'language_id', 'industry_id', 'invitations', 'credit', 'last_login_date', 'creation_date', 'last_updated');

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

		$this->view->addHelperPath('ADMIN/View/Helper/Admin', 'ADMIN_View_Helper_Admin');
//		$this->view->addHelperPath('ADMIN/View/Helper/Admin/Acl/User', 'ADMIN_View_Helper_Admin_Acl_User');
		$this->view->addHelperPath('ADMIN/View/Helper/Admin/Dim/Industry', 'ADMIN_View_Helper_Admin_Dim_Industry');
		$this->view->addHelperPath('ADMIN/View/Helper/Admin/Dim/Language', 'ADMIN_View_Helper_Admin_Dim_Language');

	}


	public function addAction(){
		$nodeNS = new Zend_Session_Namespace('Node');
		var_dump($nodeNS->history); exit;
		// Manage Form Display
		$form = new ADMIN_Form_Admin_ACL_User_Add();
		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			if ($form->isValid($formData)) {
				echo 'success';
//				var_dump ($formData); exit;
				$dao = new ADMIN_DAO_ACL_User();
				$dao->insertData($formData);
				$this->_redirect ( $this->view->myController . '/list' );
			} else {
				$form->populate($formData);
			}
		}
		$this->view->form = $form;

		//		var_dump ($this->view); exit;

	}

	public function addroleAction(){
		$nodeNS = new Zend_Session_Namespace('Node');
		var_dump($nodeNS->history); exit;
		// Manage Form Display
		$form = new ADMIN_Form_Admin_ACL_User_Add();
		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			if ($form->isValid($formData)) {
				echo 'success';
//				var_dump ($formData); exit;
				$dao = new ADMIN_DAO_ACL_User();
				$dao->insertData($formData);
				$this->_redirect ( $this->view->myController . '/list' );
			} else {
				$form->populate($formData);
			}
		}
		$this->view->form = $form;

		//		var_dump ($this->view); exit;

	}

	public function addobjectAction(){
		$nodeNS = new Zend_Session_Namespace('Node');
		var_dump($nodeNS->history); exit;
		// Manage Form Display
		$form = new ADMIN_Form_Admin_ACL_User_Add();
		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			if ($form->isValid($formData)) {
				echo 'success';
//				var_dump ($formData); exit;
				$dao = new ADMIN_DAO_ACL_User();
				$dao->insertData($formData);
				$this->_redirect ( $this->view->myController . '/list' );
			} else {
				$form->populate($formData);
			}
		}
		$this->view->form = $form;

		//		var_dump ($this->view); exit;
	}


	public function addpackageAction(){
		$nodeNS = new Zend_Session_Namespace('Node');
		var_dump($nodeNS->history); exit;
		// Manage Form Display
		$form = new ADMIN_Form_Admin_ACL_User_Add();
		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			if ($form->isValid($formData)) {
				echo 'success';
//				var_dump ($formData); exit;
				$dao = new ADMIN_DAO_ACL_User();
				$dao->insertData($formData);
				$this->_redirect ( $this->view->myController . '/list' );
			} else {
				$form->populate($formData);
			}
		}
		$this->view->form = $form;

		//		var_dump ($this->view); exit;
	}

	public function addpreferenceAction(){
		$nodeNS = new Zend_Session_Namespace('Node');
		var_dump($nodeNS->history); exit;
		// Manage Form Display
		$form = new ADMIN_Form_Admin_ACL_User_Add();
		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			if ($form->isValid($formData)) {
				echo 'success';
//				var_dump ($formData); exit;
				$dao = new ADMIN_DAO_ACL_User();
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
		parent::init();

		$this->getHelper('layout')->setLayout('administration');

		$id = $this->getRequest()->getParam('id', 1);

		$dao = new ADMIN_DAO_ACL_User();
		//	$this->view->dataArray = $dao->getDataList();
		$this->view->readArray = $dao->readData($id);

		$this->view->tableMetaData = $dao->getTableMetaData();
		$this->view->tableColumnNames = $dao->getTableColumnNames();
	}



	public function viewpasswordAction(){

		$id = $this->getRequest()->getParam('id', 1);
		$user_id = $id;
//		echo $user_id; exit;

		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/jquery-1.3.2.min.js');
//		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/jquery-ui-1.7.2.custom.min.js');
		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/jquery.md5.js');

//		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/smoothness/jquery-ui-1.7.2.custom.css');
		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/global_menu.js');
		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/application.css');
		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/tabs.css');

		$this->view->headScript()->appendScript($this->jsjqViewPasswordAction($user_id));


//		$id = $this->getRequest()->getParam('id', 1);
		$form_options = null;
		$dao = new ADMIN_DAO_ACL_User();
		$form_data = $dao->readData($id);


		$form = new ADMIN_Form_Admin_ACL_User_PasswordUpdate($form_options, $form_data);
//		$form->setAction($this->view->myController . '/viewpassword/id/' . $id);
		$form->setMethod('post');
		$form->addAttribs(array( 'id' => 'acl_user_password')); 		// used for ajaxlogin
		$form->addAttribs(array( 'onSubmit' => 'return false', )); 	// used for ajaxlogin

		$this->view->id = $id;
		$this->view->form = $form;

	}


	public function viewpasswordchangesuccessAction() {

		$id = $this->getRequest()->getParam('id', 1);
		$user_id = $id;
//		echo $user_id; exit;

		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/jquery-1.3.2.min.js');
//		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/jquery-ui-1.7.2.custom.min.js');
		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/jquery.md5.js');

//		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/smoothness/jquery-ui-1.7.2.custom.css');
		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/global_menu.js');
		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/application.css');
		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/tabs.css');

		$this->view->headScript()->appendScript($this->jsjqViewPasswordAction($user_id));


//		$id = $this->getRequest()->getParam('id', 1);
		$form_options = null;
		$dao = new ADMIN_DAO_ACL_User();
		$form_data = $dao->readData($id);


		$form = new ADMIN_Form_Admin_ACL_User_PasswordUpdate($form_options, $form_data);
//		$form->setAction($this->view->myController . '/viewpassword/id/' . $id);
		$form->setMethod('post');
		$form->addAttribs(array( 'id' => 'acl_user_password')); 		// used for ajaxlogin
		$form->addAttribs(array( 'onSubmit' => 'return false', )); 	// used for ajaxlogin

		$this->view->id = $id;
		$this->view->form = $form;



	}

	public function viewpasswordoriginalfailAction() {

		$id = $this->getRequest()->getParam('id', 1);
		$user_id = $id;
//		echo $user_id; exit;

		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/jquery-1.3.2.min.js');
//		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/jquery-ui-1.7.2.custom.min.js');
		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/jquery.md5.js');

//		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/smoothness/jquery-ui-1.7.2.custom.css');
		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/global_menu.js');
		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/application.css');
		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/tabs.css');

		$this->view->headScript()->appendScript($this->jsjqViewPasswordAction($user_id));


//		$id = $this->getRequest()->getParam('id', 1);
		$form_options = null;
		$dao = new ADMIN_DAO_ACL_User();
		$form_data = $dao->readData($id);


		$form = new ADMIN_Form_Admin_ACL_User_PasswordUpdate($form_options, $form_data);
//		$form->setAction($this->view->myController . '/viewpassword/id/' . $id);
		$form->setMethod('post');
		$form->addAttribs(array( 'id' => 'acl_user_password')); 		// used for ajaxlogin
		$form->addAttribs(array( 'onSubmit' => 'return false', )); 	// used for ajaxlogin

		$this->view->id = $id;
		$this->view->form = $form;


	}

	public function viewpasswordtryagainAction(){

		$id = $this->getRequest()->getParam('id', 1);
		$user_id = $id;
//		echo $user_id; exit;

		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/jquery-1.3.2.min.js');
//		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/jquery-ui-1.7.2.custom.min.js');
		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/jquery.md5.js');

//		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/smoothness/jquery-ui-1.7.2.custom.css');
		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/global_menu.js');
		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/application.css');
		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/tabs.css');

		$this->view->headScript()->appendScript($this->jsjqViewPasswordAction($user_id));


//		$id = $this->getRequest()->getParam('id', 1);
		$form_options = null;
		$dao = new ADMIN_DAO_ACL_User();
		$form_data = $dao->readData($id);


		$form = new ADMIN_Form_Admin_ACL_User_PasswordUpdate($form_options, $form_data);
//		$form->setAction($this->view->myController . '/viewpassword/id/' . $id);
		$form->setMethod('post');
		$form->addAttribs(array( 'id' => 'acl_user_password')); 		// used for ajaxlogin
		$form->addAttribs(array( 'onSubmit' => 'return false', )); 	// used for ajaxlogin

		$this->view->id = $id;
		$this->view->form = $form;

	}



	/**
	 * Shows list of Objects associated with this user
	 * @return unknown_type
	 */
	public function viewobjectsAction(){
		parent::listAction();

		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/jquery-1.3.2.min.js');
		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/jquery-ui-1.7.2.custom.min.js');

		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/smoothness/jquery-ui-1.7.2.custom.css');
		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/global_menu.js');
		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/application.css');
		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/tabs.css');


		$id = $this->getRequest()->getParam('id', 1);
		$dao = new ADMIN_DAO_ACL_UserObject();
		if (!$dao->ifExistTable()){
			echo "creating table";
			$dao->createTable();
			$dao->insertBaseData();
		}

		$search_query	= $this->getRequest()->getParam('q'	, '');
		$page			= $this->getRequest()->getParam('p'	, 1);
		$page_size		= $this->getRequest()->getParam('ps', 10);
		$filter			= $this->getRequest()->getParam('f'	, COMMON_DAO_Base::FILTER_NONE);
		$order			= $this->getRequest()->getParam('o'	, COMMON_DAO_Base::ORDER_NONE);

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
		$dao->user_id				= $id;

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
		$this->view->tableColumnNames = $dao->getTableColumnNames();

		$paginator = Zend_Paginator::factory($this->view->dataArray);
//		$adapter = new Zend_Paginator_Adapter_DbSelect($dao->db->select()->from('dbo.location'));
//		$paginator = new Zend_Paginator($adapter);
		$paginator->setItemCountPerPage(10);
		$paginator->setCurrentPageNumber($page);
		$this->view->paginator=$paginator;
		$this->view->id = $id;


		$this->view->addHelperPath('ADMIN/View/Helper/Admin', 'ADMIN_View_Helper_Admin');
		$this->view->addHelperPath('ADMIN/View/Helper/Admin/Acl/User', 'ADMIN_View_Helper_Admin_Acl_User');
		$this->view->addHelperPath('ADMIN/View/Helper/Admin/Acl/Role', 'ADMIN_View_Helper_Admin_Acl_Role');
		$this->view->addHelperPath('ADMIN/View/Helper/Admin/Acl/Object/', 'ADMIN_View_Helper_Admin_Acl_Object');
		$this->view->addHelperPath('ADMIN/View/Helper/Admin/Acl/Right', 'ADMIN_View_Helper_Admin_Acl_Right');


	}


	/**
	 * viewrolesAction()
	 * -
	 * Shows list of Roles associated with this user
	 * @return unknown_type
	 */
	public function viewrolesAction(){
		parent::listAction();

		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/jquery-1.3.2.min.js');
		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/jquery-ui-1.7.2.custom.min.js');

		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/smoothness/jquery-ui-1.7.2.custom.css');
		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/global_menu.js');
		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/application.css');
		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/tabs.css');

		$id = $this->getRequest()->getParam('id', 1);
		$dao = new ADMIN_DAO_ACL_UserRole();
		if (!$dao->ifExistTable()){
			echo "creating table";
			$dao->createTable();
			$dao->insertBaseData();
		}

		$search_query	= $this->getRequest()->getParam('q'	, '');
		$page			= $this->getRequest()->getParam('p'	, 1);
		$page_size		= $this->getRequest()->getParam('ps', 10);
		$filter			= $this->getRequest()->getParam('f'	, COMMON_DAO_Base::FILTER_NONE);
		$order			= $this->getRequest()->getParam('o'	, COMMON_DAO_Base::ORDER_NONE);

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
		$dao->user_id				= $id;

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
		$this->view->tableColumnNames = $dao->getTableColumnNames();

		$paginator = Zend_Paginator::factory($this->view->dataArray);
//		$adapter = new Zend_Paginator_Adapter_DbSelect($dao->db->select()->from('dbo.location'));
//		$paginator = new Zend_Paginator($adapter);
		$paginator->setItemCountPerPage(10);
		$paginator->setCurrentPageNumber($page);
		$this->view->paginator=$paginator;

		$this->view->addHelperPath('ADMIN/View/Helper/Admin', 'ADMIN_View_Helper_Admin');
		$this->view->addHelperPath('ADMIN/View/Helper/Admin/Acl/User', 'ADMIN_View_Helper_Admin_Acl_User');
		$this->view->addHelperPath('ADMIN/View/Helper/Admin/Acl/Role', 'ADMIN_View_Helper_Admin_Acl_Role');
		$this->view->addHelperPath('ADMIN/View/Helper/Admin/Acl/Object/', 'ADMIN_View_Helper_Admin_Acl_Object');
		$this->view->addHelperPath('ADMIN/View/Helper/Admin/Acl/Right', 'ADMIN_View_Helper_Admin_Acl_Right');

	}


	/**
	 * Shows list of Preferences associated with this user
	 * @return unknown_type
	 */
	public function viewpreferencesAction(){
		parent::listAction();

		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/jquery-1.3.2.min.js');
		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/jquery-ui-1.7.2.custom.min.js');

		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/smoothness/jquery-ui-1.7.2.custom.css');
		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/global_menu.js');
		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/application.css');
		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/tabs.css');

		$id = $this->getRequest()->getParam('id', 1);
		$dao = new ADMIN_DAO_ACL_UserPreference();
		if (!$dao->ifExistTable()){
			echo "creating table";
			$dao->createTable();
			$dao->insertBaseData();
		}

		$search_query	= $this->getRequest()->getParam('q'	, '');
		$page			= $this->getRequest()->getParam('p'	, 1);
		$page_size		= $this->getRequest()->getParam('ps', 10);
		$filter			= $this->getRequest()->getParam('f'	, COMMON_DAO_Base::FILTER_NONE);
		$order			= $this->getRequest()->getParam('o'	, COMMON_DAO_Base::ORDER_NONE);

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
		$dao->user_id				= $id;

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
		$this->view->tableColumnNames = $dao->getTableColumnNames();

		$paginator = Zend_Paginator::factory($this->view->dataArray);
//		$adapter = new Zend_Paginator_Adapter_DbSelect($dao->db->select()->from('dbo.location'));
//		$paginator = new Zend_Paginator($adapter);
		$paginator->setItemCountPerPage(10);
		$paginator->setCurrentPageNumber($page);
		$this->view->paginator=$paginator;
		$this->view->id = $id;

		$this->view->addHelperPath('ADMIN/View/Helper/Admin', 'ADMIN_View_Helper_Admin');
		$this->view->addHelperPath('ADMIN/View/Helper/Admin/Acl/User', 'ADMIN_View_Helper_Admin_Acl_User');
		$this->view->addHelperPath('ADMIN/View/Helper/Admin/Acl/Preference', 'ADMIN_View_Helper_Admin_Acl_Preference');
		$this->view->addHelperPath('ADMIN/View/Helper/Admin/Acl/Object/', 'ADMIN_View_Helper_Admin_Acl_Object');
		$this->view->addHelperPath('ADMIN/View/Helper/Admin/Acl/Right', 'ADMIN_View_Helper_Admin_Acl_Right');

	}

	/**
	 * Shows list of Packages associated with this user
	 * @return unknown_type
	 */
	public function viewpackagesAction(){
		parent::listAction();

		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/jquery-1.3.2.min.js');
		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/jquery-ui-1.7.2.custom.min.js');

		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/smoothness/jquery-ui-1.7.2.custom.css');
		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/global_menu.js');
		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/application.css');
		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/tabs.css');


		$id = $this->getRequest()->getParam('id', 1);
		$dao = new ADMIN_DAO_ACL_UserPackage();
		if (!$dao->ifExistTable()){
			echo "creating table";
			$dao->createTable();
			$dao->insertBaseData();
		}

		$search_query	= $this->getRequest()->getParam('q'	, '');
		$page			= $this->getRequest()->getParam('p'	, 1);
		$page_size		= $this->getRequest()->getParam('ps', 10);
		$filter			= $this->getRequest()->getParam('f'	, COMMON_DAO_Base::FILTER_NONE);
		$order			= $this->getRequest()->getParam('o'	, COMMON_DAO_Base::ORDER_NONE);

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
		$dao->user_id				= $id;

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
		$this->view->tableColumnNames = $dao->getTableColumnNames();

		$paginator = Zend_Paginator::factory($this->view->dataArray);
//		$adapter = new Zend_Paginator_Adapter_DbSelect($dao->db->select()->from('dbo.location'));
//		$paginator = new Zend_Paginator($adapter);
		$paginator->setItemCountPerPage(10);
		$paginator->setCurrentPageNumber($page);
		$this->view->paginator=$paginator;
		$this->view->id = $id;

		$this->view->addHelperPath('ADMIN/View/Helper/Admin', 'ADMIN_View_Helper_Admin');
		$this->view->addHelperPath('ADMIN/View/Helper/Admin/Acl/User', 'ADMIN_View_Helper_Admin_Acl_User');
		$this->view->addHelperPath('ADMIN/View/Helper/Admin/Acl/Package', 'ADMIN_View_Helper_Admin_Acl_Package');

	}


	public function viewpaymentmethodsAction() {
		parent::listAction();

		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/jquery-1.3.2.min.js');
		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/jquery-ui-1.7.2.custom.min.js');

		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/smoothness/jquery-ui-1.7.2.custom.css');
		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/global_menu.js');
		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/application.css');
		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/tabs.css');

		$id = $this->getRequest()->getParam('id', 1);


	}

	public function viewtransactionsAction() {
		parent::listAction();

		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/jquery-1.3.2.min.js');
		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/jquery-ui-1.7.2.custom.min.js');

		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/smoothness/jquery-ui-1.7.2.custom.css');
		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/global_menu.js');
		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/application.css');
		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/tabs.css');


		$id = $this->getRequest()->getParam('id', 1);


		$search_query	= $this->getRequest()->getParam('q'	, '');
		$page			= $this->getRequest()->getParam('p'	, 1);
		$page_size		= $this->getRequest()->getParam('ps', 10);
		$filter			= $this->getRequest()->getParam('f'	, COMMON_DAO_Base::FILTER_NONE);
		$order			= $this->getRequest()->getParam('o'	, COMMON_DAO_Base::ORDER_NONE);

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



	}

	public function editAction(){

		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/jquery-1.3.2.min.js');
//		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/jquery-ui-1.7.2.custom.min.js');

//		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/smoothness/jquery-ui-1.7.2.custom.css');
		$this->view->headScript()->appendFile( $this->baseURL . '/web/js/global_menu.js');
		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/application.css');
		$this->view->headLink()->appendStylesheet( $this->baseURL . '/web/css/tabs.css');

		$this->view->headScript()->appendScript($this->jsjqEditAction());

		$id = $this->getRequest()->getParam('id', 1);
//		echo $id; exit;
		$form_options = null;
		$dao = new ADMIN_DAO_ACL_User();
		$form_data = $dao->readData($id);


		$form = new ADMIN_Form_Admin_ACL_User_Edit($form_options, $form_data);
		$form->setAction($this->view->myController . '/' . $this->view->myAction . '/id/' . $id);
		$form->setMethod('post');

		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			$formData['id'] = $form_data['id'];
			$formData['login'] = $form_data['login'];
			$formData['fullname'] = $form_data['fullname'];

//			var_dump ($formData); exit;
			if ($form->isValid($formData)) {
				echo 'success';
//				var_dump ($formData); exit;
				$dao->updateData($formData, $id);
				$this->_redirect ( $this->view->myController . '/edit/id/' . $id  );
			} else {
				$form->populate($formData);
			}
		}
		$this->view->id = $id;
		$this->view->form = $form;

		//		var_dump ($this->view); exit;





	}






	/**
	 * ajaxpasswordupdateAction()
	 * - saves the md5 version of password into database.
	 * @return unknown_type
	 */
	public function ajaxpasswordupdateAction(){
		$this->getHelper('layout')->disableLayout();

//		echo 'test';
		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();

			if (!empty($formData['user_id'])) {

				$dao = new ADMIN_DAO_ACL_User();
				$form_data = $dao->readData($formData['user_id']);



				if (!empty($formData['original_password'])) {
					if (strcmp($formData['original_password'], $form_data['password']) == 0){
						// Original Password is a correct

						if (strcmp($formData['new_password'], $formData['repeat_password']) == 0){
							// New passwords match

							// TODO :  Update the password
							$form_data['password'] = $formData['new_password'];
							$dao->updateData($form_data);

							echo '/adm-acl-user/viewpasswordchangesuccess';

						} else {
							echo '/adm-acl-user/viewpasswordtryagain';
						}


					} else {
						// Original Password does not match
						echo '/adm-acl-user/viewpasswordoriginalfail';
					}

	//				echo $formData['original_password'];
				} else {
					// Original Password not supplied
					echo '/adm-acl-user/viewpasswordoriginalfail';
				}

//				echo $formData['user_id'];
			}

		}
		exit;

	}


	public function deleteAction(){
		// We should maintain a delete counter to check if excessive deletes are done?

		$id = $this->getRequest()->getParam('id', 1);
		$dao = new ADMIN_DAO_ACL_User();
		$dao->deleteData($id);
		$this->_redirect ( $this->view->myController . '/list' );

	}


	// -------------------------------------------------------------------------------
	// Table Management Controllers
	// -------------------------------------------------------------------------------
	// Should be admin-only
	public function droptableAction() {
		Zend_Registry::get('log_control')->debug('A/C :' . $this->view->myController . '/' . $this->view->myAction . ' reached');

		$dao = new ADMIN_DAO_ACL_User();
		$dao->dropTable();
//		echo "drop Table"; exit;
		$this->_redirect ( $this->view->myController . '/list' );
	}

	public function createtableAction() {
		Zend_Registry::get('log_control')->debug('A/C :' . $this->view->myController . '/' . $this->view->myAction . ' reached');

		$dao = new ADMIN_DAO_ACL_User();
		$dao->createTable();
		echo "createTable"; exit;
		$this->_redirect ( $this->view->myController . '/list' );

	}


	/**
	 * checkAdmin()
	 * - checks if user has admin access.
	 * - first checks if user has the Administrator role
	 * - then checks fine-grain security rules.
	 * @return unknown_type
	 */
	protected function checkAdmin(){
		$front = Zend_Controller_Front::getInstance();
		$security_pass_flag = false; // Initially not allowed.
		$loginNS = new Zend_Session_Namespace('Login');


		if (!empty($loginNS->username)) {
			// Broad Security Access Roles
			foreach ($loginNS->acl_user_roles as $role){
	//			echo $role['role_id'] . ',';
				if ($role['role_id'] == 1){ // 1 = Administrator
					$security_pass_flag = true;
				}
			}

			// Fine Grain Security Access - Check Positives
			foreach ($loginNS->acl_user_objects as $object){
	//			echo $object['object_id'] . ',';
				if ($object['object_id'] == 1){ // 1 = Admin
					$security_pass_flag = true;
				}
			}

			// TODO : Fine Grain Security Access - Check Negatives


			if ($security_pass_flag == false) {
				$three_char_controller = substr($front->getRequest()->getControllerName(), 0, 3);
				// TODO First 3 chars of controller, or 'Admin'
				if ( ((in_array($front->getRequest()->getControllerName(), array('admin'))) || ($three_char_controller == 'Adm')) ) {
					$this->_redirect ( '/query/index' );
				}
			}
		}
	}




	// -------------------------------------------------------------------------------
	// Javascript Library
	// - eventually move to own classes?
	// -------------------------------------------------------------------------------


	public function jsjQuery_List(){

		$js[] = " $(document).ready(function() { ";
//		$js[] = "		$('#tabs').tabs(); ";
		$js[] = " }) ";

		$javascript = implode ($js);

		return stripslashes($javascript);

	}


	public function jsjqEditAction(){

		$js[] = " $(document).ready(function() { ";

//		$js[] = "	$('#tabs').tabs(); ";
//
////		$js[] = " 	$('#tabs').tabs().tabs('select', 2); "; // Success
//
//		$js[] = " 	$('#tabs').bind('tabsselect', function(event, ui) { ";
////		$js[] = " 	 	alert(ui.index); ";
//		$js[] = " 		if (ui.index == 1) { ";
//		$js[] = " 	 		document.location='/Adm-Acl-User/viewpassword/id/1'; ";
//
//		$js[] = " 		} ";
//		$js[] = "	}); ";

		$js[] = " 	$('#photo_path').bind('click', function() { ";
//		$js[] = " 		console.log(\"Original Password reached\"); ";
		$js[] = " 		alert ('Original password box clicked'); ";
		$js[] = " 	}); ";


		$js[] = " }) ";

		$javascript = implode ($js);

		return stripslashes($javascript);

	}


	public function jsjqViewPasswordAction($user_id){
		// Should this function be moved to the GD_Form_Login?

		$js[] = " $(document).ready(function() { ";

		$js[] = " 	$(\"#original_password\").focus(); ";

		$js[] = " 	$('#original_password').bind('click', function() { ";
//		$js[] = " 		console.log(\"Original Password reached\"); ";
//		$js[] = " 		alert ('Original password box clicked'); ";
		$js[] = " 	}); ";

		$js[] = " 	$('#new_password').bind('click', function() { ";
//		$js[] = " 		console.log(\"new password reached\"); ";
//		$js[] = " 		alert ('new_password  box clicked'); ";
		$js[] = " 	}); ";

		$js[] = " 	$('#repeat_password').bind('click', function() { ";
//		$js[] = " 		console.log(\"Repeat Password reached\"); ";
//		$js[] = " 		alert ('repeat pass box clicked'); ";
		$js[] = " 	}); ";


		$js[] = " 	$('#submit').bind('click', function() { ";
//		$js[] = " 		console.log(\"Submit clicked\"); ";
//		$js[] = " 		alert ('submit clicked'); ";

		//hide the errors/info (if form has already been submitted)
		$js[] = " 		$('.info').hide(); ";
		$js[] = " 		var errorFlag = false; ";

		$js[] = " 		var original_password = $('#original_password').val(); ";
		$js[] = " 		var new_password = $('#new_password').val(); ";
		$js[] = " 		var repeat_password = $('#repeat_password').val(); ";

		$js[] = " 		if (original_password == '') { ";
		$js[] = " 			$('#original_password').after('<br><span class=info style=\"color:#cc0000;\"> Please enter your current password</span>'); ";
		$js[] = " 			$(\"#original_password\").focus(); ";
		$js[] = " 			errorFlag = true; ";
		$js[] = " 		} ";


		$js[] = " 		if (new_password == '') { ";
		$js[] = " 			$('#new_password').after('<br><span class=info style=\"color:#cc0000;\"> Please enter your new password</span>'); ";
		$js[] = " 			if(errorFlag == false) { ";
		$js[] = " 				$(\"#new_password\").focus(); ";
		$js[] = " 			} ";
		$js[] = " 			errorFlag = true; ";
		$js[] = " 		} ";

		$js[] = " 		if(repeat_password == '') { ";
		$js[] = " 			$('#repeat_password').after('<br><span class=info style=\"color:#cc0000;\"> Please repeat your password</span>'); ";
		$js[] = " 			if(errorFlag == false) { ";
		$js[] = " 				$(\"#repeat_password\").focus(); ";
		$js[] = " 			} ";
		$js[] = " 			errorFlag = true; ";
		$js[] = " 		} ";

//		$js[] = " 		if(new_password <> repeat_password) { ";
//		$js[] = " 			$(\"#new_password\").focus(); ";
//		$js[] = " 			errorFlag = true; ";
//		$js[] = " 		} else { ";
//		$js[] = " 			repeat_password = $.md5(repeat_password); "; //turn the pwd into an md5 for transmit
//		$js[] = " 		} ";


		$js[] = " 		if(errorFlag == false) { ";
//		$js[] = " 		alert ('submit clicked'); ";
		if (!empty($user_id)){
			$js[] = " 			user_id  = " . $user_id . "; ";
		}
		$js[] = " 			original_password = $.md5(original_password); "; //md5 it for transmit
		$js[] = " 			new_password = $.md5(new_password); "; //md5 it for transmit
		$js[] = " 			repeat_password = $.md5(repeat_password); "; //md5 it for transmit

//		$js[] = " 		alert ('user_id:'); ";

//		$js[] = " 			$.post('/Adm-Acl-User/ajaxpasswordupdate') ";
//		$js[] = " 			$.post('/index/ajaxpasswordupdate', {original_password: original_password, repeat_password: repeat_password}, function(data) { ";

		$js[] = " 			$.post('/Adm-Acl-User/ajaxpasswordupdate/', {user_id : user_id, original_password: original_password, new_password: new_password, repeat_password: repeat_password}, function(data) { ";
//		$js[] = " 				alert ('post successful' + data); ";
		$js[] = " 				if(jQuery.trim(data) == '/adm-acl-user/viewpasswordchangesuccess') { ";
		$js[] = " 					document.location= '/adm-acl-user/viewpasswordchangesuccess'; ";
		$js[] = " 				} else if (jQuery.trim(data) == '/adm-acl-user/viewpasswordoriginalfail') { ";
		$js[] = " 					document.location= '/adm-acl-user/viewpasswordoriginalfail'; ";
		$js[] = " 				} else { ";
		$js[] = " 					document.location= '/adm-acl-user/viewpasswordtryagain'; ";
		$js[] = " 				} ";
		$js[] = " 			}); ";
		$js[] = " 		} else { ";
 		$js[] = " 			return false; "; // dont post form
		$js[] = " 		} ";
 		$js[] = " 	}); ";


		$js[] = " }) ";

		$javascript = implode ($js);

		return stripslashes($javascript);
	}


	// -------------------------------------------------------------------------------
	// CSS Library
	// -------------------------------------------------------------------------------



}

