module import_bank
How does it work:
	With this module you can import .csv bank statement this file is then checked for any matching bank_accounts if found it will look for outstanding bills. 
	When there are no bills to post the payment to it will book a deposit for that (vendor/ costumer).
	If there is no matching bank_account it will look for previous bookings with the same description and book it the same way(only two lines in the general ledger). 
	If it can not match anything it will post the booking to a predefined(set in the module set-up) general ledger.
	
	Fields that are marked required in the xml file need to be in your csv file.
	If there is only one colum with a amount make sure the xml-fields ( amount and debit_credit) are present.
	If there are two colums(debit, credit) with a amount make sure the xml-fields (debit_amount and credit_amount) are present.
	
	It would support two types of amount import.
	the first would be:
		a field that describes if a amount is added or subtracted. (debit_credit)
		together with a field that displays the amount.(amount)
	
	the second would be:
		two fields with a amount one for amounts that are added (debit)and one for amounts that are subtracted(credit).
		(for every line one should be empty)


It requires:
- Phreebooks v3 or higer
- a general ledger with your bank account number in it.
- you need to modify the .xml in the root of the module (read the install pocedure)
- and bank account numbers in your table contacts (the field will be created on install)
butt you need to modify the contacts module to include a extra tab containing the bank_account.( you can use my contacts-add-on )


Installation Procedure:
1. unzip and install in the module directory.
2. Go to Admin -> Modules and install the module.
3. Edit the Admin -> Modules defaults.
4. You may have to set permission and re-log in.
5. Make sure the contacts have a extra field 'bank_account' (should be created when you install this module) 

You may need to costumise the import_bank.xml to match the column naming of you csv file
In the xml you find the following example. 
<Field>
	  <Name>date</Name>
	  <TagName>this needs to be modified to correspond with column description</TagName>
	  <Type>datetime</Type>
	  <Description>Specifies the date the transaction was made</Description>
	  <Properties>NOT NULL default '0000-00-00 00:00:00'</Properties>
	  <CanImport>1</CanImport>
	  <Required>1</Required>
	</Field>



// new in version 1.	
Till now it was only posible to have one bank_account field per contact. 
Now you can have a multiple of both iban and bank_account fields. 
all you have to do is to create the extra fields in (module administration >contact module> custom fields.) 
all that is required is that part of the field name can be identified as 'bank_account' or 'iban'
so you could create a field 'bank_account_1' and 'iban_1'.

Note: If a iban is supplied and there are iban fields present in the contacts table. 
then a search will be done on the iban field and not the bank account number. 


 