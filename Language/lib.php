<?php
function save_lang_files($file, $text) {
	if(file_put_contents($file . ".new", $text)) {
		// swap old and new
		chmod($file . ".new", 0666);
		if(is_file($file . ".old")) {
			unlink($file . '.old');
		}
		if(is_file($file)) {
			rename($file, $file . '.old');
		}
		rename($file . '.new', $file);
		return true;
	}
	return false;
}
?>
