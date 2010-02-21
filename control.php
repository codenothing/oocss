<?
/**
 * Object Oriented CSS [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 */ 

// Base directory away
$dir = dirname(__FILE__);
// Cache Directory & Debugging Mode
define('OOCSS_CACHE_DIR', $dir . '/cache/');
define('OOCSS_DEBUG_MODE', false);
// Path to OOCSS parser
define('OOCSS_PARSER', $dir . '/oocss.php');


Class OOCSScontrol
{
	/**
	 * Class Variables
	 *
	 * @param (string) file_path: Path to requested file
	 * @param (string) cache_path: Path to cached parsed file
	 * @param (array) mtime: Holds various file last time changes
	 */
	private $file_path = '';
	private $cache_path = '';
	private $mtime = array();

	/**
	 * Cached files are stored based on various files
	 * last change dates (see setCachePath() method),
	 * run the parser based on that
	 *
	 * @params none
	 */ 
	public function __construct(){
		// File Path
		$this->setFilePath();
		$this->setCachePath();

		// If there have been updates, recompile
		if ($this->mtime['file'] > $this->mtime['cache'] || 
			$this->mtime['oocss'] > $this->mtime['cache'] || 
			$this->mtime['control'] > $this->mtime['cache']){
				$this->compile();
		} else {
			$this->mtime['cache'] = filemtime($this->cache_path);
		}
	}

	/**
	 * oocss files should be redirected to this main file,
	 * so use apaches REDIRECT_URL to find the file being
	 * requested.
	 *
	 * @params none
	 */ 
	private function setFilePath(){
		// Use current selected path if possible
		$this->file_path = $_SERVER['DOCUMENT_ROOT'] . $_SERVER['REDIRECT_URL'];
		if (! is_file($this->file_path)){
			// See if there's a css extension of the file
			$this->file_path = preg_replace("/\.oocss$/i", '.css', $this->file_path);
			if (! is_file($this->file_path))
				die('/* No Stylesheet Found */');
		}
	}

	/**
	 * Cached Files are stored based on file change times of
	 * this control file, the OOCSS parser, and the oocss
	 * file being parsed.
	 *
	 * @params none
	 */ 
	private function setCachePath(){
		// Store the last modified times
		$this->mtime['file'] = filemtime($this->file_path);
		$this->mtime['oocss'] = filemtime(OOCSS_PARSER);
		$this->mtime['control'] = filemtime(__FILE__);

		// New hash of file only
		$this->cache_path = OOCSS_CACHE_DIR . md5($this->file_path) . '.css';

		// Store the make time if possible
		$this->mtime['cache'] = is_file($this->cache_path) ? filemtime($this->cache_path) : 0;
	}

	/**
	 * Run the parser, and cache the results
	 *
	 * @params none
	 */ 
	private function compile(){
		// Get and run parser
		require(OOCSS_PARSER);
		$oocss = new ObjectOrientedCSS( file_get_contents($this->file_path) );
		$file = $oocss->__get('file');

		// Add commented out notes in debug mode
		if (OOCSS_DEBUG_MODE){
			// Prepend debug string to file
			$file = "/*******\nOOCSS DEBUGGING TREES"
				."\n\nVariables Stored: "
				.print_r($oocss->__get('vars'), true)
				."\n\nTree Parsed: "
				.print_r($oocss->__get('tree'), true)
				."******/\n\n\n"
				.$file;
		}

		// Cache compression
		$fh = fopen($this->cache_path, 'w');
		fwrite($fh, $file);
		fclose($fh);

		// Set new time for cached file
		$this->mtime['cache'] = time();
	}

	/**
	 * Attach caching headers and output stored cached file
	 *
	 * @params none
	 */ 
	public function __destruct(){
		ob_start();
		header('Content-type: text/css');
		header('Expires: '.gmdate('D, d M Y H:i:s', time() + 3600*24*7).' GMT');
		header('Last Modified: '.gmdate('D, d M Y H:i:s', $this->mtime['cache']));
		echo file_get_contents($this->cache_path);
		ob_end_flush();
	}
};

new OOCSScontrol;
?>
