Fine installation guide
=========================

Install Fine
--------------

By default the installation create a FileTranlator instance named fileTranslatorService. You can add or change this instance with the Mouf interface.

Configuration
-------------
By default the fileTranslatorService creates has default value.
For this example we will add a new instance:
CLick on the menu Instances -> Create a new instance. Add a name for your instance and select FileTranslator
IMAGE x2

After you must configure the variable:
languageDetection, this is the function hwo return the language value to retrieve the great translation, please add it (en) click on it
IMAGE

Drag and drop your languageDetection
IMAGE

The second parameters is the folder, where the files will be save. If you start with / or c:/ the path is the real path on your disk, otherwise if this is a letter, the root path is the application.
CAUTION: You must to check if your web server has the right to write files in this folder.
IMAGE

After the configuration, there are 2 possibilities to use it:

###Common package

With the package i18n.common in Mouf. This package integrates all the function necessary to use easily the translator. It addes method eMsg and iMsg usable in your own code.
Please see it : URL !

###Binding

Bind your fileTranslator in your controller (with Mouf interface) to call the function getTranslation.
In your controller, add an private attribut of LanguageTranslationInterface. This interface forces to have a function getTranslation.
After this you can use it :
(CODE EXAMPLE)

It's possible to add the language detection in parameter, if you want to force another language, but by default the instance use is set in translator.
