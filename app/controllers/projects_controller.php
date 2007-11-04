<?php
class ProjectsController extends AppController {

	var $name = 'Projects';
	var $helpers = array (
		'Html',
		'Form',
		'Ajax',
		'Pagination',
		'Error'
	);
	var $components = array (
		'Pagination'
	);

	var $uses = array (
		'Project',
		'DeploymentLog'
	);

	function beforeRender() {
		parent::beforeRender();
		
		// Tableau de liens pour la création du menu contextuel
		$tab[] = array (
			'text' => 'Actions'
		);

		if ($this->action != 'index')
			$tab[] = array (
				'text' => LANG_PROJECTSLIST,
				'link' => '/projects/index'
			);

		if ($this->action != 'add')
			$tab[] = array (
				'text' => LANG_ADDPROJECT,
				'link' => '/projects/add'
			);

		$tab[] = array (
			'text' => LANG_DISPLAYFULLHISTORY,
			'link' => '/deploymentLogs'
		);
		$this->set("context_menu", $tab);
	}

	// Public actions -----------------------------------------------------------
	
	function index() {

		$criteria = NULL;
		list ($order, $limit, $page) = $this->Pagination->init($criteria); // Added
		$data = $this->Project->findAll($criteria, NULL, $order, $limit, $page); // Extra parameters added
		$this->set('data', $data);
	}

	function deploy($id = null) {
		$this->layout = 'ajax';
		$this->set('id', $id);

	}
	
	function view($id = null) {

		if (!$id) {
			$this->Session->setFlash(LANG_INVALIDID);
			$this->redirect('/projects/index');
		}
		$project = $this->Project->read(null, $id);
		$this->set('project', $project);

	}

	function add() {

		if (empty ($this->data)) {
			$this->render();
		} else {
			$this->cleanUpFields();
			if ($this->Project->save($this->data)) {
				$this->Session->setFlash(LANG_PROJECTSAVED);
				$this->redirect('/projects/index');
			} else {
				$this->Session->setFlash(LANG_CORRECTERRORSBELOW);
			}
		}
	}

	function edit($id = null) {

		if (empty ($this->data)) {
			if (!$id) {
				$this->Session->setFlash(LANG_INVALIDID);
				$this->redirect('/projects/index');
			}
			$this->data = $this->Project->read(null, $id);
		} else {
			$this->cleanUpFields();
			if ($this->Project->save($this->data)) {
				$this->Session->setFlash(LANG_PROJECTSAVED);
				$this->redirect('/projects/view/' . $id);
			} else {
				$this->Session->setFlash(LANG_CORRECTERRORSBELOW);
			}
		}
	}

