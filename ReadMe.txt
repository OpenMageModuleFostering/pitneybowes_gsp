========================================================
INSTALLATION
========================================================
Disclaimer: We recommend to install ANY extension you purchase/download in a testing environment before deploying it to your production environment. Please also backup your Magento installation (files and database) before installing any extension and make sure no conflicting extensions are installed. All extensions are tested in clean Magento installations without third party extensions and we can't guarantee for compatibility with third party extensions.

If you're using the Magento compiler - or if you are not sure if you're doing so - please log into the Magento backend and go to System > Tools > Compilation and turn it off. If you don't do so there's a good chance you'll temporarily break your Magento installation. Also, it is recommended to disable the cache before installing any extension.

Open the extension ZIP file and extract it to a directory on your computer using a tool like WinRar, WinZIP or similar.

Upload the extracted folders and files into the root directory of your Magento installation. The root directory of Magento is the folder that contains the directories "app", "js", "skin", "lib" and more. All folders should match the existing folder structure. If one of our extensions is already installed and you're updating it, make sure to overwrite the existing files of the extension.

Go to System > Cache Management and click both the 'Flush Magento Cache' as well as the 'Flush Cache Storage' button. This is required to activate the extension.

Log out of the Magento admin and log back in - you'll see a 404 Error when trying to open the configuration section if you don't log out / log in once.

If you're using the Magento compiler and had it enabled before installing this extension, go to System > Tools > Compilation and click on 'Run Compilation Process' to re-compile Magento and enable it again by clicking on 'Enable'.

========================================================
CONFIGURATION
========================================================
Attention!! Be sure to logout & re-login before configuration else you will get '404 Error (Page not found)' in System > Configuration Page.
1> After installation go to Admin:
System >> Configuration >> Shipping Methods >> Pitney Bowes PBGSP >> Manage your setting here.
Be sure to review the full documentation found here http://wiki.ecommerce.pb.com

Your Pitney Bowes project manager will provide you with the necessary credentials needed to complete the installation.

GNUPG encryption
In order to take advantage of the file encryption setting in the extension configs your server will need to have the GNUGP module installed. You can find out more about that here: https://www.gnupg.org

========================================================
CHANGELOG
========================================================

v. 1.3.0

Added admin interface for managing categories to be exported in the catalog. Store managers can now quickly enable and disable categories to be exported to PB.

Adjusted catalog export default preferences to 100k products

Catalog file naming change when multiple files are exported per catalog. (Changed file suffix to 00001, 00002, 00003, etc)

added a check if the ASN has already been generated for a shipment, do not create a new ASN if yes.

Fixed error when checkout with paypal express. The lastname was not set and the PB API throws an error when last name isn't set.


v. 1.2.3

Fixed an issue with 3rd party checkout extensions where shipping  wouldn’t show correctly when switching back and forth between US and International locations

Small adjustment to force USD as the user's currency selected during the API call to PB


v. 1.2.2

Fixed an issue where the RH_CATEGORY_ID_PATH had a leading zero in the child category ID. This was causing duplicate categories in the PB system

Created a manual catalog export tool to be used at the command line. A new file is added at magento root directory: PBGSP_Manual_Catalog_Export.php 


v. 1.2.1

Added logic in extension preferences to change API URL values dynamically from Sandbox to Production
Small adjustment to catalog product URL to use $product->getUrlInStore() .

Add required logic to ensure that the ASN generation logic is triggered no matter the method of shipment creation (manual or programmatically).

Fixed an error when the tracking number was not provided for the order.

Added backwards compatibility to CE 1.7.x+ and Enterprise 1.8.x


v. 1.2.0

Add config option to override domestic shipping address with PB HUB address. When active the PB domestic HUB address will overwrite the customer shipping address for the order. The customer shipping address is stored with PB and also added to the order details for record keeping.

move the product weight value into the COMMODITY_WEIGHT column on the CSV catalog exports.

add logic to separate free shipping and tax. Now each work independently of one another where before enabling free shipping also made tax 0.00


v. 1.1.2

Integrated validation for title field in configuration page to resolve PHP 5.3 will warning when there is an empty string

Added a cron job to check for shipped PB orders that have no ASNs (tracking numbers) and attempt to create.


v. 1.1.1

Corrected currency conversion logic to not request rate from PB but run conversion directly in Magento


v. 1.1.0

Added logic to cancel the order in the PB system when a PB order is cancelled in Magento.

Fixed the incremental catalog/product export to only include products and/or category files when one or both are edited in magento (example: if the incremental update is set to every hour and only a single product was updated in any way during the wait period, that product will be included in the next incremental catalog update. The same is true for categories that were updated within the wait period.)

Fixed an issue when empty catalog and category export csv files were created even though there were no updates made.


v. 1.0.3

Added compatibility to run in php 5.6.x 


v. 1.0.2

Resolved issue where orders with shipments were not passing additional ASNs. Now an ASN is generated for each shipment per order


v. 1.0.1

Fixed a production issue when exportedFiles variable is null
Added City to the Pitney Bowes Hub address provided to store admin on order details
Update PB tracking URL to https://parceltracking.pb.com/app/#/dashboard/UPIDHERE
Fixed issue with international tracking url
Fixed a production issue when exportedFiles variable is null
Fixed issue with ASN request not sending correct commodity quantity.
Made the PGP extension not required for installation thru the Magento Connect process.
Added Input values in config to allow customer return address when shipment is rejected

v. 1.0.0 - 
- Initial Application build, see documentation for details


========================================================
BUGS / NEW FEATURE REQUEST
========================================================
PB-Magento-GSP-Support@pb.com
#################(RAY, we can add a support email here that will be PB specific but that will also forward to my team)
========================================================
FOR SUPPORT
========================================================
PB-Magento-GSP-Support@pb.com
