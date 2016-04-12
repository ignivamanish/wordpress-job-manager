

# Introduction #

Job Manager was first written around the time of WordPress 2.8/2.9, and makes extensive use of features that weren't yet fully formed. As one of my earliest plugins, it also contains much gode that is written using sub-optimal methods, or just plain ugly hacks.

This plan involves the following:
  * Re-create current functionality, though there may be parts that are deprecated.
  * Roll in some new features that would particularly benefit this release.

As this is a fairly large milestone, I've decided to bump the version number to 1.0.

All code shall follow the [WordPress Coding Standard](http://codex.wordpress.org/WordPress_Coding_Standards).

# Current Functionality #

This is an in-depth look at the current functionality of Job Manager, including what needs to be kept, improved or discarded.

## Settings ##

The good news is that WordPress now provides functions for creating meta boxes, instead of having to generate the HTML ourselves.
http://codex.wordpress.org/Function_Reference/do_meta_boxes

### Admin Settings ###

Admin Settings will probably need significant work in order to work with the planned multi-user functionality.

**Categories** may need to be moved to their own page. Hierarchical Categories ([Issue #112](https://code.google.com/p/wordpress-job-manager/issues/detail?id=#112)) make this a necessity.

I'm not sure what to do with **Icons**, yet. At the moment, they're stored as Media, which means they show up in the media list. This is obviously bad. We may be able to handle the uploads ourselves, depending on how nicely that plays with WordPress.com.

**User Settings** will change with multi-user, probably the same with **Application Email Settings**.

**Interview Settings** will probably remain unchanged.

Currently undecided about **Other Plugins**. I'm inclined to just silently support _Google XML Sitemaps_ (if it still needs support effort once we fix up our Custom Post Type support), and drop _SI Captcha_, because it's horrible.

**Uninstall Settings** may be expanded, but there's no immediate need.

### Display Settings ###

The **Display Settings** box can stay, except for _Date Format_, which should really use the WordPress date format.

**Job List Sorting** is apparently buggy, so this will need investigating.

I'm quite proud of **Job Templates** and **Application Form Template**, though there's no denying they're quite complex, and intimidating to the new user. I'm not sure how to fix this without making them less powerful.

**Miscellaneous Text** feels kind of kludgy, but I'm not sure how to replace it.

**Page Text** is a hack that needs to go. Perhaps in the upgrade process, wo should offer to migrate the text to the appropriate template.

### App. Form Settings ###

Similar to the Templates pages from **Display Settings**, this is really powerful, but really confusing.

The _Show this field in the Application List?_ option needs to go away, it can be replaced with in the _Screen Options_ tab on the actual application list page, using the [Custom Column](http://yoast.com/custom-post-type-snippets/) functionality.

Contextual help would be a really good idea, especially for explaining how filters work.

### Job Form Settings ###

Same kind of thing as **App. Form Settings** - remove _Show this field in the Admin Job List?_, add contextual help.

## Add Job / Jobs ##

The listing and editing code will need to be completely re-written to use the custom post type API.

## Applications ##

Again, this will need to be rewritten using the custom post type API.

For the filtering functionality, it looks like it can be implemented in a similar manner to these snippets:
http://yoast.com/custom-post-type-snippets/

## Emails ##

I don't remember what this does.

## Interviews ##

This can stand alone fairly well, but it could probably do with a design refresh.

# New Functionality #

## Pluggable Field Types ##

I'm cool with not exposing this anywhere, but I would like all the field types to be pluggable, as this will be a public feature in the future. It just needs to be a standard class that all field types inherit from, with appropriate functions for:
  * Output in:
    * Front end HTML/Javascript
    * Admin editor HTMLJavascript
    * Admin list view HTML/Javascript
    * Admin full view HTML/Javascript
  * Filters:
    * Javascript for checking input against filters
    * PHP for checking input against filters
    * PHP for sanity checking filters
    * HTML help for filters