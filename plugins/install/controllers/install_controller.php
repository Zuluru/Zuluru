<?php
/**
 * Install Controller
 *
 * This was modified from the Croogo install plugin:
 * @author   Fahad Ibnay Heylaal <contact@fahad19.com>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://www.croogo.org
 */
class InstallController extends InstallAppController {
	var $name = 'Install';
	var $uses = null;
	var $components = null;

	var $defaultDbConfig = array(
		'name' => 'default',
		'driver'=> 'mysql',
		'persistent'=> false,
		'host'=> 'localhost',
		'login'=> 'root',
		'password'=> '',
		'database'=> 'zuluru',
		'schema'=> null,
		'prefix'=> null,
		'encoding' => 'UTF8',
		'port' => null,
	);

	function beforeFilter() {
		parent::beforeFilter();

		$this->layout = 'install';
		App::import('Component', 'Session');
		$this->Session = new SessionComponent;
	}
/**
 * If installed.php exists, app is already installed
 *
 * @return void
 */
	function _check() {
		if (file_exists(CONFIGS . 'installed.php')) {
			$this->Session->setFlash('Already Installed');
			$this->redirect('/');
		}
	}

/**
 * Delete the installation plugin
 *
 * @return true if it succeeds, false otherwise
 */
	function _delete() {
		App::import('Core', 'Folder');
		$this->folder = new Folder;
		if ($this->folder->delete(APP . 'plugins' . DS . 'install')) {
			$this->Session->setFlash(__('Installation files deleted successfully.', true), 'default', array('class' => 'success'));
			return true;
		} else {
			$this->Session->setFlash(__('Could not delete installation files.', true), 'default', array('class' => 'error'));
			return false;
		}
	}

/**
 * Create or update the installed.php file with version details
 *
 * @return void
 */
	function _writeInstalled($schema = SCHEMA_VERSION) {
		$file = new File(CONFIGS . 'installed.php', true);
		$date = date('r');
		$version = ZULURU_MAJOR . '.' . ZULURU_MINOR . '.' . ZULURU_REVISION;

		$installed = <<<CONFIG
<?php
\$config['installed'] = array(
	'date' => '$date',
	'ip' => '{$_SERVER['REMOTE_ADDR']}',
	'version' => '$version',
	'schema_version' => $schema,
);
?>
CONFIG;

		if (!$file->write($installed)) {
			$this->set('config_file', $file->pwd());
			$this->set('config_contents', $installed);
			return false;
		}

		return true;
	}

/**
 * Import default data from the specified file
 *
 * @return void
 */
	function _importData($data) {
		if (!App::import('class', $data, false, CONFIGS . 'schema' . DS . 'data' . DS)) {
			return;
		}
		$classVars = get_class_vars($data);
		$modelAlias = substr($data, 0, -4);
		$table = $classVars['table'];
		$records = $classVars['records'];
		App::import('Model', 'Model', false);
		$modelObject =& new Model(array(
			'name' => $modelAlias,
			'table' => $table,
			'ds' => 'default',
		));
		if (is_array($records) && count($records) > 0) {
			foreach($records as $record) {
				$modelObject->create($record);
				$modelObject->save();
			}
		}
	}

/**
 * Step 0: welcome
 *
 * A simple welcome message for the installer.
 *
 * @return void
 * @access public
 */
	function index() {
		$this->_check();
		$this->set('title_for_layout', __('Installation: Welcome', true));
	}

/**
 * Step 1: settings
 *
 * Copy install.php file into place and update
 *
 * @return void
 * @access public
 */
	function settings() {
		$this->_check();
		$this->set('title_for_layout', __('Step 1: Basic settings', true));

		if (empty($this->data)) {
			return;
		}

		$config = array_merge (array(
				'webroot' => $_SERVER['DOCUMENT_ROOT'],
		), $this->data['Install']);

		copy(CONFIGS . 'install.php.default', CONFIGS . 'install.php');
		App::import('Core', 'File');
		$file = new File(CONFIGS . 'install.php', true);
		$content = $file->read();

		foreach ($config as $configKey => $configValue) {
			$content = str_replace('{' . $configKey . '}', $configValue, $content);
		}

		if($file->write($content) ) {
			$this->Session->setFlash(__('Basic settings saved. Please review install.php for any advanced setting changes.', true), 'default', array('class' => 'success'));
			$this->redirect(array('action' => 'database'));
		} else {
			$this->Session->setFlash(__('Could not write install.php file.', true), 'default', array('class' => 'error'));
		}
	}

/**
 * Step 2: database
 *
 * Try to connect to the database and give a message if that's not possible so the user can check their
 * credentials or create the missing database
 * Create the database file and insert the submitted details
 *
 * @return void
 * @access public
 */
	function database() {
		$this->_check();
		$this->set('title_for_layout', __('Step 2: Database configuration', true));

		if (empty($this->data)) {
			return;
		}

		@App::import('Model', 'ConnectionManager');
		$config = array_merge ($this->defaultDbConfig, $this->data['Install']);

		copy(CONFIGS . 'database.php.default', CONFIGS . 'database.php');
		App::import('Core', 'File');
		$file = new File(CONFIGS . 'database.php', true);
		$content = $file->read();

		foreach ($config as $configKey => $configValue) {
			$content = str_replace('{' . $configKey . '}', $configValue, $content);
		}

		if($file->write($content) ) {
			require(CONFIGS . 'database.php');
			ConnectionManager::create('default');
			$db = ConnectionManager::getDataSource('default');
			if (!$db->isConnected()) {
				$this->Session->setFlash(__('Could not connect to database.', true), 'default', array('class' => 'error'));
				return;
			}
			$this->redirect(array('action' => 'data'));
		} else {
			$this->Session->setFlash(__('Could not write database.php file.', true), 'default', array('class' => 'error'));
		}
	}

/**
 * Step 3: Run the initial sql scripts to create the db and seed it with data
 *
 * @return void
 * @access public
 */
	function data() {
		$this->_check();
		$this->set('title_for_layout', __('Step 3: Build database', true));
		if (isset($this->params['named']['run'])) {
			App::import('Core', 'File');
			App::import('Model', 'CakeSchema', false);
			App::import('Model', 'ConnectionManager');

			$db = ConnectionManager::getDataSource('default');
			if(!$db->isConnected()) {
				$this->Session->setFlash(__('Could not connect to database.', true), 'default', array('class' => 'error'));
			} else {
				$schema =& new CakeSchema(array('name' => 'zuluru'));
				$schema = $schema->load();
				foreach($schema->tables as $table => $fields) {
					$create = $db->createSchema($schema, $table);
					$db->execute($create);
				}

				$dataObjects = App::objects('class', CONFIGS . 'schema' . DS . 'data' . DS);
				foreach ($dataObjects as $data) {
					$this->_importData($data);
				}

				$this->redirect(array('action' => 'finish'));
			}
		}
	}

/**
 * Step 4: finish
 *
 * Remind the user to delete 'install' plugin
 *
 * @return void
 * @access public
 */
	function finish() {
		$this->set('title_for_layout', __('Installation completed successfully', true));

		if (isset($this->params['named']['delete'])) {
			if ($this->_delete()) {
				$this->redirect('/');
			}
		}
		$this->_check();

		// set email address for admin
		Configure::Load('install');
		$User = ClassRegistry::init('User');
		$User->id = $User->field('id', array('user_name' => 'admin'));
		$User->saveField('email', 'admin@' . Configure::read('urls.domain'));

		// set new salt and seed value
		$File =& new File(CONFIGS . 'core.php');
		if (!class_exists('Security')) {
			require LIBS . 'security.php';
		}
		$salt = Security::generateAuthKey();
		$seed = mt_rand() . mt_rand();
		$contents = $File->read();
		$contents = preg_replace('/(?<=Configure::write\(\'Security.salt\', \')([^\' ]+)(?=\'\))/', $salt, $contents);
		$contents = preg_replace('/(?<=Configure::write\(\'Security.cipherSeed\', \')(\d+)(?=\'\))/', $seed, $contents);
		if (!$File->write($contents)) {
			return false;
		}

		// set password, hashed according to new salt value
		$User->saveField('password', Security::hash('password', 'sha256', $salt));

		$this->_writeInstalled();
	}

/**
 * Step 5: update
 *
 * Contents of this have been copied and modified from the cake console schema task.
 * 
 * Remind the user to delete 'install' plugin.
 *
 * @return void
 * @access public
 */
	function update() {
		$this->set('title_for_layout', __('Update database', true));

		if (isset($this->params['named']['delete'])) {
			if ($this->_delete()) {
				$this->redirect('/');
			}
		}

		App::import('Model', 'CakeSchema', false);
		$this->schema =& new CakeSchema();

		Configure::load('installed');
		$this->results = $this->dataObjects = array();
		$success = $this->_update();
		if ($success) {
			if (empty($this->results)) {
				$this->Session->setFlash(__('Database is already up to date.', true), 'default', array('class' => 'info'));
				if (ZULURU_MAJOR . '.' . ZULURU_MINOR . '.' . ZULURU_REVISION != Configure::read('installed.version') ||
					SCHEMA_VERSION != Configure::read('installed.schema_version'))
				{
					$this->_writeInstalled();
				}
				$this->render('finish');
				return;
			}

			if (isset($this->params['named']['execute'])) {
				// This must be done at the very end, after all schema changes are completed,
				// as the data will be formatted for the current schema.
				foreach ($this->dataObjects as $data) {
					$this->_importData($data);
				}

				$this->_writeInstalled();

				// Clear the model cache one last time, so it's refreshed
				// with correct data for the next visitor
				Cache::clear(false, '_cake_model_');
			}
		}

		$this->set(array('results' => $this->results, 'success' => $success));
	}

