# Introduction #

In Job Manager 0.4.0, there were signification changes made to how data is stored. As such, you should take particular care when upgrading.

# Before Upgrading #

  * **WordPress 2.9 or higher is a requirement**
> As part of JM 0.4.0, the entire data storage has been re-written to use only the WordPress default tables. As part of this, Job Manager now uses custom post types, which were introduced in WordPress 2.9.
  * **Test before upgrading production**
> As mentioned, significant changes have been made to the data storage. Similar changes have been made to the plugin settings, leaving virtually no data untouched. In order to make sure there are no problems with your WordPress/Job Manager installation, please test the upgrade before doing it in your production environment.
  * **Backup before you upgrade**
> In order to safeguard against potential data loss, you should perform a full database backup before upgrading.
  * **Jobs are now stored as pages**
> JM now stores everything as pages in your database. If you have a page that uses the same slug as JM, it is strongly recommended that you change one of them before upgrading. JM will take control of this page during the upgrade process.

# Feature Changes #
  * **Access URLs**
> Along with the data changes, you will notice several changes to the URLs used for accessing Job Manager.

> _Current Nice URLs_:
    * View a Job: `http://yoursite.com/jobs/view/1-job-name/`
> _New Nice URLs_:
    * View a Job: `http://yoursite.com/jobs/job-name/`

> All other Nice URLs will stay the same.

> Because Job Manager is changing to store everything as pages, the default URL scheme will change significantly, to simply load the page numbers (_n_ in the URL represents the page number):

> _Current Default URLs_:
    * Job List: `http://yoursite.com/?jobs=all`
    * Job List by Category: `http://yoursite.com/?jobs=category-slug`
    * View a Job: `http://yoursite.com?jobs=view&data=1-job-slug`
    * Application Form: `http://yoursite.com?jobs=apply`
    * Application Form for a Job: `http://yoursite.com?jobs=apply&data=1`
    * Application Form for a Category: `http://yoursite.com?jobs=apply&data=category-slug`
> _New Default URLs_:
    * Job List: `http://yoursite.com/?p=n`
    * Job List by Category: `http://yoursite.com/?p=n`
    * View a Job: `http://yoursite.com/?p=n`
    * Application Form: `http://yoursite.com/?p=n`
    * Application Form for a Job: `http://yoursite.com/?p=n&job=m`
    * Application Form for a Category: `http://yoursite.com/?p=n&cat=m`

# Help! Something Went Wrong! #

Okay, take a deep breath. Don't panic. First of all, download [version 0.3.3](http://wordpress.org/extend/plugins/job-manager/download/) and install it in your plugin directory. Restore the database backup you took at the start. You're now back to where your site was before the upgrade.

Next, [submit a bug report](http://code.google.com/p/wordpress-job-manager/issues/list). Please include as much information as you can. Error messages, screen shots, Apache error logs, anything you think might help.

Finally, relax. I'm planning on getting through any bug reports on 0.4.0 as quickly as possible, to ensure you have a stable, quality release to play with.