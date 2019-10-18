<?php
	$targetFolder = $_SERVER['DOCUMENT_ROOT'].'/server/storage/app/public';
	$linkFolder = $_SERVER['DOCUMENT_ROOT'].'/server/public/storage';
	echo $targetFolder.'<br>'.$linkFolder.'<br>';
	symlink($targetFolder,$linkFolder);
	echo 'Symlink process successfully completed';
?>