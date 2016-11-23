Translator Module 

Installation Procedure:
1. unzip and install in the module directory.
2. Go to Admin -> Modules and install the module.
3. You may have to set permissions (Tools tab) to access the module and re-log in.

NOTE: If you did an upgrade, you will need to uninstall your prior version.
If you have the zip file you sent me, use that to do the upload. I
tested this with your zip file you sent me and all seemed well.
Now to build a translation.

Usage:
The first step is to upload an older release of the language to
translate to (i.e. fr_fr for release 2.1).

1. Click on the Upload icon and enter the language to create (i.e.
fr_fr), enter All for all modules (this is necessary as the module
name and version will be extracted from the zipped file), select a
zipped language file to upload. The file will be uploaded and all 
'defines' will be extracted into the db.

2. If you have an older translation in the source language, repeat 
step 1 for that language, the translator will check for changes and
pre-select translated lines to save time. this feature is used for 
upgrades.

3. Import your current translation. For example, Language: en_us, Module:
All (we'll do all at once), leave the install directory alone for
now, it will need to be imported separately.

Now the fun part, creating new translation files. This is done 
module by module.

4. Click New Translation icon. Enter language to create (i.e.
fr_fr), select a source module and language (typically needs to be
en_us). If you have a translation loaded from another language (other
than the source), select the check box and enter best guess language
(i.e. fr_fr from step 1 above). 

The new translation is created in the largest release of the modules
created. If a similar translation is checked, it will be set. If an 
earlier translation from the same language is found, it will overwrite 
the value, if no matches are found, the source language will be set. 
If an earlier source translation has been loaded and the define constant 
has not changed, the translated box will be cecked otherwise the box
will be unchecked.

Removal
Remove the module through the Company -> Module Administration page. NOTE: Removing the module will remove any
files from the /my_files/translator directory and remove the database table.
