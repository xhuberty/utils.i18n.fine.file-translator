<?php
require_once __DIR__."/../../../autoload.php";
use Mouf\Actions\InstallUtils;
use Mouf\MoufManager;

// First, let's request the install utilities


// Let's init Mouf

InstallUtils::init(InstallUtils::$INIT_APP);

// Let's create the instance
$moufManager = MoufManager::getMoufManager();

//FineFileTranslatorService
if ($moufManager->instanceExists("FineFileTranslatorService")) {
	$defaultTranslationService = $moufManager->getInstanceDescriptor("FineFileTranslatorService");
} else {
	$defaultTranslationService = $moufManager->createInstance("Mouf\\Utils\\I18n\\Fine\\Translate\\FileTranslator");
	$defaultTranslationService->setName("fineFileTranslatorService");
	$defaultTranslationService->getProperty("i18nMessagePath")->setValue("resources/");
	$defaultTranslationService->getProperty("languageDetection")->setValue($defaultLanguageDetection);
}

// Let's rewrite the MoufComponents.php file to save the component
$moufManager->rewriteMouf();

// Finally, let's continue the install
InstallUtils::continueInstall();