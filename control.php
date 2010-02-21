<?
/**
 * Object Oriented CSS 2.0
 * September 5, 2009
 * Corey Hart @ http://www.codenothing.com
 */ 

// Cache Directory & Debugging Mode
define('OOCSS_CACHE_DIR', dirname(__FILE__).'/cache/');
define('OOCSS_DEBUG_MODE', false);
// Path to OOCSS parser
define('OOCSS_PARSER', dirname(__FILE__).'/oocss.php');


Class OOCSScontrol
{
	/**
	 * Class Variables
	 *
	 * @param (string) file_path: Path to requested file
	 * @param (string) cache_path: Path to cached parsed file
	 * @param (array) mtime: Holds various file last time changes
	 */ 
	var $file_path = '';
	var $cache_path = '';
	var $mtime = array();

	/**
	 * Cached files are stored based on various files
	 * last change dates (see setCachePath() method),
	 * run the parser based on that
	 *
	 * @params none
	 */ 
	function __construct(){
		// File Path
		$this->setFilePath();
		$this->setCachePath();

		// If there is no cached file, parse requested file
		if (! is_file($this->cache_path)){
			$this->compile();
		}else{
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
	function setFilePath(){
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
	function setCachePath(){
		// Store the last modified times
		$this->mtime['file'] = filemtime($this->file_path);
		$this->mtime['oocss'] = filemtime(OOCSS_PARSER);
		$this->mtime['control'] = filemtime(__FILE__);

		// Filname to hash out
		$hash = $this->file_path . $this->mtime['file'] . $this->mtime['oocss'] . $this->mtime['control'];
		$this->cache_path = OOCSS_CACHE_DIR.md5($hash).'.css';
	}

	/**
	 * Run the parser, and cache the results
	 *
	 * @params none
	 */ 
	function compile(){
		// Get and run parser
		require(OOCSS_PARSER);
		$oocss->run(file_get_contents($this->file_path));

		// Add commented out notes in debug mode
		if (OOCSS_DEBUG_MODE){
			// Prepend debug string to file
			$oocss->file = "/*******\nOOCSS DEBUGGING TREES"
				."\n\nVariables Stored: "
				.print_r($oocss->vars, true)
				."\n\nTree Parsed: "
				.print_r($oocss->tree, true)
				."******/\n\n\n\n"
				.$oocss->file;
		}

		// Cache compression
		$fh = fopen($this->cache_path, 'w');
		fwrite($fh, $oocss->file);
		fclose($fh);

		// Set new time for cached file
		$this->mtime['cache'] = time();
	}

	/**
	 * Attach caching headers and output stored cached file
	 *
	 * @params none
	 */ 
	function __destruct(){
		header('Content-type: text/css');
		header('Expires: '.gmdate('D, d M Y H:i:s', time() + 3600*24*7).' GMT');
		header('Last Modified: '.gmdate('D, d M Y H:i:s', $this->mtime['cache']));
		echo file_get_contents($this->cache_path);
	}
};

new OOCSScontrol;
?>
