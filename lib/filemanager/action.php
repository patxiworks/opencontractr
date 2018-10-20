<?php

/**
 * This code is part of the FileManager software (www.gerd-tentler.de/tools/filemanager), copyright by
 * Gerd Tentler. Obtain permission before selling this code or hosting it on a commercial website or
 * redistributing it over the Internet or in any other medium. In all cases copyright must remain intact.
 */

include_once('class/FileManager.php');

$container = isset($_REQUEST['fmContainer']) ? $_REQUEST['fmContainer'] : '';

if($container != '' && isset($_SESSION[$container])) {
	$FileManager = unserialize($_SESSION[$container]);
	$fmMode = isset($_REQUEST['fmMode']) ? $_REQUEST['fmMode'] : '';
	$fmName = isset($_REQUEST['fmName']) ? $_REQUEST['fmName'] : '';
	$fmRememberPwd = isset($_REQUEST['fmRememberPwd']) ? $_REQUEST['fmRememberPwd'] : '';

	if($fmMode == 'login' && $fmName != '' && $fmRememberPwd) {
		@setcookie($FileManager->container . 'LoginPwd', $fmName, time() + 90 * 24 * 3600);
	}

	if(!in_array($fmMode, $FileManager->binaryModes)) {
		if($FileManager->locale) @setlocale(LC_ALL, $FileManager->locale);
		header("Content-Type: text/html; charset=UTF-8");
		header('Cache-Control: private, no-cache, must-revalidate');
		header('Expires: 0');
		header('X-Robots-Tag: noindex, nofollow');
	}
	if($fmMode=='getMeta') {
		$fileinfo = array();
		$rooturl = 'http://localhost:8888/';
		$rootDir = $FileManager->getListing()->FileManager->rootDir;
		$ids = isset($_REQUEST['fmObject']) ? $_REQUEST['fmObject'] : '';
		$ids = explode(',', $ids); $i=0;
		if(is_array($ids)) foreach($ids as $id) {
			if ($Entry = $FileManager->getListing()->getEntry($id)) {
				if(!$Entry->isDir()) {
					$fileinfo[$i]['id'] = $Entry->hash;
					$fileinfo[$i]['title'] = pathinfo($Entry->path)['filename'];
					$fileinfo[$i]['format'] = mime_content_type($Entry->path);
					$fileinfo[$i]['created'] = filectime($Entry->path);
					$fileinfo[$i]['modified'] = date( 'Y-m-dTh:m:s', filemtime($Entry->path) );//'2017-12-31T12:12:12';//
					$fileinfo[$i]['url'] = $rooturl . explode($rootDir .'/', $Entry->path)[1];
					$fileInfo[$i]['owner'] = $Entry->owner;
					$fileInfo[$i]['group'] = $Entry->group;
					$fileInfo[$i]['size'] = $Entry->size;
					$fileInfo[$i]['language'] = 'en';
					$i++;
				}
			}
		}
		print_r(json_encode($fileinfo));
		//print_r($FileManager->getListing()->getEntry(0));
	} else {
	ob_start();
	$FileManager->action();
	ob_end_flush();
	}
}
else {
	header('X-Robots-Tag: noindex, nofollow');
	$msg = 'Cannot restore FileManager object from PHP session - ';
	if($container == '') $msg .= 'fmContainer not set!';
	else if(!session_id()) $msg .= 'could not create session!';
	else $msg .= "\$_SESSION['$container'] not found!";
	FileManager::error($msg);
}

?>