# WowFramework
A lightweight extendable and powerful PHP MVC framework. Includes only general needs and optimized for speed.

##About Routing Mechanism
The routing mechanism finds the Classname, Class Method and Method parameters from url.
A url like /sales/latest-sales extends to
* Controller : SalesController
* Action: LatestSalesAction
* View Name: sales/latest-sales

The view folders and view file names are always lowercase. Because linux platforms are case sensitive in filesystem but windows not. Lowercasing all the files help us to fix case sensivity problems.
Also all the controller file names are capitelized. So a controller class file with a name SalesManagerController must be SalesManagerController.php

Another Sample:
A url like /sales-manager/get-sale/11 extends to
* Controller : SalesManagerController
* Action: GetSaleAction(11)
* View Name: sales-manager/get-sale

Another Sample:
A url like /salesManager/getSale/11 extends to
* Controller : SalesmanagerController
* Action: GetsaleAction(11)
* View Name: salesmanager/getsale

This is the default routing schema 
```
(@controller(@action(@id)))
```

If you want a route to answer only defined methods the pattern must be
```
GET|POST (@controller(@action(@id)))
```
The pharanteses "()" means that this parameter can pass null. If some parameters null then the default parameter used.
A sample for this:
Our routes are these
```
        "routes"   => array(
            "DefaultRoute" => array(
                "(/@controller(/@action(/@id)))",
                array(
                    "controller" => "Home",
                    "action"     => "Index"
                )
            )
        )
```
So the url /About extends to
* Controller : AboutController
* Action: IndexAction()
* View Name: about/index

Routes are defined in /app/Config/Routes.php file!

##System Config File Samples
These samples show you how to make .htaccess file (for linux based platforms) or web.config files (for windows IIS platform) 
### Sample .htaccess file for Apache

    RewriteEngine On
    # Redirect Trailing Slashes...
    RewriteRule ^(.*)/$ /$1 [L,R=301]

    # Handle Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
    
### Sample Web.config File for IIS
    <rewrite>
      <rules>
        <rule name="FixTrailingSlashes" stopProcessing="true">
          <match url="^(.*)/$" ignoreCase="false" />
          <conditions>
            <add input="{REQUEST_FILENAME}" matchType="IsDirectory" ignoreCase="false" negate="true" />
          </conditions>
          <action type="Redirect" redirectType="Permanent" url="/{R:1}" />
        </rule>
        <rule name="FrontController" stopProcessing="true">
          <match url="^" ignoreCase="false" />
          <conditions>
            <add input="{REQUEST_FILENAME}" matchType="IsDirectory" ignoreCase="false" negate="true" />
            <add input="{REQUEST_FILENAME}" matchType="IsFile" ignoreCase="false" negate="true" />
          </conditions>
          <action type="Rewrite" url="index.php" />
        </rule>
      </rules>
    </rewrite>
