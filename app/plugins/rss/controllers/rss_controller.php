<?php

class RssController extends RssAppController {
	var $name = 'Rss';
	var $helpers = array('Time');
	var $uses = '';
	var $model = null;

	var $titleFields = array('title', 'name');
	var $descFields = array('desc', 'description', 'text', 'content', 'body');

	function feed() {
		//check that a model is being requested
		if (empty($this->passed_args[0])) {
			debug('No model was passed.  The URL format should be /rss/model');
			die;
		}

		//load the model
		$this->modelName = Inflector::classify($this->passed_args[0]);

		if (!loadModel($this->modelName)) {
			debug('Unable to load the requested model.');
			die;
		}

		$this->model = new $this->modelName();

		//check the model is allowed to be used as rss
		if (!isset($this->model->feed)) {
			debug('You are not allowed to create a feed from this model.');
			die;
		}

		$conditions = null;
		if (isset($this->model->feed['conditions'])) {
			$conditions = $this->model->feed['conditions'];
		}

		$orderby = 'created DESC';
		if (isset($this->model->feed['orderby'])) {
			$orderby = $this->model->feed['orderby'];
		}

		$limit = '10';
		if (isset($this->model->feed['limit'])) {
			$limit = $this->model->feed['limit'];
		}

		$data = $this->model->findAll($conditions, null, $orderby, $limit);
		
		//organize the data
		$rssData = array();

		foreach($data as $i => $item) {
			$temp = array();

			//set the title
			$temp['title'] = $this->__getValue($item, 'titleField', $this->titleFields);

			//set the desc
			$temp['desc'] = $this->__getValue($item, 'descField', $this->descFields);

			//skip the item if there is no title or desc
			if (empty($temp['title']) || empty($temp['desc'])) {
					continue;
			}

			//set the link
			if (isset($this->model->feed['link'])) {
				$temp['link'] = sprintf($this->model->feed['link'], $item[$this->model->name][$this->model->primaryKey]);
			} else {
				$temp['link'] = '/' . $this->model->name . '/view/' . $item[$this->model->name][$this->model->primaryKey];
			}

			$temp['created'] = $item[$this->model->name]['created'];

			$rssData[] = $temp;
		}

		$this->set('rssData', $rssData);
		$this->set('feedTitle', Inflector::humanize(env('HTTP_HOST')) . ' Recent ' . Inflector::pluralize($this->model->name));
		$this->set('modelName', $this->model->name);

		$this->layout = '';
		$this->render('rss');
	}

	function __getValue($item, $element, $fields) {

		if (isset($this->model->feed[$element])) {
			if (strpos($this->model->feed[$element], '.') !== false) {
				@list($m, $f) = preg_split('/[.]/', $this->model->feed[$element], 2);
				return $item[$m][$f];
			} else {
				return $item[$this->model->name][$this->model->feed[$element]];
			}
		}

		foreach($fields as $field) {
			if (isset($item[$this->model->name][$field])) {
				$temp['title'] = $item[$this->model->name][$field];
			}
		}

		return '';
	}
}

?>