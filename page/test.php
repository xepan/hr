<?php

/**
* description: ATK Page
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\hr {

	class page_test extends \Page {
		public $title='Page Title';

		function init(){
			parent::init();

			if(!($root_path = $this->recall('root_path',false))){
				$root_path = $this->add('xepan\hr\Model_File')
							->addCondition('name','root')
							->addCondition('mime','directory')
							->addCondition('created_by_id',$this->app->employee->id)
							->setOrder('id')
							->tryLoadAny()
							;
				if(!$root_path->loaded()) $root_path->save();
				$root_path = $root_path->id;
				$this->memorize('root_path',$root_path);
			}


			// Handle driver

			if($_GET['cmd']){
				$path_asset = $this->app->pathfinder->base_location->base_path.'/websites/'.$this->app->current_website_name.'/assets';
				$path_www = $this->app->pathfinder->base_location->base_path.'/websites/'.$this->app->current_website_name.'/www';
				$path_upload = $this->app->pathfinder->base_location->base_path.'/websites/'.$this->app->current_website_name.'/upload';
				
				// \elFinder::$netDrivers['ftp'] = 'FTP';
				// \elFinder::$netDrivers['dropbox'] = 'Dropbox';

				$opts = array(
				    'locale' => '',
				    'roots'  => array(
				        array(
				            'driver' => 'ATKFileStore',
				            'path'   => $root_path,
				            // 'URL'    => 'websites/'.$this->app->current_website_name.'/upload',
				            'alias'=>'MyDrive'

				        )
				    )
				);

				// run elFinder
				$x = new \elFinder($opts);
				$connector = new \elFinderConnector($x);
				$connector->run();
				exit;
			}else{
				// as per page 
			// http://codepen.io/kaizoku-kuma/pen/JDxtC
			$this->app->jui->addStylesheet('codemirror/codemirror-5.15.2/lib/codemirror');
			$this->app->jui->addStylesheet('codemirror/codemirror-5.15.2/theme/solarized');
			// $this->app->jui->addStylesheet('theme');

			$this->app->jui->addStaticInclude('codemirror/codemirror-5.15.2/lib/codemirror');
			$this->app->jui->addStaticInclude('codemirror/codemirror-5.15.2/mode/htmlmixed/htmlmixed');
			$this->app->jui->addStaticInclude('codemirror/codemirror-5.15.2/mode/jade/jade');
			$this->app->jui->addStaticInclude('codemirror/codemirror-5.15.2/mode/php/php');
			$this->app->jui->addStaticInclude('codemirror/codemirror-5.15.2/mode/xml/xml');
			$this->app->jui->addStaticInclude('codemirror/codemirror-5.15.2/mode/css/css');
			$this->app->jui->addStaticInclude('codemirror/codemirror-5.15.2/mode/javascript/javascript');
			
			$this->js(true,'

					elFinder.prototype.i18.en.messages["cmdshare"] = "Share";          
				    elFinder.prototype._options.commands.push("share");
				    elFinder.prototype.commands.share = function() {
				        this.exec = function(hashes) {
				             //do whatever
				        	alert("share");
				        }
				        this.getstate = function() {
				            //return 0 to enable, -1 to disable icon access
				            return 0;
				        }
				    }

					$("#'.$this->name.'").elfinder({
						url: "index.php?page=xepan_base_test",
						height:450,
						contextmenu : {
				            // navbarfolder menu
				            navbar : ["open", "|", "copy", "cut", "paste", "duplicate", "|", "rm", "|", "info","|","share"],
				            // current directory menu
				            cwd    : ["reload", "back", "|", "upload", "mkdir", "mkfile", "paste", "|", "sort", "|", "info"],
				            // current directory file menu
				            files  : ["getfile", "|", "share", "quicklook", "|", "download", "|", "copy", "cut", "paste", "duplicate", "|", "rm", "|", "edit", "rename", "resize", "|", "archive", "extract", "|", "info"]
				        },
						commandsOptions: {
							edit : { 
								// list of allowed mimetypes to edit // if empty - any text files can be edited mimes : [],
								// you can have a different editor for different mimes 
								editors : [{
									mimes : ["text/plain", "text/html","text/x-jade", "text/javascript", "text/css", "text/x-php", "application/x-httpd-php", "text/x-markdown", "text/plain", "text/html", "text/javascript", "text/css"],
									load : function(textarea) {
										this.myCodeMirror = CodeMirror.fromTextArea(textarea, { 
																						lineNumbers: true,
																						theme: "solarized",
																						viewportMargin: Infinity, 
																						lineWrapping: true, 
																						mode:"javascript",json:true,
																						mode:"css",css:true , 
																						htmlMode: true
																					});
									},
									close : function(textarea, instance) { 
										this.myCodeMirror = null; 
									},
									save : function(textarea, editor) {
										textarea.value = this.myCodeMirror.getValue(); 
									}
								}] //editors 
							} //edit
						} //commandsOptions 
					}).elfinder("instance");
				');
			}




			// Show filemanager

		}
	}

}


namespace {

	class elFinderVolumeATKFileStore extends \elFinderVolumeDriver{

		protected $driverId='atk';
		protected $sessionCaching = array('rootstat' => false, 'subdirs' => false);

		public function __construct() {
			$opts = array(
				'tmbPath'       => '',
				'tmpPath'       => '',
				'rootCssClass'  => 'elfinder-navbar-root-atk',
				'noSessionCache' => array('hasdirs')
			);
			$this->options = array_merge($this->options, $opts);
			$this->options['mimeDetect'] = 'internal';
			$this->api = $this->app = $GLOBALS['api'];

		}

		function init(){			
			// $this->updateCache($this->options['path'], $this->_stat($this->options['path']));
			return true;
		}

		/**
		 * Set tmp path
		 *
		 * @return void
		 * @author Dmitry (dio) Levashov
		 **/
		protected function configure() {
			parent::configure();

			if (($tmp = $this->options['tmpPath'])) {
				if (!file_exists($tmp)) {
					if (mkdir($tmp)) {
						chmod($tmp, $this->options['tmbPathMode']);
					}
				}
				
				$this->tmpPath = is_dir($tmp) && is_writable($tmp) ? $tmp : false;
			}
			if (!$this->tmpPath && ($tmp = elFinder::getStaticVar('commonTempPath'))) {
				$this->tmpPath = $tmp;
			}
			
			if (!$this->tmpPath && $this->tmbPath && $this->tmbPathWritable) {
				$this->tmpPath = $this->tmbPath;
			}

			$this->mimeDetect = 'internal';
		}

		/**
		 * Close connection
		 *
		 * @return void
		 * @author Dmitry (dio) Levashov
		 **/
		public function umount() {
			return true;
		}

		/**
		 * Return debug info for client
		 *
		 * @return array
		 * @author Dmitry (dio) Levashov
		 **/
		public function debug() {
			$debug = parent::debug();
			if ($this->dbError) {
				$debug['dbError'] = $this->dbError;
			}
			return $debug;
		}

		/**
		 * Create empty object with required mimetype
		 *
		 * @param  string  $path  parent dir path
		 * @param  string  $name  object name
		 * @param  string  $mime  mime type
		 * @return bool
		 * @author Dmitry (dio) Levashov
		 **/
		protected function make($path, $name, $mime) {
			
			$file = $this->app->add('xepan\hr\Model_File');
			$file['parent_id']=$path;
			$file['name']=$name;
			$file['mime']=$mime;
			$file->save();
			return $file->loaded();
		}

		/*********************************************************************/
		/*                               FS API                              */
		/*********************************************************************/
		
		/**
		 * Cache dir contents
		 *
		 * @param  string  $path  dir path
		 * @return string
		 * @author Dmitry Levashov
		 **/
		protected function cacheDir($path) {
			$this->dirsCache[$path] = array();

			$sql = 'SELECT f.id, f.parent_id, f.name, f.size, f.mtime AS ts, f.mime, f.read, f.write, f.locked, f.hidden, f.width, f.height, IF(ch.id, 1, 0) AS dirs 
					FROM '.$this->tbf.' AS f 
					LEFT JOIN '.$this->tbf.' AS ch ON ch.parent_id=f.id AND ch.mime=\'directory\'
					WHERE f.parent_id=\''.$path.'\'
					GROUP BY f.id';
					
			$res = $this->query($sql);
			if ($res) {
				while ($row = $res->fetch_assoc()) {
					$id = $row['id'];
					if ($row['parent_id']) {
						$row['phash'] = $this->encode($row['parent_id']);
					} 
					
					if ($row['mime'] == 'directory') {
						unset($row['width']);
						unset($row['height']);
						$row['size'] = 0;
					} else {
						unset($row['dirs']);
					}
					
					unset($row['id']);
					unset($row['parent_id']);
					
					
					
					if (($stat = $this->updateCache($id, $row)) && empty($stat['hidden'])) {
						$this->dirsCache[$path][] = $id;
					}
				}
			}
			
			return $this->dirsCache[$path];
		}

		// protected function updateCache($path, $stat) {
		// 	return;
		// }

		// ========== Abstract functions implementation

		 /** @author Dmitry (dio) Levashov
		 **/
		function _dirname($path){
			return ($stat = $this->stat($path)) ? (!empty($stat['phash']) ? $this->decode($stat['phash']) : $this->root) : false;
		}

		/**
		 * Return file name
		 *
		 * @param  string  $path  file path
		 * @return string
		 * @author Dmitry (dio) Levashov
		 **/
		function _basename($path){
			return $path;
		}

		/**
		 * Join dir name and file name and return full path
		 *
		 * @param  string  $dir
		 * @param  string  $name
		 * @return string
		 * @author Dmitry (dio) Levashov
		 **/
		protected function _joinPath($dir, $name) {
			$sql = 'SELECT id FROM '.$this->tbf.' WHERE parent_id=\''.$dir.'\' AND name=\''.$this->db->real_escape_string($name).'\'';

			if (($res = $this->query($sql)) && ($r = $res->fetch_assoc())) {
				$this->updateCache($r['id'], $this->_stat($r['id']));
				return $r['id'];
			}
			return -1;
		}

		/**
		 * Return normalized path 
		 *
		 * @param  string  $path  file path
		 * @return string
		 * @author Dmitry (dio) Levashov
		 **/
		function _normpath($path){
			return $path;
		}

		/**
		 * Return file path related to root dir
		 *
		 * @param  string  $path  file path
		 * @return string
		 * @author Dmitry (dio) Levashov
		 **/
		function _relpath($path){
			return $path;
		}
		
		/**
		 * Convert path related to root dir into real path
		 *
		 * @param  string  $path  rel file path
		 * @return string
		 * @author Dmitry (dio) Levashov
		 **/
		function _abspath($path){
			return $path;
		}
		
		/**
		 * Return fake path started from root dir.
		 * Required to show path on client side.
		 *
		 * @param  string  $path  file path
		 * @return string
		 * @author Dmitry (dio) Levashov
		 **/
		function _path($path){
			return $path;
		}
		
		/**
		 * Return true if $path is children of $parent
		 *
		 * @param  string  $path    path to check
		 * @param  string  $parent  parent path
		 * @return bool
		 * @author Dmitry (dio) Levashov
		 **/
		function _inpath($path, $parent){
			return false;
		}
		
		/**
		 * Return stat for given path.
		 * Stat contains following fields:
		 * - (int)    size    file size in b. required
		 * - (int)    ts      file modification time in unix time. required
		 * - (string) mime    mimetype. required for folders, others - optionally
		 * - (bool)   read    read permissions. required
		 * - (bool)   write   write permissions. required
		 * - (bool)   locked  is object locked. optionally
		 * - (bool)   hidden  is object hidden. optionally
		 * - (string) alias   for symlinks - link target path relative to root path. optionally
		 * - (string) target  for symlinks - link target path. optionally
		 *
		 * If file does not exists - returns empty array or false.
		 *
		 * @param  string  $path    file path 
		 * @return array|false
		 * @author Dmitry (dio) Levashov
		 **/
		function _stat($path){
			// $sql = 'SELECT f.id, f.parent_id, f.name, f.size, f.mtime AS ts, f.mime, f.read, f.write, f.locked, f.hidden, f.width, f.height, IF(ch.id, 1, 0) AS dirs
			// 	FROM '.$this->tbf.' AS f 
			// 	LEFT JOIN '.$this->tbf.' AS p ON p.id=f.parent_id
			// 	LEFT JOIN '.$this->tbf.' AS ch ON ch.parent_id=f.id AND ch.mime=\'directory\'
			// 	WHERE f.id=\''.$path.'\'
			// 	GROUP BY f.id';

			// $res = $this->query($sql);
			
			$res = $this->app->add('xepan\hr\Model_File');
			$res->tryLoad($path);

			if ($res->loaded()) {
				$stat = $res->get();
				if ($stat['parent_id']) {
					$stat['phash'] = $this->encode($stat['parent_id']);
				} 
				if ($stat['type'] == 'Folder') {
					unset($stat['width']);
					unset($stat['height']);
					$stat['size'] = 0;
					$stat['mime'] = 'directory';
				} else {
					unset($stat['dirs']);
				}
				unset($stat['id']);
				unset($stat['parent_id']);
				return $stat;
				
			}
			return array();
		}
		

		/***************** file stat ********************/

			
		/**
		 * Return true if path is dir and has at least one childs directory
		 *
		 * @param  string  $path  dir path
		 * @return bool
		 * @author Dmitry (dio) Levashov
		 **/
		function _subdirs($path){
			return ($stat = $this->stat($path)) && isset($stat['dirs']) ? $stat['dirs'] : false;
		}
		
		/**
		 * Return object width and height
		 * Ususaly used for images, but can be realize for video etc...
		 *
		 * @param  string  $path  file path
		 * @param  string  $mime  file mime type
		 * @return string
		 * @author Dmitry (dio) Levashov
		 **/
		function _dimensions($path, $mime){
			return ($stat = $this->stat($path)) && isset($stat['width']) && isset($stat['height']) ? $stat['width'].'x'.$stat['height'] : '';
		}
		
		/******************** file/dir content *********************/

		/**
		 * Return files list in directory
		 *
		 * @param  string  $path  dir path
		 * @return array
		 * @author Dmitry (dio) Levashov
		 **/
		function _scandir($path){
			return [false];
			return isset($this->dirsCache[$path])
			? $this->dirsCache[$path]
			: $this->cacheDir($path);
		}
		
		/**
		 * Open file and return file pointer
		 *
		 * @param  string $path file path
		 * @param  string $mode open mode
		 * @return resource|false
		 * @author Dmitry (dio) Levashov
		 **/
		function _fopen($path, $mode="rb"){

		}
		
		/**
		 * Close opened file
		 * 
		 * @param  resource  $fp    file pointer
		 * @param  string    $path  file path
		 * @return bool
		 * @author Dmitry (dio) Levashov
		 **/
		function _fclose($fp, $path=''){

		}
		
		/********************  file/dir manipulations *************************/
		
		/**
		 * Create dir and return created dir path or false on failed
		 *
		 * @param  string  $path  parent dir path
		 * @param string  $name  new directory name
		 * @return string|bool
		 * @author Dmitry (dio) Levashov
		 **/
		function _mkdir($path, $name){
			return $this->make($path,$name,'directory') ? $this->_joinPath($path, $name) : false;
		}
		
		/**
		 * Create file and return it's path or false on failed
		 *
		 * @param  string  $path  parent dir path
		 * @param string  $name  new file name
		 * @return string|bool
		 * @author Dmitry (dio) Levashov
		 **/
		function _mkfile($path, $name){

		}
		
		/**
		 * Create symlink
		 *
		 * @param  string  $source     file to link to
		 * @param  string  $targetDir  folder to create link in
		 * @param  string  $name       symlink name
		 * @return bool
		 * @author Dmitry (dio) Levashov
		 **/
		function _symlink($source, $targetDir, $name){

		}

		/**
		 * Copy file into another file (only inside one volume)
		 *
		 * @param  string $source source file path
		 * @param $targetDir
		 * @param  string $name file name
		 * @return bool|string
		 * @internal param string $target target dir path
		 * @author Dmitry (dio) Levashov
		 */
		function _copy($source, $targetDir, $name){

		}

		/**
		 * Move file into another parent dir.
		 * Return new file path or false.
		 *
		 * @param  string $source source file path
		 * @param $targetDir
		 * @param  string $name file name
		 * @return bool|string
		 * @internal param string $target target dir path
		 * @author Dmitry (dio) Levashov
		 */
		function _move($source, $targetDir, $name){

		}
		
		/**
		 * Remove file
		 *
		 * @param  string  $path  file path
		 * @return bool
		 * @author Dmitry (dio) Levashov
		 **/
		function _unlink($path){

		}

		/**
		 * Remove dir
		 *
		 * @param  string  $path  dir path
		 * @return bool
		 * @author Dmitry (dio) Levashov
		 **/
		function _rmdir($path){

		}

		/**
		 * Create new file and write into it from file pointer.
		 * Return new file path or false on error.
		 *
		 * @param  resource  $fp   file pointer
		 * @param  string    $dir  target dir path
		 * @param  string    $name file name
		 * @param  array     $stat file stat (required by some virtual fs)
		 * @return bool|string
		 * @author Dmitry (dio) Levashov
		 **/
		function _save($fp, $dir, $name, $stat){

		}
		
		/**
		 * Get file contents
		 *
		 * @param  string  $path  file path
		 * @return string|false
		 * @author Dmitry (dio) Levashov
		 **/
		function _getContents($path){

		}
		
		/**
		 * Write a string to a file
		 *
		 * @param  string  $path     file path
		 * @param  string  $content  new file content
		 * @return bool
		 * @author Dmitry (dio) Levashov
		 **/
		function _filePutContents($path, $content){

		}

		/**
		 * Extract files from archive
		 *
		 * @param  string  $path file path
		 * @param  array   $arc  archiver options
		 * @return bool
		 * @author Dmitry (dio) Levashov, 
		 * @author Alexey Sukhotin
		 **/
		function _extract($path, $arc){

		}

		/**
		 * Create archive and return its path
		 *
		 * @param  string  $dir    target dir
		 * @param  array   $files  files names list
		 * @param  string  $name   archive name
		 * @param  array   $arc    archiver options
		 * @return string|bool
		 * @author Dmitry (dio) Levashov, 
		 * @author Alexey Sukhotin
		 **/
		function _archive($dir, $files, $name, $arc){

		}

		/**
		 * Detect available archivers
		 *
		 * @return void
		 * @author Dmitry (dio) Levashov, 
		 * @author Alexey Sukhotin
		 **/
		function _checkArchivers(){

		}

		/**
		 * Change file mode (chmod)
		 *
		 * @param  string  $path  file path
		 * @param  string  $mode  octal string such as '0755'
		 * @return bool
		 * @author David Bartle,
		 **/
		function _chmod($path, $mode){

		}

	}
}
