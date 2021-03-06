<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>OOCSS: Object Oriented CSS [VERSION]</title>
	<link rel='stylesheet' type='text/css' href='styles.oocss' />
</head>
<body>


<!--
Object Oriented CSS [VERSION]
[DATE]
Corey Hart @ http://www.codenothing.com
-->


<div id='warning'>
	<h1 style='color:red;'>Please make sure that your cache/ directory has full permissions</h1>
</div>

<h1>OOCSS: Object Oriented CSS [VERSION]</h1>

<p>
Object Oriented CSS is a new way to write CSS files to speed up development 
by using parent relations and variable storage.
</p>


<p>
Nothing is easy to explain without an example, so I've included the stylesheet
used on this page below. (Please use firefox for iframes to display properly).
</p>


<table><tr>
<td width=600>
Unparsed <a href='styles.oocss'>styles.oocss</a>
<pre><?= htmlspecialchars(file_get_contents(dirname(__FILE__).'/styles.oocss'), ENT_QUOTES) ?></pre>
</td>
<td>
Parsed <a href='styles.oocss'>styles.oocss</a>
<iframe src='styles.oocss'></iframe>
</td>
</tr></table>


<p>
OOCSS takes what would normally be a painfully large, unintuitive style sheet,
and turns it into less code for better maintenance. All parsed scripts are cached
server side.
</p>







<h2>How it Works</h2>

<h4>Variables</h4>
<p>
OOCSS can parse both single and multiple definition varaibles. Single definition varibales
must follow the following pattern:

	<code>$var = prop:value;</code>

You can think of multiple definitions as an array. The array must be be enclosed
in brackets trailed by a semi-colon, with each definition seperated by a comma. Ex:

	<code>$multi_var = { prop1:value1, prop2:value2, prop3:value3 }</code>

When using a variable, they must be followed by a semi-colon, or they will not be replaced
with their values.
</p>






<h4>Parsing</h4>
<p>
The file is parsed like a DOM Tree, where inner elements are considered children of
the wrapped element. If you set the OOCSS_DEBUG_MODE defined variable to true, you can
get a better picture of what variables are being stored, and how the tree looks. Here's
what this pages stylesheet tree looks like:
</p>

<iframe src='styles-tree.php'></iframe>

<p>
To prevent long loads, parsed files are cached into a seperate directory. The files are
stored using a combination of both oocss.php and your oocss last change times, so you
can be assured that any changes made will be automatically parsed.
</p>





<div id='footer'>
Have any questions? Found a bug? <a href='mailto:corey@codenothing.com?Subject=OOCSS Question/Bug'>Let me know</a>
</div>



<div style='margin:50px 0;'>
	<a href='http://www.codenothing.com/archives/other/oocss/'>&laquo; Back to Original Article</a>
</div>



</body>
</html>
