Use the translator
==================

Common package
--------------

This package is automatically bind to the cascading translator (this class is in utils.i18n.fine.common package).
With the package utils.i18n.fine.common in Mouf. This package integrates all the function necessary to use easily the translator. It addes method eMsg and iMsg usable in your own code.
Please see it: [See this package utils.i18n.fine.common](http://www.mouf-php.com/)[https://mouf-php.com/packages/mouf/utils.i18n.fine.common]

Binding
-------

Bind your fileTranslator in your controller (with Mouf interface) to call the function getTranslation.
In your controller, add an private attribut of TranslatorInterface. This interface forces to have a function getTranslation.
After this you can use it:
```
echo $this->translatorInterface->getTranslation('mykey', array('name', 'myname'));
```

It's possible to add the language detection in parameter, if you want to force another language, but by default the instance use is set in translator.
Example:
```
$languageDetection = new Mouf\Utils\I18n\Fine\Language\FixedLanguageDetection();
$languageDetection->setLanguage('fr');

echo $this->translatorInterface->getTranslation('mykey', array('name', 'myname'), languageDetection);
```
