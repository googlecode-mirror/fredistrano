<?php
/* SVN FILE: $Id$ */
/**
	* Controller that interacts with deployment log files
	*
	* PHP 5
	*
	* Licensed under The MIT License
	* Redistributions of files must retain the above copyright notice.
	*
	* @filesource
	* @link			http://code.google.com/p/fredistrano
* @package			app
	* @subpackage		app.controllers
	* @version			$Revision$
	* @modifiedby		$Author$
	* @lastmodified	$Date$
	* @license			http://www.opensource.org/licenses/mit-license.php The MIT License
*/
/**
	* Controller that interacts with deployment log files
	*
	* @package		app
	* @subpackage	app.controllers
	*/
class DeploymentLogsController extends AppController {

	var $name = 'DeploymentLogs';

	var $helpers = array ('Text');

	var $uses = array ('DeploymentLog','Profile','Project');

	var $authLocal = array (
		'DeploymentLogs'	=> 	array( 'entrance' ),
		'except' 	=> 	array(
			'index' 		=> 	array( 'public' )
		)
	);

	function beforeRender() {
		parent::beforeRender();

		// Tableau de lien pour la création du menu contextuel
		$tab[] = array (
			'text' => 'Actions'
		);
		if ($this->action != 'index') {
			$tab[] = array (
				'text' => __('Display full history', true),
				'link' => '/deploymentLogs'
			);
		}
		$tab[] = array (
			'text' => __('List projects', true),
			'link' => '/projects'
		);
		if ($this->Session->read('User')) {
			if ( Configure::read('Feeds.enabled') === true ) {
				$tab[] = array (
					'text' => __('Rss Feed', true),
					'link' => '/deploymentLogs/index.rss?token='.$this->Session->read('User.Profile.rss_token')
				);
			}
		}
		// On passe le tableau de lien dans la variable links pour l'élément context_menu.thtml
		$this->set("context_menu", $tab);
	} // beforeRender

	// Available actions ----------------------------------------------------------------------------------
	/**
	* List all logs
	*/
	function index($op = null, $id = null) {
		$limit = null;
		if ($this->RequestHandler->isRss()) {
			Configure::write('debug', 0);
			$limit = 50;

			if (!Configure::read('Feeds.enabled')) {
				$this->Session->setFlash(__('Feeds not enabled. To use them, activate them first in the config file.', true));
				$this->redirect('/deploymentLogs');
				exit();
			}

			if (!$this->Profile->find('count', array ('conditions' => array('rss_token' => $this->params['url']['token'])))) {
				$this->Session->setFlash(__("Rss token not found in database.", true));
				$this->redirect('/projects/index');
				exit();
			}
		} else {
			$this->Aclite->checkAccess(array('entrance'));
		}

		if (isset($this->data['Log']['project_id'])) {
			$op = 'project';
			$id = $this->data['Log']['project_id'];
		}

		$this->set('projects', $this->Project->find('list'));
		$archived = $this->_archive();
		if ($archived > 0){
			$this->Session->setFlash(sprintf(__("%d logs have been archived", true),$archived));
		}

		switch ($op) {
			case null :
				$this->_listAll($limit);
				break;
			case 'person' :
				$this->_listByPerson($id);
				break;
			case 'project' :
				$this->_listByProject($id);
				break;
			default :
				$this->Session->setFlash(__('Unsupported action ', true) . $op);
				$this->redirect('/deploymentLogs/list_all');
				break;
		} // switch
	} // index

	/**
	* View properties of a specified log
	* @param string $id ID of the log to be viewed
	*/
	function view($id) {
		if (!$id or !($deployLog = $this->DeploymentLog->read(null, $id)) ) {
			$this->Session->setFlash(__('Invalid id', true));
			$this->redirect('/deploymentLogs/list_all');
		}
		$output = null;
		$options = array(
			'reverse'			=>	false,
			'logPath'			=> F_DEPLOYLOGDIR.$deployLog['DeploymentLog']['uuid'].'.xml'
			);
		if ($deployLog['DeploymentLog']['archive']) {
			$this->set('error', 	__('No details available for archived logs.', true) );
		} else {
			$output = $this->Project->readAssociatedLog($deployLog['DeploymentLog']['project_id'], $options);
			if ( $output === false ) {
				$this->set('error', 	$this->Project->lastReadError);
			} else {
				$this->set('project', 	$this->Project->read(null, $this->data['Search']['project_id']));
				$this->set('size', 		$this->Project->lastReadSize);
				$this->set('logPath',	$options['logPath']);
			}
		}			
		$this->set('log',	 	$output);
		$this->set('deployLog', $deployLog);
		$this->set('project', 	$this->Project->read(null, $this->data['Search']['project_id']));
	} // view

	// Private functions ----------------------------------------------------------------------------------
	/**
	* Private function for archiving old logs
	*/
	private function _archive() {
		$oldTime = time() - Configure::read('Log.archiveDate');
		return $this->DeploymentLog->archive($oldTime);
	}// _archive

	/**
	* Private function for deleting of all logs
	*/
	private function _reset() {
		$this->DeploymentLog->delAll();

		// Afichage
		$this->Session->setFlash(__('All logs deleted', true));
		$this->redirect('/deploymentLogs/list_all');
	}//_ reset

	/**
	* Private function for listing logs
	*/
	private function _listAll($limit = null) {
		$filter = array();
		$conditions = '';
		if (!isset($this->params['url']['showArchived'])) {
			$conditions = 'archive=0';
		}

		$fields = array (
			'id',
			'project_id',
			'user_id',
			'created',
			'comment',
			'User.email',
			'User.login',
			'User.first_name',
			'User.last_name',
			'Project.name'
			);
		$order = 'DeploymentLog.created DESC';
		$logs = $this->DeploymentLog->findAll($conditions, $fields, $order,$limit);
		$this->set('filter', $filter);
		$this->set('logs', $logs);
	}// _listAll

	/**
	* Private function for listing logs associated to a project
	*
	* @param string $id ID of the project
	*/
	private function _listByProject($id = null) {

		if ($id && !$this->Project->read(null, $id)) {
			$this->Session->setFlash(__('Invalid id', true));
		}

		$conditions = '1=1';
		if ($id) {
			$conditions .= ' AND DeploymentLog.project_id = ' . $id;
		}
		if (!isset($this->params['url']['showArchived']) && (!isset($this->data['Log']['showArchived']) || $this->data['Log']['showArchived'] == 0)){
			$conditions .= ' AND archive=0';
		}
		$fields = array (
			'id',
			'project_id',
			'user_id',
			'created',
			'comment',
			'User.email',
			'User.login',
			'User.first_name',
			'User.last_name',
			'Project.name'
			);
		$order = 'DeploymentLog.created DESC';
		$logs = $this->DeploymentLog->findAll($conditions, $fields, $order);

		$this->set('logs', $logs);
	} // _listByProject

} // DeploymentLogs
?>