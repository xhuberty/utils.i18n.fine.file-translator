<?php
namespace Mouf\Utils\I18n\Fine\Translator;

use Mouf\Installer\PackageInstallerInterface;
use Mouf\MoufManager;

/**
 * A logger class that writes messages into the php error_log.
 */
class FileTranslatorInstaller implements PackageInstallerInterface
{

    /**
     * (non-PHPdoc)
     * @see \Mouf\Installer\PackageInstallerInterface::install()
     */
    public static function install(MoufManager $moufManager)
    {
		//FineFileTranslatorService
		if (!$moufManager->instanceExists("fileTranslatorService")) {
			$cascadingLanguageDetection = null;
			if($moufManager->instanceExists('cascadingLanguageDetection')) {
				$cascadingLanguageDetection = $moufManager->getInstanceDescriptor("cascadingLanguageDetection");
			} 
		
			$fileTranslator = $moufManager->createInstance("Mouf\\Utils\\I18n\\Fine\\Translator\\FileTranslator");
			$fileTranslator->setName("fileTranslatorService");
			$fileTranslator->getProperty("i18nMessagePath")->setValue("resources/");
		
			if($cascadingLanguageDetection) {
				$fileTranslator->getProperty("languageDetection")->setValue($cascadingLanguageDetection);
			}
			
			if($moufManager->instanceExists('defaultTranslationService')) {
				$defaultTranslationService = $moufManager->getInstanceDescriptor("defaultTranslationService");
				$translators = $defaultTranslationService->getProperty('translators')->getValue();
				$translators[] = $fileTranslator;
				$defaultTranslationService->getProperty('translators')->setValue($translators);
			}
		}
		
		// Let's rewrite the MoufComponents.php file to save the component
		$moufManager->rewriteMouf();
		    	
    }
}
