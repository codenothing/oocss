<pre>
<?
// OOCSS Parser
require('oocss.php');

// Parse the styles pages
$oocss = new ObjectOrientedCSS(file_get_contents('styles.oocss'));

// Print out the tree
print_r($oocss->__get('tree'));
?>
</pre>
