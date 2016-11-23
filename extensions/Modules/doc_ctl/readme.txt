Document Control Module 

PLEASE READ BEFORE THIS MODULE IS INSTALLED:
The document control module will, by default, install one top 
level folder. If additional folders are needed, make the following
adjustment BEFORE installing the module.

File: /modules/doc_ctl/defaults.php
Change: define('INSTALL_NUMBER_OF_DRIVES', 1); 

From 1 to the number of drives to install. They can be renamed once installed.

TOP LEVEL DRIVES CANNOT BE ADDED ONCE THE MODULE IS INSTALLED!

Installation Procedure:
1. unzip and install in the module directory.
2. Go to Admin -> Modules and install the module.
3. Don't forget to set permissions for access.

NOTE: This module will be automatically installed if loaded
when Phreebooks is installed. If the module is added later, install
the module, the installer will not overwrite the database tables
if they are already there (from a prior release).
