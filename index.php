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
	  
	  <div class="container" style="width: 800px; padding-bottom: 60px;">
	<h1 style="padding-top: 5px;">Full-Text RSS <?php echo _FF_FTR_VERSION; ?> <span style="font-size: .7em; font-weight: normal;">&mdash; from <a href="http://fivefilters.org">FiveFilters.org</a></span></h1>
    <form method="get" action="makefulltextfeed.php" id="form" class="form-horizontal">
	<fieldset>
		<legend>Create full-text feed from feed or webpage URL</legend>
		<div class="control-group">
			<label class="control-label" for="url">Enter URL</label>
			<div class="controls"><input type="text" id="url" name="url" style="width: 450px;" title="URL" data-content="Typically this is a URL for a partial feed which we transform into a full-text feed. But it can also be a standard web page URL, in which case we'll extract its content and return it in a 1-item feed." /></div>
		</div>
	</fieldset>
	<fieldset>
	<legend>Options</legend>
	<?php if (isset($options->api_keys) && !empty($options->api_keys)) { ?>
	<div class="control-group">
	<label class="control-label" for="key">Access key</label>
	<div class="controls">
	<input type="text" id="key" name="key" class="input-medium" <?php if ($options->key_required) echo 'required'; ?> title="Access Key" data-content="<?php echo ($options->key_required) ? 'An access key is required to generate a feed' : 'If you have an access key, enter it here.'; ?>" />
	</div>
	</div>
	<?php } ?>
	<div class="control-group">
	<label class="control-label" for="max">Max items</label>
	<div class="controls">
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
	<input type="text" name="max" id="max" class="input-mini" value="<?php echo $options->default_entries; ?>" title="Feed item limit" data-content="Set the maximum number of feed items we should process. The smaller the number, the faster the new feed is produced.<br /><br />If your URL refers to a standard web page, this will have no effect: you will only get 1 item.<br /><br /> <?php echo $msg_more; ?>" />
	<span class="help-inline" style="color: #888;"><?php echo $msg; ?></span>
	</div>
	</div>
	<div class="control-group">
	<label class="control-label" for="links">Links</label>
	<div class="controls">
	<select name="links" id="links" class="input-medium" title="Link handling" data-content="By default, links within the content are preserved. Change this field if you'd like links removed, or included as footnotes.">
		<option value="preserve" selected="selected">preserve</option>
		<option value="footnotes">add to footnotes</option>
		<option value="remove">remove</option>
	</select>
	</div>
	</div>
	<?php if ($options->exclude_items_on_fail == 'user') { ?>
	<div class="control-group">
	<label class="control-label" for="exc">If extraction fails</label>
	<div class="controls">
	<select name="exc" id="exc" title="Item handling when extraction fails" data-content="If extraction fails, we can remove the item from the feed or keep it in.<br /><br />Keeping the item will keep the title, URL and original description (if any) found in the feed. In addition, we insert a message before the original description notifying you that extraction failed.">
		<option value="" selected="selected">keep item in feed</option>
		<option value="1">remove item from feed</option>
	</select>
	</div>
	</div>
	<?php } ?>
	
	<?php if ($options->summary == 'user') { ?>
	<div class="control-group">
	<label class="control-label" for="summary">Include excerpt</label>
	<div class="controls">
	<input type="checkbox" name="summary" value="1" id="summary" style="margin-top: 7px;" />
	</div>
	</div>
	<?php } ?>

	<div class="control-group" style="margin-top: -15px;">
	<label class="control-label" for="json">JSON output</label>
	<div class="controls">
	<input type="checkbox" name="format" value="json" id="json" style="margin-top: 7px;" />
	</div>
	</div>
	
	<div class="control-group" style="margin-top: -15px;">
	<label class="control-label" for="debug">Debug</label>
	<div class="controls">
	<input type="checkbox" name="debug" value="1" id="debug" style="margin-top: 7px;" />
	</div>
	</div>	
	
	</fieldset>
	<div class="form-actions">
		<input type="submit" id="sudbmit" name="submit" value="Create Feed" class="btn btn-primary" />
	</div>
	</form>
	  
	  
	  
	  
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
		
		
		<div id="wp">
<div id="ua" ss_c="xin81"></div>
<h1>
<img src="/soso/images/logo_index_soso.png?v=2" width="280" height="49" alt="召唤师-全文RSS生成" title="召唤">
</h1>
<div id="sc" class="control-group" >
	<div id="s" class="controls" >
	<form id="url" name="url" class="controls" >
		<input type="hidden" value="utf8" name="ie" />
		<input type="hidden" value="s.idx" id="pid" name="pid" />
		<input type="text" placeholder="输入被屏蔽的feed或网址.." value="" id="query" name="query" class="form-actions"  />
        <input type="submit" id="sudbmit" name="submit" value="Creat Feed" class="btn btn-primary"  />
	</form>
	</div>
</div>
</div>
<div id="ft">
	<p style="font-size: 13px; color: #08c; text-decoration: none; text-align: center; margin: 0 0 9px; line-height: 24px;
text-align: center;padding: 0;
margin: 0;" >
    代码 <a href="http://fivefilters.org">fivefilters</a>
    丨托管 <a href="https://dashboard.heroku.com/">heroku</a>
    丨CDN <a href="https://dash.cloudflare.com/">cloudflare</a>
	</p>
</div>



	</div>
  </body>
</html>
