Amazon Module 

Installation Procedure:
1. Unzip and install in the module directory (/path_to_phreebooks/modules/).
2. Go to Admin -> Modules and install the module.
3. You may have to set permission and re-log in.
4. Edit the defaults.php file and insert your specific gl accounts, price sheet name and other information specific to your business.
5. rename/merge the two custom extra_* files in each folder (inventory and phreebooks). Remove the trailing _amazon from the filename.

!!! IMPORTANT !!!
[ /modules/amazon/classes/amazon.php/function dumpAmazon() ] needs to be corrected to match the Amazon template being used. The code
is set for Consumer Electronics and several fields need to be added to the inventory table to build the template properly. First choose
the template you will be matching against and then map/create the fields to fill the template.

NOTE: This module will be automatically installed if loaded 
when Phreebooks is installed. If the module is added later, install
the module, the installer will not overwrite the database tables
if they are already there (from a prior release).
