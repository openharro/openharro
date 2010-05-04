<?php
/**
 * @author Terence
 */

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected $_config;


	

	
	/**
	 * Debug Functions
	 *
	 */
	protected function _initDebug()
	{

		//Level 1 - Basic Debug
		ini_set('display_errors', 1);
		ini_set('error_reporting', E_ALL);
		error_reporting(E_ALL);

		//Level 2  - Trace Debug - output to screen
		//Level 2a - Trace Debug - output to screen + SQL debug
		//Level 3  - Trace Debug  - output to log


	}// end _initDebug


	/**
	 * Iniitalize Autoloading functions
	 * - in 1.8 ZF now uses namespaces.
	 * @author Terence Chia
	 */
	protected function _initAutoloader(){
		$loader = Zend_Loader_Autoloader::getInstance();
		$loader->registerNamespace('ADMIN_');
		$loader->registerNamespace('COMMON_');
		$loader->registerNamespace('GD_');

		// If we don have Namespaces set esp. when Models dont have namespaces set.
		//		$loader->setFallbackAutoloader(true);
		//$loader->suppressNotFoundWarnings(false);

	}


	protected function _initConfig()
	{
		$this->_config = $this->getOptions();
//				echo "config reached";
//				var_dump ($this->_config); exit;


	}





	/**
	 * _initRegistry - setups the Registry
	 * - db connectors loaded in _initDB
	 * - env = development or production
	 * -
	 * @return unknown_type
	 */
	protected function _initRegistry()
	{
		//		$this->bootstrap('db');
		//		Zend_Registry::set('dbResource', $this->getPluginResource('db'));
		//		var_dump (Zend_Registry::get('dbResource'));

		//		$this->_config = $this->getOptions();

		// Register GLOBALs via registry
		if (!empty($this->_config)){
//			echo "Setting config in registry";
//			echo $this->_config['defines']['upload_dir'];
			if (!empty($this->_config['defines']['site_id'])) Zend_Registry::set('site_id', $this->_config['defines']['site_id']);
			if (!empty($this->_config['defines']['server_id'])) Zend_Registry::set('server_id', $this->_config['defines']['server_id']);
			if (!empty($this->_config['defines']['images_dir'])) Zend_Registry::set('images_dir', $this->_config['defines']['images_dir']);
			if (!empty($this->_config['defines']['upload_dir'])) Zend_Registry::set('upload_dir', $this->_config['defines']['upload_dir']);
			if (!empty($this->_config['defines']['file_dir'])) Zend_Registry::set('file_dir', $this->_config['defines']['file_dir']);
		}

//		echo Zend_Registry::get('upload_dir'); exit;
//		Zend_Registry::set('config', $this->_config);
//		Zend_Registry::set('env', APPLICATION_ENV);

//		$config = $this->getResource('config');
//		$frontcontroller = $this->getResource('frontcontroller');


	}
	

	protected function _initLog(){
		
		// TODO Don't really need log config in Registry. Delete?
		if (!empty($this->_config)){
			if (!empty($this->_config['log']['log_dir'])) Zend_Registry::set('log_dir', $this->_config['log']['log_dir']);
			if (!empty($this->_config['log']['control'])) Zend_Registry::set('log_control', $this->_config['log']['control']);
			if (!empty($this->_config['log']['db'])) Zend_Registry::set('log_db', $this->_config['log']['db']);
		

			if (!empty($this->_config['log']['base'])){
				$base_log_writer = new Zend_Log_Writer_Stream($this->_config['log']['log_dir'] . $this->_config['log']['base']);
				$base_log = new Zend_Log();
				$base_log->addWriter($base_log_writer);
				Zend_Registry::set('log_base', $base_log);
			}			
			if (!empty($this->_config['log']['control'])){
				$control_log_writer = new Zend_Log_Writer_Stream($this->_config['log']['log_dir'] . $this->_config['log']['control']);
				$control_log = new Zend_Log();
				$control_log->addWriter($base_log_writer);
				$control_log->addWriter($control_log_writer);
				Zend_Registry::set('log_control', $control_log);	
			}

			if (!empty($this->_config['log']['db'])){
				$db_log_writer = new Zend_Log_Writer_Stream($this->_config['log']['log_dir'] . $this->_config['log']['db']);
				$db_log = new Zend_Log();
				$db_log->addWriter($base_log_writer);
				$db_log->addWriter($db_log_writer);
				Zend_Registry::set('log_db', $db_log);	
			}

			if (!empty($this->_config['log']['web'])){
				$web_log_writer = new Zend_Log_Writer_Stream($this->_config['log']['log_dir'] . $this->_config['log']['web']);
				$web_log = new Zend_Log();
				$web_log->addWriter($base_log_writer);
				$web_log->addWriter($web_log_writer);
				Zend_Registry::set('log_web', $web_log);	
			}
			
			if (!empty($this->_config['log']['security'])){
				$security_log_writer = new Zend_Log_Writer_Stream($this->_config['log']['log_dir'] . $this->_config['log']['security']);
				$security_log = new Zend_Log();
				$security_log->addWriter($base_log_writer);
				$security_log->addWriter($security_log_writer);
				Zend_Registry::set('log_security', $security_log);	
			}
			
			
			// TODO Consider the Mapping log

		}
	}


	/**
	 * Initialise and Register DB Connections
	 *
	 */
	protected function _initDB()
	{
		//		echo "initDB reached";

		if (!empty($this->_config)){
			$databases = $this->_config['database'];
			//		var_dump ($database);
			$data = new COMMON_DAO_Base(); 
			foreach ($databases as $database_name => $database) {
//					var_dump ($database_name);
//					var_dump ($database);
//					exit;
				// TODO We convert the array into a config object for now until COMMON_DAO_Base is ready to migrate.
				$database_config = new Zend_Config($database);
				$data->setDB($database_name, $database['adapter'], $database_config);
			}
		}
		//		exit;
		//		echo "end initDB<BR>";
	}

	
	protected function _initLayout()
	{
		// start layout MVC
		$layout = Zend_Layout::startMvc(array(
                                    	'layoutPath' => '../application/layouts',
                                    	'layout'     => 'frontpage'
                                    	));

	}// end setLayout


	protected function _initView(){
		$view = new Zend_View();
		$view->addHelperPath("ZendX/JQuery/View/Helper", "ZendX_JQuery_View_Helper");
		$viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
		$viewRenderer->setView($view);
		Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);		
		
		
//		$view->addHelperPath('GIST/View/Helper/', 'GIST_View_Helper');
	}



	protected function _initSession(){
		session_save_path('../application/sessions');

		//		ini_set('session.save_path');
		//		Zend_Session::setOptions($this->_config->toArray());
	}

	
	protected function _initCache(){
		
		if (!empty($this->_config)){
			if (!empty($this->_config['defines']['application_cache_dir'])) Zend_Registry::set('application_cache_dir', realpath(dirname(__FILE__) . $this->_config['defines']['application_cache_dir']));
			if (!empty($this->_config['defines']['public_cache_dir'])) Zend_Registry::set('public_cache_dir', realpath(dirname(__FILE__) . $this->_config['defines']['public_cache_dir'])); // Used by CDN
		}
	}
	
	
	
	
	public function run()
	{
		//      echo "Bootstrap execution";
		//      var_dump ($this->bootstrapDb());

		parent::run();
		//      exit;
	}



}

