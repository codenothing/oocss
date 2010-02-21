<?
/**
 * Object Oriented CSS [VERSION]
 * [DATE]
 * Corey Hart @ http://www.codenothing.com
 *
 * Credit to Thiemo Mättig @ http://maettig.com/ for his regex help
 */ 


Class ObjectOrientedCSS
{
	/**
	 * Class Vars
	 *
	 * @param (array) vars: Holds variables found in css file
	 * @param (string) file: Contents of css file
	 * @param (array) tree: Holds parsed tree found in css file
	 */ 
	private $vars = array();
	private $tree = array();
	private $file = '';

	/**
	 * Constructer will automatically parse any string
	 * passed into it. To retrieve it, call __get('file').
	 *
	 * @param (string) css: Contents of css file
	 */ 
	public function __construct($css){
		if (is_string($css) && strlen($css) > 0)
			$this->run($css);
	}

	// Allow access to all vars
	public function __get($name){
		return isset($this->$name) ? $this->$name : FALSE;
	}

	// Disallow changing class vars
	private function __set($name, $value){}

	/**
	 * Centralized function to run the OOCSS Parser
	 *
	 * @param (string) css: Contents of css file
	 */ 
	public function run($css){
		// Store contents into class var
		$this->file = $css;

		// Raw file trimmed down to single line
		$this->trimCSS();

		// Run variable conversions first
		$this->variableReplacement();

		// Loop through and create the tree (Pass file as var, for loop)
		$this->prepFileForLoop();
		$this->tree = $this->convertLoop($this->file);

		// Process tree for output (Pass as var, for loops)
		$this->file = $this->processFile($this->tree);

		// Return parsed file for quick output
		return $this->file;
	}

	/**
	 * Trim the css file into single line and remove comments
	 *
	 * @params none
	 */ 
	private function trimCSS(){
		$search = array("/\/\*(.*?)\*\//s", "/\s+/s");
		$replace = array('', ' ');
		$this->file = preg_replace($search, $replace, $this->file);
	}

	/**
	 * Find and replace variables
	 *
	 * @params none
	 */ 
	private function variableReplacement(){
		// Only variables use the '=' sign
		preg_match_all("/([$]\w+)\s*=\s*([^;]+);/s", $this->file, $matches);
		for ($i=0, $imax=count($matches[0]); $i<$imax; $i++){
			// Matches
			$string = $matches[0][$i];
			$var = $matches[1][$i];
			$value = $matches[2][$i];

			// Check for multiple definitions
			if (strpos($value, '{') !== false){
				$search = array('{', '}', ',');
				$replace = array('', '', ';');
				$value = str_replace($search, $replace, $value);
			}

			// Store the var for debugging
			$this->vars[$var] = $value;

			// Remove the declaration from the file
			$this->file = str_replace($string, '', $this->file);

			// Enforce semi-colon replacement
			$this->file = str_replace($var.';', $value.';', $this->file);
		}
	}

	/**
	 * Reformat css contents into seperate lines
	 * for line-by-line parsing
	 *
	 * @params none
	 */ 
	private function prepFileForLoop(){
		// Line out as much as possible
		$this->file = preg_replace("/([{]|[}]|;)/", "$1\n", $this->file);

		// Return each line
		$this->file = explode("\n", str_replace('}', "\n}", $this->file));
	}

	/**
	 * Create tree structure from formatted contents.
	 * Recursively calls itself to reach multiple levels
	 *
	 * @param (array) file: Reformated css contents
	 */ 
	private function convertLoop(&$file){
		$ret = $old = array();
		while ($file){
			$line = trim(array_shift($file));
			if (!$line || $line == ''){
				continue;
			}
			else if (strpos($line, '{') !== false){
				// Take out the tag
				$tag = trim(array_shift(explode('{', $line)));

				// Store old tag for merging
				if (isset($ret[$tag]))
					$old[$tag] = $ret[$tag];

				// Run recursive loop of all levels
				$ret[$tag] = $this->convertLoop($file);

				// If old tag found, merge results
				if ($old){
					$ret[$tag] = array_merge_recursive($old[$tag], $ret[$tag]);
					$old = array();
				}
			}
			else if (strpos($line, '}') !== false){
				return $ret;
			}
			else{
				$ret['props'][] = $line;
			}
		}
		return $ret;
	}

	/**
	 * Create standard CSS file from processed tree
	 *
	 * @param (array) file: Tree parsed from contents
	 * @param (string) tag: Current tag depth
	 */ 
	private function processFile($file, $tag=''){
		// Only add props if it exists with a tag
		if (($tag = trim($tag)) && $tag != '' && $file['props']){
			// Remove Lingering direct descendent tag and trim it
			$trimedTag = trim(preg_replace('/[>]$/', '', $tag));
			$str = "$trimedTag {\n\t" . implode("\n\t", $file['props']) . "\n}\n\n";
		}

		// Loop through nested levels
		foreach ($file as $key => $value)
			if ($key != 'props') 
				$str .= $this->processFile($value, $tag.' '.$key);

		// Return concatenated string
		return $str;
	}
};

?>
