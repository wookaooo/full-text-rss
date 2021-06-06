<?php
require_once(dirname(__FILE__).'/config.php');
// check for custom index.php (custom_index.php)
if (!defined('_FF_FTR_INDEX')) {
	define('_FF_FTR_INDEX', true);
	if (file_exists(dirname(__FILE__).'/custom_index.php')) {
		include(dirname(__FILE__).'/custom_index.php');
		exit;
	}
}
?><!DOCTYPE html>
<html>
  <head>
    <title>Full-Text RSS Feeds Proxy</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />	
	<meta name="robots" content="noindex, follow" />
	<link rel="stylesheet" href="css/bootstrap.min.css" type="text/css" media="screen" />
	<script type="text/javascript" src="js/jquery.min.js"></script>
	<script type="text/javascript" src="js/bootstrap-tooltip.js"></script>
	<script type="text/javascript" src="js/bootstrap-popover.js"></script>
	<script type="text/javascript" src="js/bootstrap-tab.js"></script>
	<script type="text/javascript">
	var baseUrl = 'http://'+window.location.host+window.location.pathname.replace(/(\/index\.php|\/)$/, '');
	$(document).ready(function() {
		// remove http scheme from urls before submitting
		$('#form').submit(function() {
			$('#url').val($('#url').val().replace(/^http:\/\//i, ''));
			$('#url').val($('#url').val().replace(/^https:\/\//i, 'sec://'));
			return true;
		});
		// popovers
		$('#url').popover({offset: 10, placement: 'left', trigger: 'focus', html: true});
		$('#key').popover({offset: 10, placement: 'left', trigger: 'focus', html: true});
		$('#max').popover({offset: 10, placement: 'left', trigger: 'focus', html: true});
		$('#links').popover({offset: 10, placement: 'left', trigger: 'focus', html: true});
		$('#exc').popover({offset: 10, placement: 'left', trigger: 'focus', html: true});
		// tooltips
		$('a[rel=tooltip]').tooltip();
	});
	</script>
	<style>
	html {width: 100%; height: 100%; }	
	html, body { background: linear-gradient(180deg, #edf7fe 0%, white 95%, white 100%); }
	body { margin: 0; line-height: 1.4em; font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; }
	label, input, select, textarea { font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif; }
	li { color: #404040; }
	li.active a { font-weight: bold; color: #666 !important; }
	form .controls { margin-left: 220px !important; }
	label { width: 200px !important; }
	fieldset legend { padding-left: 220px; line-height: 20px !important; margin-bottom: 10px !important;}
	.form-actions { padding-left: 220px !important; }
	.popover-inner { width: 205px; }
	h1 { margin-bottom: 18px; }

	/* JSON Prettify CSS from http://chris.photobooks.com/json/default.htm */
	.jsonOutput.PRETTY {
		font-family: Consolas, "Courier New", monospace;
		background-color: #333;
		color: #fff;
		padding: 10px; 
		border-radius: 4px;
	}
	.ERR             { color: #FF0000; font-weight: bold; }
	.FUNC            { color: #FF0000; font-weight: bold; }
	.IDK             { color: #FF0000; font-weight: bold; }
	.KEY             { color: #FFFFFF; font-weight: bold; }
	.BOOL            { color: #00FFFF; }
	.NUMBER          { color: #7FFF00; }
	.DATE            { color: #6495ED; }
	.REGEXP          { color: #DEB887; }
	.STRING          { color: #D8FFB0; }
	.UNDEF           { color: #91AA9D; font-style: italic; }
	.NULL            { color: #91AA9D; font-style: italic; }
	.EMPTY           { color: #91AA9D; font-style: italic; }
	.HTML span.ARRAY { color: #91AA9D; font-style: italic; }
	.HTML span.OBJ   { color: #91AA9D; font-style: italic; }
	table.OBJ        { background-color: #22353C; }
	table.ARRAY      { background-color: #252C47; }

	</style>
  </head>
	
	
	
  <body>
	<div class="container" style="width: 800px; padding-bottom: 30px;">
	<h1 style="padding-top: 180px;text-align: center;">RSS Feed 全文输出</h1>
	<h2>从外网feed或者网页URL,创建全文feed</h2>
    <div method="get" action="makefulltextfeed.php" id="form" class="form-horizontal">
    <div class="control-group">
		<label class="control-label" for="url">输入feed链接或者网址：</label>
		<div class="controls"><input type="text" id="url" name="url" style="width: 450px;"  /></div>
		</div>
	</div>
	    
		<div class="form-actions" style="background-color: transparent; border-color: transparent; margin-top: 0px; "  >
		<input type="submit" id="sudbmit" name="submit" value="Create Feed" class="btn btn-primary" style="background-color: #1B9AF7;
border-color: #1B9AF7; border-radius: 4px;" line-height: 50px; padding: 0 50px; font-size: 16px; />
	</div>	
		
	<?php if (isset($options->api_keys) && !empty($options->api_keys)) { ?>
	<?php } ?>
	<?php
	// echo '<select name="max" id="max" class="input-medium">'
	// for ($i = 1; $i <= $options->max_entries; $i++) {
	//	printf("<option value=\"%s\"%s>%s</option>\n", $i, ($i==$options->default_entries) ? ' selected="selected"' : '', $i);
	// } 
	// echo '</select>';
	if (!empty($options->api_keys)) {
		$msg = 'Limit: '.$options->max_entries.' (with key: '.$options->max_entries_with_key.')';
		$msg_more = 'If you need more items, change <tt>max_entries</tt> (and <tt>max_entries_with_key</tt>) in config.';
	} else {
		$msg = 'Limit: '.$options->max_entries;
		$msg_more = 'If you need more items, change <tt>max_entries</tt> in config.';
	}
	?>	
	<?php if ($options->exclude_items_on_fail == 'user') { ?><?php } ?>
	<?php if ($options->summary == 'user') { ?><?php } ?>

	

	<p style="font-size: 13px; color: #08c; text-decoration: none; text-align: center; margin: 0 0 9px; " >
    代码 <a href="http://fivefilters.org">fivefilters</a>
    丨托管 <a href="https://dashboard.heroku.com/">heroku</a>
    丨CDN <a href="https://dash.cloudflare.com/">cloudflare</a>
	</p>

	</div>
  </body>
</html>
