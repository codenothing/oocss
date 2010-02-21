<?
/**
 * Object Oriented CSS 2.0
 * September 5, 2009
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
	var $vars = array();
	var $file = '';
	var $tree = array();

	/**
	 * Centralized function to run the OOCSS Parser
	 *
	 * @param (string) css: Contents of css file
	 */ 
	function run($css){
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
	 * Format the css file into single line
	 *
	 * @params none
	 */ 
	function trimCSS(){
		// Remove CSS Comments
		$this->file = preg_replace("/\/\*(.*?)\*\//s", '', $this->file);
		// Remove Multiple Spaces (Credit goes to Thiemo Mättig @ http://maettig.com/ for pointing out this regex)
		$this->file = preg_replace("/\s+/s", ' ', $this->file);
	}

	/**
	 * Find and replace variables
	 *
	 * @params none
	 */ 
	function variableReplacement(){
		// Credit goes to Thiemo Mättig @ http://maettig.com/ for pointing out this regex
		preg_match_all("/([$]\w+)\s*=\s*([^;]+);/s", $this->file, $matches);
		for ($i=0, $imax=count($matches[0]); $i<$imax; $i++){
			// Matches
			$string = $matches[0][$i];
			$var = $matches[1][$i];
			$value = $matches[2][$i];

			// Check for multiple definitions
			if (strpos($value, '{') !== false){
				$value = str_replace('{', '', $value);
				$value = str_replace('}', '', $value);
				$value = str_replace(',', ';', $value);
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
	function prepFileForLoop(){
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
	function convertLoop(&$file){
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
					$old = $ret;

				// Run recursive loop of all levels
				$ret[$tag] = $this->convertLoop($file);

				// If old tag found, merge results
				if ($old){
					$ret = array_merge_recursive($old, $ret);
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
	function processFile($file, $tag=''){
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

$oocss = new ObjectOrientedCSS;
?>
