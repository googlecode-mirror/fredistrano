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
				'text' => 'Lister les projets',
				'link' => '/projects/index'
			);

		if ($this->action != 'add')
			$tab[] = array (
				'text' => 'Ajouter un projet',
				'link' => '/projects/add'
			);

		$tab[] = array (
			'text' => 'Afficher tout l\'historique',
			'link' => '/deploymentLogs'
		);
		$this->set("context_menu", $tab);
	}

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

	function deploy_result() {
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
			$this->Session->setFlash('L\'identifiant du projet est invalide.');

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
			set_time_limit(_TIMELIMITSVN);
			$output .= shell_exec("svn export" . $revision . $authentication . " " . $project['Project']['svn_url'] . " tmpDir");
			preg_match('/ ([0-9]+)\.$/', $output, $matches);

			$this->set('revision', $matches[1]);
			$this->set('output', $output);
		}
	}

	function synchro() {
		$this->layout = 'ajax';

		$project = $this->Project->read(null, $this->data['Project']['id']);
		$this->set('project', $project);

		$output = '';

		if (!@ file_exists(_DEPLOYTMPDIR . DS . $project['Project']['name'] . DS . "tmpDir" . DS . "deploy.php")) {
			$output .= '[ERROR] - synchro impossible, fichier deploy.php inexistant, ' .
					'ce fichier doit se trouver à la racine du projet à déployer, voir la documentation de Fredistrano';
		} else {
			include_once (_DEPLOYTMPDIR . DS . $project['Project']['name'] . DS . "tmpDir" . DS . "deploy.php");

			$exclude = $this->_getConfig()->exclude;
			$exclude_string = "";
			for ($i = 0; $i < sizeof($exclude); $i++) {
				$exclude_string .= $exclude[$i] . "\n";
			}
			$exclude_file_name = _DEPLOYTMPDIR . DS . $project['Project']['name'] . DS . "exclude_file.txt";
			$handle = fopen($exclude_file_name, "w");
			fwrite($handle, $exclude_string);
			fclose($handle);
			//on sauvegarde la version actuelle au cas ou
			if ($this->_backup($project, $output)) {
				//on défini les option de la commande rsync
				if ($this->data['Project']['simulation'] == 1) {
					// simulation
					$option = 'rtpgoOvn';
				} else {
					// pas simulation
					$option = 'rtpgoOv';

					// Log du deploiment 
					$data = array (
						'DeploymentLog' => array (
							'project_id' => $this->data['Project']['id'],
							'title' => $project['Project']['name'] . ' - ' . $_SESSION['User']['login'],
							'user_id' => $_SESSION['User']['id'],
							'comment' => $this->data['DeploymentLog']['comment'] ? $this->data['DeploymentLog']['comment'] : 'aucun'
						)
					);

					$this->DeploymentLog->save($data);
				}

				//mise en forme des paramètres (windows/linux) pour la commande rsync 
				$exclude_file_name = $this->_pathConverter($exclude_file_name);
				$source = $this->_pathConverter(_DEPLOYTMPDIR . DS . $project['Project']['name'] . DS . "tmpDir" . DS);
				$target = $this->_pathConverter($project['Project']['prd_path']);

				chdir(_DEPLOYDIR);
				set_time_limit(_TIMELIMITRSYNC);
				$output .= shell_exec("rsync -$option --delete --exclude-from=$exclude_file_name $source $target");

				if ($this->data['Project']['simulation'] == 0) {
					if (_WINOS === true) {
						//couche cygwin
						$prefix = "bash.exe --login -c '";
						$suffix = "'";
					} else {
						$prefix = "";
						$suffix = "";
					}
					
					//renommage des versions de prod des fichiers de type .prd.xxx en .xxx et suppression des *.dev.*
					$output .= shell_exec($prefix."find " . $this->_pathConverter($project['Project']['prd_path']) . " -name '*.prd.*' -exec /usr/bin/perl ".$this->_pathConverter(_DEPLOYDIR)."/renamePrdFile -vf 's/\.prd\./\./i' {} \;".$suffix);
					$output .= shell_exec($prefix."find " . $this->_pathConverter($project['Project']['prd_path']) . " -name '*.dev.*' -exec rm -f {} \;".$suffix);

					//correction des droits 
					$output .= shell_exec($prefix."find " . $this->_pathConverter($project['Project']['prd_path']) . " -type d -exec chmod " . _DIRMODE . " {} \;".$suffix);
					$output .= shell_exec($prefix."find " . $this->_pathConverter($project['Project']['prd_path']) . " -type f -exec chmod " . _FILEMODE . " {} \;".$suffix);	
					$writable = $this->_getConfig()->writable;
					if (sizeof($writable) > 0) {
						for ($i = 0; $i < sizeof($writable); $i++) {
							$output .= shell_exec($prefix."find " . $this->_pathConverter($project['Project']['prd_path'] . $writable[$i] ) . " -type d -exec chmod 777 {} \;".$suffix);
						}
					}
					
					//suppression du fichier deploy.php
					$output .= "\n-[suppression du fichier " . $project['Project']['prd_path'] . DS . "deploy.php]";
					$output .= shell_exec('rm ' . $this->_pathConverter( $project['Project']['prd_path'] . DS . "deploy.php") );
				}
			} else {
				$output .= "Erreur - problème de sauvegarde ";
			}
		}
		$this->set('output', $output);
	}

	function view($id = null) {

		if (!$id) {
			$this->Session->setFlash('Invalid id for Project.');
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
				$this->Session->setFlash('The Project has been saved');
				$this->redirect('/projects/index');
			} else {
				$this->Session->setFlash('Please correct errors below.');
			}
		}
	}

	function edit($id = null) {

		if (empty ($this->data)) {
			if (!$id) {
				$this->Session->setFlash('Invalid id for Project');
				$this->redirect('/projects/index');
			}
			$this->data = $this->Project->read(null, $id);
		} else {
			$this->cleanUpFields();
			if ($this->Project->save($this->data)) {
				$this->Session->setFlash('The Project has been saved');
				$this->redirect('/projects/view/' . $id);
			} else {
				$this->Session->setFlash('Please correct errors below.');
			}
		}
	}

	function delete($id = null) {
		if (!$id) {
			$this->Session->setFlash('Invalid id for Project');
			$this->redirect('/projects/index');
		}
		if ($this->Project->del($id)) {
			$this->Session->setFlash('The Project deleted: id ' . $id . '');
			$this->redirect('/projects/index');
		}
	}

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