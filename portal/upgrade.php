<?php
/*
 * PhreeBooks 5 - Bizuno DB Upgrade Script - from any version to current release
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Bizuno to newer
 * versions in the future. If you wish to customize Bizuno for your
 * needs please refer to http://www.phreesoft.com for more information.
 *
 * @name       Bizuno ERP
 * @author     Dave Premo, PhreeSoft <support@phreesoft.com>
 * @copyright  2008-2017, PhreeSoft, Inc.
 * @license    PhreeSoft Proprietary, All Rights Reserved
 * @version    3.x Last Update: 2019-03-27
 * @filesource /portal/upgrade.php
 */

namespace bizuno;

ini_set("max_execution_time", 1000000000);

/**
 * Handles the db upgrade for all versions of Bizuno to the current release level
 * @param string $dbVersion - current Bizuno db version
 */
function bizunoUpgrade($dbVersion='1.0')
{
    if (version_compare($dbVersion, '2.9') < 0) {
        if (!dbFieldExists(BIZUNO_DB_PREFIX.'users', 'cache_date')) {
            dbGetResult("ALTER TABLE ".BIZUNO_DB_PREFIX."users ADD `cache_date` DATETIME DEFAULT NULL COMMENT 'tag:CacheDate;order:70' AFTER `attach`");
        }
    }
    if (version_compare($dbVersion, '3.0.7') < 0) {
        if (!dbFieldExists(BIZUNO_DB_PREFIX.'journal_main', 'notes')) {
            dbTransactionStart();
            // Increase the configuration value to support big charts and more dashboards
            dbGetResult("ALTER TABLE ".BIZUNO_DB_PREFIX."configuration CHANGE `config_value` `config_value` MEDIUMTEXT COMMENT 'type:hidden;tag:ConfigValue;order:20'");
            // Convert the date and datetime fields to remove the unsupported default = '0000-00-00' issue for newer versions of MySQL
            dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."contacts` CHANGE `first_date` `first_date` DATE DEFAULT NULL COMMENT 'type:date;tag:DateCreated;col:4;order:10'");
            dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."contacts` CHANGE `last_update` `last_update` DATE DEFAULT NULL COMMENT 'type:date;tag:DateLastEntry;col:4;order:20'");
            dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."contacts` CHANGE `last_date_1` `last_date_1` DATE DEFAULT NULL COMMENT 'type:date;tag:AltDate1;col:4;order:30'");
            dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."contacts` CHANGE `last_date_2` `last_date_2` DATE DEFAULT NULL COMMENT 'type:date;tag:AltDate2;col:4;order:40'");
            dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."journal_cogs_owed` CHANGE `post_date` `post_date` DATE DEFAULT NULL COMMENT 'type:date;tag:PostDate;order:40'");
            dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."journal_history` CHANGE `last_update` `last_update` DATE DEFAULT NULL COMMENT 'type:date;tag:LastUpdate;order:90'");
            dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."journal_item` CHANGE `post_date` `post_date` DATE DEFAULT NULL COMMENT 'type:date;tag:PostDate;order:85'");
            dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."journal_main` CHANGE `post_date` `post_date` DATE DEFAULT NULL COMMENT 'type:date;tag:PostDate;order:50'");
            dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."journal_main` CHANGE `terminal_date` `terminal_date` DATE DEFAULT NULL COMMENT 'type:date;tag:TerminalDate;order:60'");
            dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."journal_main` CHANGE `closed_date` `closed_date` DATE DEFAULT NULL COMMENT 'type:hidden;tag:ClosedDate;order:6'");
            dbTransactionCommit();
            dbTransactionStart();
            dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."journal_periods` CHANGE `start_date` `start_date` DATE DEFAULT NULL COMMENT 'type:date;tag:StartDate;order:20'");
            dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."journal_periods` CHANGE `end_date` `end_date` DATE DEFAULT NULL COMMENT 'type:date;tag:EndDate;order:30'");
            dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."journal_periods` CHANGE `date_added` `date_added` DATE DEFAULT NULL COMMENT 'type:date;tag:DateAdded;order:40'");
            dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."phreeform` CHANGE `create_date` `create_date` DATE DEFAULT NULL COMMENT 'type:date;tag:CreateDate;order:20'");
            dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."phreeform` CHANGE `last_update` `last_update` DATE DEFAULT NULL COMMENT 'type:date;tag:LastUpdate;order:30'");
            dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."tax_rates` CHANGE `start_date` `start_date` DATE DEFAULT NULL COMMENT 'type:date;tag:StartDate;order:50'");
            dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."tax_rates` CHANGE `end_date` `end_date` DATE DEFAULT NULL COMMENT 'type:date;tag:EndDate;order:60'");
            dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."contacts_log` CHANGE `log_date` `log_date` DATETIME DEFAULT NULL COMMENT 'type:date;tag:LogDate;order:10'");
            dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."inventory` CHANGE `creation_date` `creation_date` DATETIME DEFAULT NULL COMMENT 'type:date;tag:DateCreated;order:10'");
            dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."inventory` CHANGE `last_update` `last_update` DATETIME DEFAULT NULL COMMENT 'type:date;tag:DateLastUpdate;order:20'");
            dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."inventory` CHANGE `last_journal_date` `last_journal_date` DATETIME DEFAULT NULL COMMENT 'type:date;tag:DateLastJournal;order:30'");
            dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."inventory_history` CHANGE `post_date` `post_date` DATETIME DEFAULT NULL COMMENT 'type:date;tag:PostDate;order:70'");
            dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."journal_item` CHANGE `date_1` `date_1` DATETIME DEFAULT NULL COMMENT 'type:date;tag:ItemDate1;order:90'");
            dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."phreemsg` CHANGE `post_date` `post_date` DATETIME DEFAULT NULL COMMENT 'type:date;tag:PostDate;order:10'");
            dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."users` CHANGE `cache_date` `cache_date` DATETIME DEFAULT NULL COMMENT 'type:date;tag:CacheDate;order:70'");
            dbTransactionCommit();
            // now extensions
            dbTransactionStart();
            if (dbTableExists(BIZUNO_DB_PREFIX.'crmPromos')) {
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."crmPromos` CHANGE `start_date` `start_date` DATE DEFAULT NULL COMMENT 'type:date;tag:StartDate;order:20'");
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."crmPromos` CHANGE `end_date` `end_date` DATE DEFAULT NULL COMMENT 'type:date;tag:EndDate;order:30'");
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."crmPromos_history` CHANGE `send_date` `send_date` DATE DEFAULT NULL COMMENT 'type:date;tag:Date;order:20'");
            }
            if (dbTableExists(BIZUNO_DB_PREFIX.'extDocs')) {
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."extDocs` CHANGE `create_date` `create_date` DATE DEFAULT NULL COMMENT 'type:date;tag:CreateDate;order:60'");
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."extDocs` CHANGE `last_update` `last_update` DATE DEFAULT NULL COMMENT 'type:date;tag:LastUpdate;order:70'");
            }
            if (dbTableExists(BIZUNO_DB_PREFIX.'extMaint')) {
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."extMaint` CHANGE `maint_date` `maint_date` DATE DEFAULT NULL COMMENT 'type:date;tag:MaintenanceDate;order:30'");
            }
            if (dbTableExists(BIZUNO_DB_PREFIX.'extQuality')) {
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."extQuality` CHANGE `creation_date` `creation_date` DATE DEFAULT NULL COMMENT 'type:date;tag:DateCreated;order:35'");
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."extQuality` CHANGE `analyze_start_date` `analyze_start_date` DATE DEFAULT NULL COMMENT 'type:date;tag:AnalyzeStartDate;order:80'");
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."extQuality` CHANGE `analyze_end_date` `analyze_end_date` DATE DEFAULT NULL COMMENT 'type:date;tag:AnalyzeEndDate;order:90'");
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."extQuality` CHANGE `repair_start_date` `repair_start_date` DATE DEFAULT NULL COMMENT 'type:date;tag:RepairStartDate;order:100'");
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."extQuality` CHANGE `repair_end_date` `repair_end_date` DATE DEFAULT NULL COMMENT 'type:date;tag:RepairEndDate;order:110'");
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."extQuality` CHANGE `audit_start_date` `audit_start_date` DATE DEFAULT NULL COMMENT 'type:date;tag:AuditStartDate;order:120'");
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."extQuality` CHANGE `audit_end_date` `audit_end_date` DATE DEFAULT NULL COMMENT 'type:date;tag:AuditEndDate;order:130'");
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."extQuality` CHANGE `close_start_date` `close_start_date` DATE DEFAULT NULL COMMENT 'type:date;tag:CloseStartDate;order:140'");
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."extQuality` CHANGE `close_end_date` `close_end_date` DATE DEFAULT NULL COMMENT 'type:date;tag:CloseEndDate;order:150'");
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."extQuality` CHANGE `action_date` `action_date` DATE DEFAULT NULL COMMENT 'type:date;tag:ActionDate;order:160'");
            }
            if (dbTableExists(BIZUNO_DB_PREFIX.'extReturns')) {
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."extReturns` CHANGE `creation_date` `creation_date` DATE DEFAULT NULL COMMENT 'type:date;tag:DateCreated;order:105'");
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."extReturns` CHANGE `invoice_date` `invoice_date` DATE DEFAULT NULL COMMENT 'type:date;tag:DateInvoiced;order:110'");
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."extReturns` CHANGE `receive_date` `receive_date` DATE DEFAULT NULL COMMENT 'type:date;tag:DateReceived;order:115'");
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."extReturns` CHANGE `closed_date` `closed_date` DATE DEFAULT NULL COMMENT 'type:date;tag:DateClosed;order:120'");
            }
            if (dbTableExists(BIZUNO_DB_PREFIX.'srvBuilder_jobs')) {
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."srvBuilder_jobs` CHANGE `date_last` `date_last` DATE DEFAULT NULL COMMENT 'type:date;tag:DateLastUsed;order:90'");
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."srvBuilder_journal` CHANGE `create_date` `create_date` DATE DEFAULT NULL COMMENT 'type:date;tag:CreateDate;order:70'");
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."srvBuilder_journal` CHANGE `close_date` `close_date` DATE DEFAULT NULL COMMENT 'type:date;tag:ClosedDate;order:80'");
            }
            if (dbTableExists(BIZUNO_DB_PREFIX.'toolXlate')) {
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."toolXlate` CHANGE `date_create` `date_create` DATE DEFAULT NULL COMMENT 'type:date;tag:CreateDate;order:60'");
            }
            if (dbTableExists(BIZUNO_DB_PREFIX.'extFixedAssets')) {
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."extFixedAssets` CHANGE `date_acq` `date_acq` DATETIME DEFAULT NULL COMMENT 'type:date;tag:DateAcquired;order:75'");
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."extFixedAssets` CHANGE `date_maint` `date_maint` DATETIME DEFAULT NULL COMMENT 'type:date;tag:DateLastMaintained;order:80'");
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."extFixedAssets` CHANGE `date_retire` `date_retire` DATETIME DEFAULT NULL COMMENT 'type:date;tag:DateRetired;order:85'");
            }
            if (dbTableExists(BIZUNO_DB_PREFIX.'extShipping')) {
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."extShipping` CHANGE `ship_date` `ship_date` DATETIME DEFAULT NULL COMMENT 'type:date;tag:ShipDate;order:30'");
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."extShipping` CHANGE `deliver_date` `deliver_date` DATETIME DEFAULT NULL COMMENT 'type:date;tag:DueDate;order:35'");
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."extShipping` CHANGE `actual_date` `actual_date` DATETIME DEFAULT NULL COMMENT 'type:date;tag:DeliveryDate;order:40'");
            }
            dbTransactionCommit();
            dbTransactionStart();
            // Add notes field to the journal_main table
            dbGetResult("ALTER TABLE ".BIZUNO_DB_PREFIX."journal_main ADD `notes` VARCHAR(255) DEFAULT NULL COMMENT 'tag:Notes;order:90' AFTER terms");
            dbTransactionCommit();
        } // EOF - if (!dbFieldExists(BIZUNO_DB_PREFIX.'journal_main', 'notes'))
        // add new field
        if (dbTableExists(BIZUNO_DB_PREFIX.'extShipping')) {
            if (!dbFieldExists(BIZUNO_DB_PREFIX.'extShipping', 'billed')) {
                dbTransactionStart();
                dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."extShipping` ADD `billed` FLOAT DEFAULT '0' COMMENT 'type:currency;tag:Billed;order:60' AFTER `cost`");
                dbTransactionCommit();
            }
        }
    }

    if (version_compare($dbVersion, '3.1.3') < 0) {
        // add new vendor form folder to phreeform
        $id = dbGetValue(BIZUNO_DB_PREFIX.'phreeform', 'id', "group_id='vend:j6' AND mime_type='dir'");
        if (!$id) {
            $parent = dbGetValue(BIZUNO_DB_PREFIX.'phreeform', 'id', "group_id='vend' AND mime_type='dir'");
            dbWrite(BIZUNO_DB_PREFIX.'phreeform', ['parent_id'=>$parent,'title'=>'journal_main_journal_id_6','group_id'=>'vend:j6','mime_type'=>'dir','security'=>'u:-1;g:-1','create_date'=>date('Y-m-d')]);
        }
        clearModuleCache('bizuno', 'properties', 'encKey'); // Fixes possible bug in storage of encryption key
    }
    if (version_compare($dbVersion, '3.1.7') < 0) {
        dbWrite(BIZUNO_DB_PREFIX.'contacts', ['inactive'=>0], 'update', "inactive=''");
        dbGetResult("ALTER TABLE `".BIZUNO_DB_PREFIX."contacts` CHANGE `inactive` `inactive` CHAR(1) NOT NULL DEFAULT '0' COMMENT 'type:select;tag:Status;order:20'");
    }

    // At every upgrade, run the comments repair tool to fix changes to the view structure
    bizAutoLoad(BIZUNO_LIB."controller/module/bizuno/tools.php", 'bizunoTools');
    $ctl = new bizunoTools();
    $ctl->repairComments(false);
}