	function delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(LANG_INVALIDID);
			$this->redirect('/projects/index');
		}
		if ($this->Project->del($id)) {
			$this->Session->setFlash(LANG_PROJECTDELETED);
			$this->redirect('/projects/index');
		}
	}
	
	// Ajax steps -----------------------------------------------------------
	
	function initialize() {
		// custom timelimit
		set_time_limit(_TIMELIMIT_INITIALIZE);
		$t = getMicrotime();
		
		$this->layout = 'ajax';
		$output = '';
		
		$this->set('output', $output);
		$this->set('took', round((getMicrotime() - $t) , 3));
	}

	function export() {
		// custom timelimit
		set_time_limit(_TIMELIMIT_EXPORT);
		$t = getMicrotime();
		
		$this->layout = 'ajax';
		$output = '';
		$revision = '';

		//on efface le cache de stat() sinon problème avec la fonction is_dir() - voir la doc Php
		clearstatcache();

		// si les répertoires temporaires et backup nécessaires à Fredistrano n'existent pas, on le crée

		if (!is_dir(_DEPLOYDIR)) {
			if (mkdir(_DEPLOYDIR, octdec(_DIRMODE), TRUE))
				$output .= "-[création du répertoire " . _DEPLOYDIR . "]\n";
		}
		if (!is_dir(_DEPLOYTMPDIR)) {
			if (mkdir(_DEPLOYTMPDIR, octdec(_DIRMODE), TRUE))
				$output .= "-[création du répertoire " . _DEPLOYTMPDIR . "]\n";
		}
		if (!is_dir(_DEPLOYBACKUPDIR)) {
			if (mkdir(_DEPLOYBACKUPDIR, octdec(_DIRMODE), TRUE))
				$output .= "-[création du répertoire " . _DEPLOYBACKUPDIR . "]\n";
		}

		if (!$this->data['Project']['id']) {
			$this->Session->setFlash(LANG_INVALIDID);

		} else {
			$project = $this->Project->read(null, $this->data['Project']['id']);
			$this->set('project', $project);

			//dossier temporaire d'export SVN pour le projet
			if (is_dir(_DEPLOYTMPDIR . DS . $project['Project']['name'])) {
				// on le vide si il existe
				$output .= "-[vidage du répertoire " . _DEPLOYTMPDIR . DS . $project['Project']['name'] . "]\n";
				$output .= shell_exec('rm -rf ' . _DEPLOYTMPDIR . DS . $project['Project']['name'] . "/*");
			} else {
				// on le crée si il n'existe pas
				if (mkdir(_DEPLOYTMPDIR . DS . $project['Project']['name'], octdec(_DIRMODE), TRUE))
					$output .= "-[création du répertoire " . _DEPLOYTMPDIR . DS . $project['Project']['name'] . "]\n";
			}

			// création du répertoire de l'application si il n'existe pas
			if (!is_dir($project['Project']['prd_path'])) {
				if (@ mkdir($project['Project']['prd_path'], octdec(_DIRMODE), TRUE))
					$output .= "-[création du répertoire " . $project['Project']['prd_path'] . "]\n";
			}

			//on se place dans le dossier temporaire pour faire le svn export
			chdir(_DEPLOYTMPDIR . DS . $project['Project']['name']);
			$output .= "-[commande svn export]\n";
			if ((isset ($this->data['Project']['revision']) && ($this->data['Project']['revision']) != "")) {
				$revision = ' -r ' . $this->data['Project']['revision'];
			}

			if (($this->data['Project']['user'] != "") && ($this->data['Project']['password'] != "")) {
				$authentication = ' --username ' . $this->data['Project']['user'] . ' --password ' . $this->data['Project']['password'];
			} else {
				$authentication = ' --username ' . _SVNUSER . ' --password ' . _SVNPASS;
			}

			// svn export
			$output .= shell_exec("svn export" . $revision . $authentication . " " . $project['Project']['svn_url'] . " tmpDir");
			preg_match('/ ([0-9]+)\.$/', $output, $matches);

			$this->set('revision', $matches[1]);
			$this->set('output', $output);
			$this->set('took', round((getMicrotime() - $t) , 3));
		}	
	}

	function synchronize() {
		// custom timelimit
		set_time_limit(_TIMELIMIT_RSYNC);
		$t = getMicrotime();

		

		$project = $this->Project->read(null, $this->data['Project']['id']);
		$this->set('project', $project);
		
		$this->layout = 'ajax';
		$output = '';

		if (!@ file_exists(_DEPLOYTMPDIR . DS . $project['Project']['name'] . DS . "tmpDir" . DS . "deploy.php")) {
			$output .= '[ERROR] - synchro impossible, fichier deploy.php inexistant, ' .
					'ce fichier doit se trouver à la racine du projet à déployer, voir la documentation de Fredistrano';
		} else {
			include_once (_DEPLOYTMPDIR . DS . $project['Project']['name'] . DS . "tmpDir" . DS . "deploy.php");

			$exclude = $this->_getConfig()->exclude;
			$exclude_string = "";
			for ($i = 0; $i < sizeof($exclude); $i++) {
				$exclude_string .= "- ".$exclude[$i] . "\n";
			}
			$exclude_string .= "- deploy.php\n";
			$exclude_string .= "- **.dev.**\n";
			
			$exclude_file_name = _DEPLOYTMPDIR . DS . $project['Project']['name'] . DS . "exclude_file.txt";
			$handle = fopen($exclude_file_name, "w");
			fwrite($handle, $exclude_string);
			fclose($handle);
			//on sauvegarde la version actuelle au cas ou
			if ($this->_backup($project, $output)) {
				//on défini les option de la commande rsync
				if ($this->data['Project']['simulation'] == 1) {
					// simulation
					$option = 'rtOvn';
				} else {
					// pas simulation
					$option = 'rtOv';

					// Log du deploiment 
					$data = array (
						'DeploymentLog' => array (
							'project_id' => $this->data['Project']['id'],
							'title' => $project['Project']['name'] . ' - ' . $_SESSION['User']['login'],
							'user_id' => $_SESSION['User']['id'],
							'comment' => $this->data['DeploymentLog']['comment'] ? $this->data['DeploymentLog']['comment'] : 'aucun',
							'archive' => 0
						)
					);
					$this->DeploymentLog->save($data);
				}

				//mise en forme des paramètres (windows/linux) pour la commande rsync 
				$exclude_file_name = $this->_pathConverter($exclude_file_name);
				$source = $this->_pathConverter(_DEPLOYTMPDIR . DS . $project['Project']['name'] . DS . "tmpDir" . DS);
				$target = $this->_pathConverter($project['Project']['prd_path']);

				chdir(_DEPLOYDIR);
				$output .= shell_exec("rsync -$option --delete --exclude-from=$exclude_file_name $source $target");
				//$output .= e("rsync -$option --delete --exclude-from=$exclude_file_name $source $target");

			} else {
				$output .= "Erreur - problème de sauvegarde ";
			}
		}
		$this->set('output', $output);
		$this->set('took', round((getMicrotime() - $t) , 3));
	}

	function finalize() {
		// custom timelimit
		set_time_limit(_TIMELIMIT_FINALIZE);
		$t = getMicrotime();

		$project = $this->Project->read(null, $this->data['Project']['id']);
		$this->set('project', $project);
		
		$this->layout = 'ajax';
		$output = '';
		
		if (!@ file_exists(_DEPLOYTMPDIR . DS . $project['Project']['name'] . DS . "tmpDir" . DS . "deploy.php")) {
			$output .= '[ERROR] - synchro impossible, fichier deploy.php inexistant, ' .
					'ce fichier doit se trouver à la racine du projet à déployer, voir la documentation de Fredistrano';
		} else {
			include_once (_DEPLOYTMPDIR . DS . $project['Project']['name'] . DS . "tmpDir" . DS . "deploy.php");
			chdir(_DEPLOYDIR);
			if (_WINOS === true) {
				//couche cygwin
				$prefix = "bash.exe --login -c '";
				$suffix = "'";
			} else {
				$prefix = "";
				$suffix = "";
			}
		
			if ($this->data['Project']['RenamePrdFile'] == true) {
				//renommage des versions de prod des fichiers de type .prd.xxx en .xxx
				$output .= "\n-[renommage des fichiers '.prd.']\n";
				$output .= shell_exec($prefix."find " . $this->_pathConverter($project['Project']['prd_path']) . " -name '*.prd.*' -exec /usr/bin/perl ".$this->_pathConverter(_DEPLOYDIR)."/renamePrdFile -vf 's/\.prd\./\./i' {} \;".$suffix);				
			}
			
			if ($this->data['Project']['ChangeFileMode'] == true) {
				//ajustement des droits 
				$output .= "\n-[modification des droits des fichiers] Nouvelles permissions: " . _FILEMODE;
				$output .= shell_exec("chmod -R " ._FILEMODE . "  ".$this->_pathConverter($project['Project']['prd_path']));	
				$output .= "\n-[modification des droits des répertoires ] Nouvelles permissions: " . _DIRMODE;
				$output .= shell_exec($prefix."find " . $this->_pathConverter($project['Project']['prd_path']) . " -type d -exec chmod " . _DIRMODE . " {} \;".$suffix);
			}
			
			if ($this->data['Project']['GiveWriteMode'] == true) {
				$output .= "\n-[Ajout de persmissions pour écriture]\n";
				$writable = $this->_getConfig()->writable;
				if (sizeof($writable) > 0) {
					for ($i = 0; $i < sizeof($writable); $i++) {
						$output .= shell_exec("chmod -vR " ._WRITEMODE . "  ".$this->_pathConverter($project['Project']['prd_path'] . $writable[$i] ));
					}
				}
			}
			
		}
		$this->set('output', $output);
		$this->set('took', round((getMicrotime() - $t) , 3));
	}
	
	// Private functions -----------------------------------------------------------

	private function _backup($project, $output) {

		if (!is_dir(_DEPLOYBACKUPDIR)) {
			if (mkdir(_DEPLOYBACKUPDIR, octdec(_DIRMODE), TRUE))
				$output .= "-[création du répertoire " . _DEPLOYBACKUPDIR . "]\n";
		}

		// création du répertoire pour la sauvegarde
		if (!is_dir(_DEPLOYBACKUPDIR . DS . $project['Project']['name'])) {
			if (mkdir(_DEPLOYBACKUPDIR . DS . $project['Project']['name'], octdec(_DIRMODE), TRUE)) {
				$output .= "-[création du répertoire " . _DEPLOYBACKUPDIR . DS . $project['Project']['name'] . "]\n";
			}
		}

		//
		$output .= "-[sauvegarde de la version actuellement en prod]\n";
		if (is_dir($project['Project']['prd_path'])) {
			// rsync pour le backup
			$output .= shell_exec("rsync -av " . $project['Project']['prd_path'] . " " . _DEPLOYBACKUPDIR . DS);
			$output .= shell_exec("chmod -R " . _DIRMODE . " " . _DEPLOYBACKUPDIR);

		} else {
			$output .= "-[pas de backup à faire car le répertoire " . $project['Project']['prd_path'] . " n'existe pas]\n";
		}

		$this->set('output', $output);

		if (is_dir(_DEPLOYBACKUPDIR . DS . $project['Project']['name'])) {
			return true;
		} else {
			return false;
		}

	}


	//dans le cas d'un path windows on le reformate à la sauce cywin
	private function _pathConverter($path) {
		$pathForRsync = $path;
		if (_WINOS) {
			$pattern = '/^([A-Za-z]):/';
			preg_match($pattern, $path, $matches, PREG_OFFSET_CAPTURE);
			if (!empty ($matches[1][0])) {
				$windowsLetter = strtolower($matches[1][0]);
				$pathForRsync = strtr(_CYGWINROOT . $windowsLetter . substr($path, 2), "\\", "/");
			}	
		}
		return $pathForRsync;
	}
	
	private function &_getConfig() {
		static $instance;

		if (!isset($instance) || !$instance) {
			$instance = &new DEPLOY_CONFIG();
		}

		return $instance;
	}
	
}
?>