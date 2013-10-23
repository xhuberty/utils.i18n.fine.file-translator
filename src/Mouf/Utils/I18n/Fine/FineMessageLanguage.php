<?php
/*
 * Copyright (c) 2012 David Negrier
 * 
 * See the file LICENSE.txt for copying permission.
 */
namespace Mouf\Utils\I18n\Fine;

use Mouf\MoufException;
/**
 * The FineMessageLanguage class represents a PHP resource file that can be loaded / saved / modified.
 * There are many files for on language. Files are write with the start information of the key. Function used the separator ., - or _. 
 */
class FineMessageLanguage {

	/**
	 * The path to the folder to be loaded
	 * @var string
	 */
	private $folder;

	/**
	 * The array of messages in the folder loaded.
	 * @var array<string, string>
	 */
	private $msg = array();

	/**
	 * Language load
	 * @var string
	 */
	private $language = null;
	
	/**
	 * Loads all message for a language
	 * @var $folder The path to the folder to be loaded
	 * @var $language Language of messages loaded
	 */
	public function loadForLanguage($folder, $language) {
		$this->folder = $folder;
		$this->language = $language;

		$this->msg = @include($folder."messages_".$language.".php");
	}

	/**
	 * Saves the file for current language
	 */
	public function save() {
		ksort($this->msg);

		$file = $this->folder."messages_".$this->language.".php";

		$old = umask(00002);
		$fp = fopen($file, "w");
		fwrite($fp, "<?php\n");
		fwrite($fp, 'return array(');
		$first = false;
		foreach ($this->msg as $key => $value) {
			if($first) {
				$first = false;
			}
			else {
				fwrite($fp, ",\n");
			}
			fwrite($fp, var_export($key, true).' => '.var_export($message, true));
		}
		fwrite($fp, ");\n");
		fwrite($fp, "?>\n");
		
		fclose($fp);
		umask($old);
	}

	/**
	 * Delete file of language (Delete language too)
	 * @param $language string Language to delete
	 */
	private function deleteFile($language) {
		unlink($this->folder."messages_".$language.".php");
	}
	
	/**
	 * Check if the folder and file are writable. And create language file.
	 * @param $file string File with all path
	 * @throws MoufException
	 */
	private function createFile($file) {
		if (!is_writable($file)) {
			if (!file_exists($file)) {
				// Does the directory exist?
				$dir = dirname($file);
				if (!file_exists($dir)) {
					$old = umask(0);
					$result = mkdir($dir, 0775, true);
					umask($old);
					
					if ($result == false) {
						throw new MoufException("Unable to create directory ".$dir);
					}
				}
			} else {
				throw new MoufException("Unable to write file ".$file);
			}
		} else {
			// Empties the file
			$fp = fopen($file, "w");
			fclose($fp);
		}
	}
	
	/**
	 * Set message for a key
	 * @param $key string Message key
	 * @param $message string Message
	 */
	public function setMessage($key, $message) {
		$this->msg[$key] = $message;
	}

	/**
	 * Sets messages
	 * 
	 * @param $translations array<string, string> To set many messages in one time. The array is key message.
	 */
	public function setMessages($translations) {
		
		foreach ($translations as $key => $message) {
			if($message)
				$this->msg[$key] = $message;
		}
	}
	
	/**
	 * Returns a message for the key $key.
	 * @var $key string Key of translation search.
	 */
	public function getMessage($key) {
		if (isset($this->msg[$key])) {
			return $this->msg[$key];
		} else {
			return null;
		}
	}

	/**
	 * Sets the message
	 * @var $key Remove a message for key
	 */
	public function deleteMessage($key) {
		if(isset($this->msg[$key]))
			unset($this->msg[$key]);
	}
	
	/**
	 * Returns all messages for this file.
	 * @return array<string, string> Return the array of message (key, value)
	 */
	public function getAllMessages() {
		return $this->msg;
	}
}

?>
