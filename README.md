PEXAdmin
===========

PEXAdmin allow you to edit your PermissionsEX data from a fancy web-interface.

Features
--------------
* Don't get bored anymore with pex permissions editing commands!
* Global overview of all permissions + preview chat prefix / suffix set
* Edit player and groups options (default, build, ...)
* Easily list all registered groups / players set in PEX
* Add / Copy / Remove groups
* Add / Copy / Remove players
* Protected access (letting you can configure write/readonly access)

Live demonstration
--------------
http://pexadmin.oxygen-framework.net
Login: demo
Password: demo

NOTE: You only have a readonly access.

Server Requirements:
--------------
- PHP >= 5.3
- PDO Drivers (MySQL)
- Apache mod_rewrite

- Oxygen Framework version >= 0.1.7 (Included)
(latest version available at https://git.dbn.re/David/oxygen-framework)

- PEX backend must be set to sql

Installation / Configuration
--------------
###Webserver configuration
PEXAdmin must be on it's own subdomain (e.g. pexadmin.example.com)

PEXAdmin comes in 2 flavors:

- If you have access to apache server's configuration, you must setup your virtualhost to let the `/` location being mapped into the `public` folder (e.g. if all config are ok, you won't be able to access `Application` folder or any of it's contents from your browser).
Here is a Virtualhost directive to use for example:

```
<VirtualHost *:80>
    DocumentRoot /path/where/you/put/pexadmin/public
    ServerName pexadmin.example.com
</VirtualHost>
```

- If you cannot modify the server configuration and only access to the folder served as root, you can download the 'no parent folder' version of PEXAdmin

###Database configuration

As the PEXAdmin config files are all in JSON format, watch out the syntax errors!
You can use tools like JSONLint to check the syntax before editing the actual file.

You have to set your database access to let PEXAdmin handle the permissions.
Open the file `Application/Config/db.json`, change the host/dbname and credentials according to your database' ones.

```
{
    "default": {
        "dsn": "mysql:host=localhost;dbname=minecraft;charset=utf8",
        "user": "test",
        "password": "password"
    }
}
```
In this sample file, the database is hosted at `localhost` and named `minecraft`, use the same as your PEX's config.yml.

If you set tables aliases in PermissionsEX config.yml, you have to tell the new names to PEXAdmin by editing `Application/Config/pex.json`.
In the `table_aliases` section you'll find a similar syntax to the config.yml to let you tell the aliases (at left (key) you have the original table name and at right (value) you can change the name according to your PermissionEX aliases configuration).

_Do not edit the rest of the file unless you know what you are doing!_

###Users access configuration

To allow wich users can connect to PEXAdmin, you have to edit the `Application/Config/users.json`.

```
{
	"demo":
	{
		"password": "89e495e7941cf9e40e6980d14a16bf023ccd4c91",
		"role": "read"
	},
	"admin":
	{
		"password": "d033e22ae348aeb5660fc2140aec35850c4da997",
		"role": "write"
	}
}
```

Each object is a user, the key is the user's login and data is user's password and role.

In the sample config shown above, there are 2 users (demo and admin) and their passwords are respectively 'demo' and 'admin' (hashed with SHA1 algorithm).

Roles:
* `read` can see all permission / players / groups but cannot edit them
    * it can be used for people that need to know permissions nodes
* `write` can edit all permission / players / groups

To get the SHA1 hash from your password, you can use one of the the online sha1 script, or simply run `echo sha1('YOUR PASSWORD');` in PHP.