	function _update() {
		for ($ver = Configure::read('installed.schema_version') + 1; $ver <= SCHEMA_VERSION; ++ $ver) {
			if (file_exists(CONFIGS . 'schema' . DS . 'migrations' . DS . 'schema' . $ver . '.php')) {
				if (!$this->_updateOne(array('name' => 'Zuluru' . $ver, 'path' => CONFIGS . 'schema' . DS . 'migrations', 'file' => 'schema' . $ver . '.php'))) {
					return false;
				}
				if (isset($this->params['named']['execute'])) {
					$this->_writeInstalled($ver);
				}
			}
		}
		if (!$this->_updateOne(array('name' => 'Zuluru', 'path' => CONFIGS . 'schema', 'file' => 'schema.php'))) {
			return false;
		}

		return true;
	}

	function _updateOne($options) {
		$schema = $this->schema->load($options);
		$db =& ConnectionManager::getDataSource($schema->connection);
		if (!$db->isConnected()) {
			$this->Session->setFlash(__('Could not connect to database.', true), 'default', array('class' => 'error'));
			return false;
		}

		$execute = isset($this->params['named']['execute']);

		// There may be pre-processing to complete before we analyze the existing schema
		$pre_result = $schema->before(array('update' => 'schema', 'execute' => $execute));
		if (!$pre_result) {
			$this->Session->setFlash(__('Failed to perform pre-processing on database', true), 'default', array('class' => 'error'));
			return false;
		} else if (is_array($pre_result)) {
			$this->results += $pre_result;
		}

		if ($execute) {
			// Clear the model cache, and make sure that what we read isn't pre-cached
			Cache::clear(false, '_cake_model_');
			$db->cacheSources = false;
		}

		// Not all of our tables have real models
		$this->old = $schema->read(array('models' => false));
		$compare = $schema->compare($this->old, $schema);

		$commands = array();
		foreach ($compare as $table => $changes) {
			if (array_key_exists ($table, $this->old['tables'])) {
				$commands[$table] = $db->alterSchema(array($table => $changes), $table);
			} else {
				$commands[$table] = $db->createSchema($schema, $table);
				if (file_exists(CONFIGS . 'schema' . DS . 'data' . DS . $table . '_data.php')) {
					$this->dataObjects[$table] = Inflector::camelize($table) . 'Data';
				}
			}
		}

		if ($execute) {
			// Clear the model cache again, so any saves that might happen in "after" callbacks will work
			Cache::clear(false, '_cake_model_');
			$db->cacheSources = false;

			foreach ($commands as $table => $sql) {
				$error = null;
				if (!$db->execute($sql)) {
					$error = $db->lastError();
				}

				$schema->after(array('update' => $table, 'execute' => $execute, 'errors' => $error));

				if (!empty($error)) {
					$this->results[$table] = $error;
					return false;
				} else {
					$this->results[$table] = __('updated.', true);
				}
			}
		} else {
			$this->results += $commands;
		}

		return true;
	}
}
?>
