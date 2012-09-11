KunstmaanAdminNodeBundle by Kunstmaan
=================================

About
-----
The KunstmaanAdminNodeBundle for Symfony 2 is part of the bundles we use to build custom and flexible applications at Kunstmaan.
You have to install this bundle in order to work with nodes and pagetypes.

View screenshots and more on our [github page](http://kunstmaan.github.com/KunstmaanAdminNodeBundle).

[![Build Status](https://secure.travis-ci.org/Kunstmaan/KunstmaanAdminNodeBundle.png?branch=master)](http://travis-ci.org/Kunstmaan/KunstmaanAdminNodeBundle)


Installation requirements
-------------------------
You should be able to get Symfony 2 up and running before you can install the KunstmaanAdminNodeBundle.

Installation instructions
-------------------------
Installation is straightforward, add the following lines to your deps file:

```
[KunstmaanAdminNodeBundle]
    git=https://github.com/Kunstmaan/KunstmaanAdminNodeBundle.git
    target=/bundles/Kunstmaan/AdminNodeBundle
```

Register the Kunstmaan namespace in your autoload.php file:

```
'Kunstmaan'        => __DIR__.'/../vendor/bundles'
```

Add the KunstmaanAdminNodeBundle to your AppKernel.php file:

```
new Kunstmaan\AdminNodeBundle\KunstmaanAdminNodeBundle(),
```

Contact
-------
Kunstmaan (support@kunstmaan.be)

Download
--------
You can also clone the project with Git by running:

```
$ git clone git://github.com/Kunstmaan/KunstmaanAdminNodeBundle
```
