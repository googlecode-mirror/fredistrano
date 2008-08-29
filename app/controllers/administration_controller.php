<?php
class AdministrationController extends AppController {
	
	var $uses = array ('Project','DeploymentLog','Deployment');

	var $authLocal = array (
		'Administration' => array (
			'administration'
		)
	);
	
	function beforeRender() {
		parent::beforeRender();
		
		// Tableau de liens pour la création du menu contextuel
		$tab[] = array (
			'text' => 'Actions'
		);

		$tab[] = array (
			'text' => 'Update application',
			'link' => '/administration/update'
		);
		
		$this->set("context_menu", $tab);
	}
	
	function index() {}

	function update() {
		$p = $this->Project->read( null, 1);
		
		if (isset($this->params['url']['result']) && isset($this->params['url']['token'])) {
			@unlink( WWW_ROOT . 'web_admin.update.'.$this->params['url']['token'].'.php');
			@unlink( WWW_ROOT . 'web_admin.update.'.$this->params['url']['token'].'.log');
			
			if ($this->params['url']['token']!=$_SESSION['update_token']) {
				$this->Session->setFlash('Invalid update token');
				$this->redirect('/administration/update');
			}
			
			if ($this->params['url']['result'] == 'success') {
				$this->DeploymentLog->save(array(
					'DeploymentLog' => array (
						'project_id'	=> 	$p['Project']['id'],
						'title' 		=> 	date("D, M jS Y, H:i") . ' - ' . $p['Project']['name'],
						'user_id' 		=> 	$_SESSION['User']['User']['cn'],
						'comment' 		=> 	'update to revision ' . $this->params['url']['revision'],
						'archive' 		=> 	0
					)
				));
				
				$this->Session->setFlash('Update finished');
				$this->redirect('/administration');			
			} else {
				// failure
				$this->Session->setFlash('Update has failed! Retry?');
				$this->set('token', $this->params['url']['token']);
			}
			
		} else { 
			$token = sha1( rand(0,10000) + time());
			$_SESSION['update_token'] = $token;
			if ($handle = fopen( WWW_ROOT . 'web_admin.update.'.$token.'.php','w+')) {
				
				$tmpDir = F_DEPLOYTMPDIR.$p['Project']['name'].DS.'tmpDir'.DS ;
				$target = Deployment :: pathConverter( F_FREDISTRANOPATH );
				$excludeFile 	= Deployment :: pathConverter( F_DEPLOYDIR.'exclude_file.txt' );
				$renameScript 	= Deployment :: pathConverter( F_DEPLOYDIR.'renamePrdFile' );
				
				$content = "<?php \n" 
					."set_time_limit(240);\n"
					."if ( !file_exists('".F_DEPLOYTMPDIR. $p['Project']['name']."') ) {\n"
						."mkdir('".F_DEPLOYTMPDIR. $p['Project']['name']."');\n"
					."} else {\n"	
						.'$res'." = shell_exec('rm -rf ".F_DEPLOYTMPDIR.$p['Project']['name']."/*');\n"
					."}\n"
					.'$res'." .= shell_exec('svn export --username ".Configure::read('Subversion.user').
						" --password ".Configure::read('Subversion.passwd')." ".$p['Project']['svn_url']." ".$tmpDir." 2>&1');\n"
					."preg_match('/ ([0-9]+)\.$/', \$res, \$matches);\n"
					."\$revision = isset(\$matches[1])?\$matches[1]:'XXX';\n"
					.'$res'." .= shell_exec('rsync -rtOv --delete --exclude-from=". $excludeFile . " ".Deployment :: pathConverter($tmpDir)." ".$target." 2>&1');\n"
					.'$res'." .= shell_exec('bash.exe --login -c \\'find ".$target." -name \"*.prd.*\" -exec /usr/bin/perl " . $renameScript . " -vf \"s/\.prd\./\./i\" {} \;\\'');\n"
					.'$res'." .= shell_exec('chmod -R 777  ".$target."');\n"
					.'$res'." .= shell_exec('chmod 777  ".$target."');\n"
					.'$res'." .= shell_exec('bash.exe --login -c \\'find ".$target." -type d -exec chmod 777 {} \;\\'');\n"
					.'$res'." .= shell_exec('chmod -vR 777  ".$target."app/tmp');\n"				
					.'$handle = fopen("' . WWW_ROOT . 'web_admin.update.' . $token . '.log","w");'."\n"
					."fwrite(\$handle,\$res);\n"
					."fclose(\$handle);\n"
					."?>\n"
					.'Job finished (<a href="'.$this->webroot.'web_admin.update.'.$token.'.log">logs</a>)'."<br/>\n"
					.'Update successful? <a href="'.$this->here.'?result=success&token='.$token.'&revision=<?php echo $revision; ?>">yes</a> / <a href="'.$this->here.'?result=failure&token='.$token.'">no</a>'."\n";
				
				fwrite($handle, $content);
				fclose($handle);
			} else {
				$this->Session->setFlash(__('Invalid id.', true));
				$this->redirect('/');
			}
			$this->set('token',$token);
		}
	}// update

} // Administration
?>