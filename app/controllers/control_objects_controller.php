<?php
/* SVN FILE: $Id$ */
/**
 * Controller that provides access to some of the user management features
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
 * Controller that provides access to some of the user management features
 *
 * @package		app
 * @subpackage	app.controllers
 */
class ControlObjectsController extends AppController {

	var $name = 'ControlObjects';

	var $authLocal = array (
		'ControlObjects' => array (
			'authorizations'
		)
	);

	function beforeRender() {
		parent::beforeRender();
		// Tableau de liens pour la création du menu contextuel
		$tab[] = array (
			'text' => 'Actions'
		);
		if ($this->action != 'index')
			$tab[] = array (
				'text' => __('Control object list', true),
				'link' => '/control_objects/index'
			);
		if ($this->action != 'add')
			$tab[] = array (
				'text' => __('Add control object', true),
				'link' => '/control_objects/add'
			);
		$this->set('context_menu', $tab);
		parent :: beforeRender();
	}// beforeRender

	function index() {
		$data = $this->paginate('ControlObject');
		$this->set(compact('data'));
	}// index

	function view($id = null) {
		if (!$id or !$this->ControlObject->read(null, $id)) {
			$this->Session->setFlash(__('Invalid id.', true));
			$this->redirect('/control_objects/index');
			exit;
		}
		$this->set('controlObject', $this->ControlObject->read(null, $id));
	}// view

	function add() {
		if (empty ($this->data)) {
			$this->set('controlObjects', $this->ControlObject->find('list'));
		} else {
			if ($this->ControlObject->save($this->data)) {
				$this->Aclite->reloadsAcls('Aco');
				$this->Session->setFlash(__('The control object has been created.', true));
				$this->redirect('/control_objects/index');
				exit;
			} else {
				$this->Session->setFlash(__('Please correct errors below.', true));
				$this->set('controlObjects', $this->ControlObject->find('list'));
			}
		}
	}// add

	function edit($id = null) {
		if (empty ($this->data)) {
			if (!$id or !$this->ControlObject->read(null, $id)) {
				$this->Session->setFlash(__('Invalid id.', true));
				$this->redirect('/control_objects/index');
				exit;
			}
			$this->data = $this->ControlObject->read(null, $id);
			$this->set('controlObjects', $this->ControlObject->find('list'));
		} else {
			if ($this->ControlObject->save($this->data)) {
				$this->Aclite->reloadsAcls('Aco');
				$this->Session->setFlash(__('The control object has been updated.', true));
				$this->redirect('/control_objects/view/' . $id);
				exit;
			} else {
				$this->Session->setFlash(__('Please correct errors below.', true));
				$this->set('controlObjects', $this->ControlObject->find('list'));
			}
		}
	}// edit

	function delete($id = null) {
		if (!$id or !$this->ControlObject->read(null, $id)) {
			$this->Session->setFlash(__('Invalid id.', true));
			$this->redirect('/control_objects/index');
			exit;
		}
		if ($this->ControlObject->del($id)) {
			$this->Aclite->reloadsAcls('Aco');
			$this->Session->setFlash(__('The control object has been deleted.', true));
			$this->redirect('/control_objects/index');
			exit;
		} else {
			$this->Session->setFlash(__('Error during deletion.', true));
			$this->redirect('/control_objects/view/' . $id);
			exit;
		}
	}// delete
	
}// ControlObjectsController
?>