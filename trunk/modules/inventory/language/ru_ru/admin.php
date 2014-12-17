<?php
// +-----------------------------------------------------------------+
// Phreedom Language Translation File
// Generated: 2014-11-23 10:04:29
// Module/Method: inventory
// ISO Language: ru_ru
// Version: 3.7
// +-----------------------------------------------------------------+
// Path: //modules/inventory/language/ru_ru/admin.php

define('MODULE_INVENTORY_TITLE','Модуль Склад');
define('MODULE_INVENTORY_DESCRIPTION','Складской модуль содержит все функциональные возможности для хранения и управления товарами и услугами, используемых в вашей компании. Это включает в себя изделия для внутреннего и внешнего использования, а также продуктов, которые вы продаете. <b> ПРИМЕЧАНИЕ: Это основной модуль и не может быть удалены </ B>!');
define('BOX_INV_ADMIN','Управление модулем');
define('INV_TABS_HEADING_TITLE','Пользовательские Табы');
define('INV_FIELDS_HEADING_TITLE','Пользовательские поля');
define('INV_HEADING_FIELD_PROPERTIES','Тип поля и свойства (выберите один)');
define('TEXT_DEFAULT_GL_ACCOUNTS','Счет главной книги по умолчанию');
define('TEXT_INVENTORY_TYPES','Тип единицы');
define('TEXT_SALES_ACCOUNT','Счет главной книги(продажи)');
define('TEXT_INVENTORY_ACCOUNT','Счет главной книги(склад)');
define('TEXT_COGS_ACCOUNT','Cost of Sales Account');
define('TEXT_COST_METHOD','Стоимость');
define('TEXT_STOCK_ITEMS','Склад');
define('TEXT_MS_ITEMS','Главный склад');
define('TEXT_ASSY_ITEMS','Сборка');
define('TEXT_SERIAL_ITEMS','Сериализация');
define('TEXT_NS_ITEMS','Нескладская ед.');
define('TEXT_SRV_ITEMS','Сервис');
define('TEXT_LABOR_ITEMS','Оплата труда');
define('TEXT_ACT_ITEMS','Активность');
define('TEXT_CHARGE_ITEMS','Расходы');
define('MODULE_INVENTORY_NOTES_1','PRIORITY MEDIUM: Set default general ledger accounts for inventory types, after loading GL accounts (Company -> Inventory Module Properties -> Inventory tab)');
define('CD_05_50_DESC','Determines the default sales tax rate to use when adding inventory items.<br /><br />NOTE: This value is applied to inventory Auto-Add but can be changed in the Inventory => Maintain screen. The tax rates are selected from the table tax_rates and must be setup through Setup => Sales tax Rates.');
define('CD_05_52_DESC','Determines the default purchase tax rate to use when adding inventory items.<br /><br />NOTE: This value is applied to inventory Auto-Add but can be changed in the Inventory => Maintain screen. The tax rates are selected from the table tax_rates and must be setup through Setup => Purchase tax Rates.');
define('CD_05_55_DESC','Позволяет автоматическое создание товарно-материальных ценностей в окне заказов. <br /> <br /> Наименований не требуется. Эта функция позволяет автоматическое создание наименований в таблице базы данных исклада .');
define('CD_05_60_DESC','Allows an ajax call to fill in possible choices as data is entered into the SKU field. his feature is helpful when the SKUs are known and expedites filling in order forms. May slow down SKU entries when bar code scanners are used.');
define('CD_05_65_DESC','When enabled, PhreeBooks looks for a SKU length in the order form equal to the Bar Code Length value and when the length is reached, attempts to match with an inventory item. This allow fast entry of items when using bar code readers.');
define('CD_05_70_DESC','Sets the number of characters to expect when reading inventory bar code values. PhreeBooks only searches when the number of characters has been reached. Typical values are 12 and 13 characters.');
define('CD_05_75_DESC','When enabled, PhreeBooks will update the item cost in the inventory table with either the PO price or Purchase/Receive price. Usefule for on the fly PO/Purchases and updating prices from the order screen without having to update the inventory tables first.');
define('INV_HEADING_CATEGORY_NAME','Название категории');
define('INV_INFO_CATEGORY_DESCRIPTION','Описание категории');
define('INV_INFO_CATEGORY_NAME','Inventory Category name<br />Name should be short (10) with no special characters or spaces.');
define('INV_INFO_INSERT_INTRO','Введите новую категорию и ее свойства');
define('INV_EDIT_INTRO','Пожалуйста, внесите необходимые изменения');
define('INV_INFO_HEADING_NEW_CATEGORY','Новая категория');
define('INV_INFO_HEADING_EDIT_CATEGORY','Редактировать категорию');
define('INV_INFO_DELETE_INTRO','точно хотите удалить категорию?\\nКатегории не могут быть удалены, если есть запись поля в категории.');
define('INV_INFO_DELETE_ERROR','Категория существует, выбирите другое имя.');
define('INV_TABS_LOG','Табы товара - ');
define('INV_CATEGORY_MEMBER','Члены категории:');
define('INV_FIELD_NAME','Наименование поля: ');
define('TEXT_SGL_PREC','Single Precision');
define('TEXT_DBL_PREC','Double Precision');
define('INV_LABEL_DEFAULT_TEXT_VALUE','Значение по умолчанию: ');
define('INV_LABEL_MAX_NUM_CHARS','Maximum Number of Characters (Length)');
define('INV_LABEL_FIXED_255_CHARS','255 знаков максимум.');
define('INV_LABEL_MAX_255','(for lengths less than 256 Characters)');
define('INV_LABEL_CHOICES','Enter Selection String');
define('INV_LABEL_TEXT_FIELD','Текстовое поле');
define('INV_LABEL_HTML_TEXT_FIELD','HTML Код');
define('INV_LABEL_HYPERLINK','Ссылка');
define('INV_LABEL_IMAGE_LINK','Файл изображения');
define('INV_LABEL_INVENTORY_LINK','Inventory Link (Link pointing to another inventory item (URL))');
define('INV_LABEL_INTEGER_FIELD','Целое число');
define('INV_LABEL_INTEGER_RANGE','Диапазон целых чисел');
define('INV_LABEL_DECIMAL_FIELD','Десятичное число');
define('INV_LABEL_DECIMAL_RANGE','Диапазон десятичных');
define('INV_LABEL_DEFAULT_DISPLAY_VALUE','Формат отображения (Max,Decimal)');
define('INV_LABEL_DROP_DOWN_FIELD','Выпадающий список');
define('INV_LABEL_RADIO_FIELD','Radio Button Selection<br />Enter choices, separated by commas as:<br />value1:desc1:def1,value2:desc2:def2<br /><u>Key:</u><br />value = The value to place into the database<br />desc = Textual description of the choice<br />def = Default 0 or 1, 1 being the default choice<br />Note: Only 1 default is allowed per list');
define('INV_LABEL_DATE_TIME_FIELD','Дата и время');
define('INV_LABEL_CHECK_BOX_FIELD','Чек бокс (Да или Нет)');
define('INV_LABEL_TIME_STAMP_FIELD','Временной штамп');
define('INV_LABEL_TIME_STAMP_VALUE','System field to track the last date and time a change to a particular inventory item was made.');
define('INV_FIELD_NAME_RULES','Fieldnames cannot contain spaces or special<br />characters and must be 64 characters or less');
define('INV_CATEGORY_CANNOT_DELETE','Не могу удалить категорию. Используется полем: ');
define('INV_CANNOT_DELETE_SYSTEM','Fields in the System category cannot be deleted!');
define('INV_IMAGE_PATH_ERROR','Error in the path specified for the upload image!');
define('INV_IMAGE_FILE_TYPE_ERROR','Error in the uploaded image file. Not an acceptable file type.');
define('INV_IMAGE_FILE_WRITE_ERROR','There was a problem writing the image file to the specified directory.');
define('INV_FIELD_RESERVED_WORD','The field name entered is a reserved word. Please choose a new field name.');
define('INV_TOOLS_VALIDATE_SO_PO','Инвентаризация: проверка количества товаров в заказах');
define('INV_TOOLS_VALIDATE_SO_PO_DESC','This operation tests to make sure your inventory quantity on Purchase Order and quantity of Sales Order match with the journal entries. The calculated values from the journal entries override the value in the inventory table.');
define('INV_TOOLS_REPAIR_SO_PO','Test and Repair Inventory Quantity on Order Values');
define('INV_TOOLS_BTN_SO_PO_FIX','Начать проверку и восстановление');
define('INV_TOOLS_PO_ERROR','SKU: %s had a quantity on Purchase Order of %s and should be %s. The inventory table balance was fixed.');
define('INV_TOOLS_SO_ERROR','SKU: %s had a quantity on Sales Order of %s and should be %s. The inventory table balance was fixed.');
define('INV_TOOLS_SO_PO_RESULT','Finished processing Inventory order quantities. The total number of items processed was %s. The number of records with errors was %s.');
define('INV_TOOLS_AUTDIT_LOG_SO_PO','Inv Tools - Repair SO/PO Qty (%s)');
define('INV_TOOLS_VALIDATE_INVENTORY','Validate Inventory Displayed Stock');
define('INV_TOOLS_VALIDATE_INV_DESC','This operation tests to make sure your inventory quantities listed in the inventory database and displayed in the inventory screens are the same as the quantities in the inventory history database as calculated by PhreeBooks when inventory movements occur. The only items tested are the ones that are tracked in the cost of goods sold calculation. Repairing inventory balances will correct the quantity in stock and leave the inventory history data alone. ');
define('INV_TOOLS_REPAIR_TEST','Test Inventory Balances with COGS History');
define('INV_TOOLS_REPAIR_FIX','Repair Inventory Balances with COGS History');
define('INV_TOOLS_REPAIR_CONFIRM','Are you sure you want to repair the inventory stock on hand to match the PhreeBooks COGS history calculated values?');
define('INV_TOOLS_BTN_TEST','Проверить баланс Склада');
define('INV_TOOLS_BTN_REPAIR','Синхронизировать Кол. на складе');
define('INV_TOOLS_OUT_OF_BALANCE','SKU: %s -> stock indicates %s on hand but COGS history list %s available');
define('INV_TOOLS_IN_BALANCE','Баланс склада в норме.');
define('INV_TOOLS_STOCK_ROUNDING_ERROR','SKU: %s -> Stock indicates %s on hand but is less than your precision. Please repair your inventory balances, the stock on hand will be rounded to %s.');
define('INV_TOOLS_BALANCE_CORRECTED','АРТ: %s -> Количество на руках изменено на %s.');
define('INV_MARGIN','Маржа');
define('INV_ENTRY_FULL_PRICE_WT','Цена с НДС');

?>
