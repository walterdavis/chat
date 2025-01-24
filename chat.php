<?php
require_once('config.inc.php');
require_once('markdown.php');
require_once('smartypants.php');
function m($strString){
	return SmartyPants(Markdown($strString));
}
$script = '';
$who = (isset($_POST['user']) && !empty($_POST['user'])) ? clean($_POST['user']) : false;
if($who && isset($_POST['message']) && !empty($_POST['message'])){
	$line = $_POST['message'];
	$m = ActiveRecord::Create('messages',array('author'=>$who,'message'=>$line));
	$m->save();
	$script = '
	$(\'posts\').scrollTop = $(\'posts\').scrollHeight;
	$(\'last\').value = "' . $m->added_at . '"
	$(\'latest\').value = ' . $m->id;
}
if(isAjax()){
	$id = $start = isset($_POST['latest']) ? clean($_POST['latest']) + 0 : 0;
	$last = (isset($_POST['last'])) ? $_POST['last'] : ActiveRecord::DbDateTime();
/*
	if(!empty($who) && ActiveRecord::FindFirst('messages','author = "' . $who . '" AND DATE_ADD(NOW(),-15 MINUTES) >= added_at','added_at DESC')){
		//they've been there
	}else{
		$m = ActiveRecord::Create('messages',array('author'=>'-','message'=>$who . ' appears to have left'));
		$m->save();
	}
*/
	header('Content-type: text/html; charset=utf-8');
	$thread = ActiveRecord::FindAll('messages','id > ' . $id);
	$thread_author = '';
	foreach($thread as $t) {
		$author = trim(m($t->author));
		$class = '';
		if($t->author == $who) $class = ' class="me"';
		if($thread_author == $author){
			$author = '<!-- -->';
		}elseif($author == '<p>-</p>'){
			$thread_author = $author;
			$class = ' class="bot"';
		}else{
			$thread_author = $author;
		}
		print('<tr' . $class . '><td class="author">' . $author . '</td><td>' . m($t->message) . "</td></tr>\n");
		$id = $t->id;
	}
	if(strlen($script) > 0 || $id > $start) print '<script type="text/javascript" charset="utf-8">' . $script . '
	$(\'latest\').value = ' . $id . ';
</script>';
}
print ' ';
?>
