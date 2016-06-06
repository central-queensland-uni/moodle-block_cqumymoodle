<a href="https://travis-ci.org/central-queensland-uni/moodle-block_cqumymoodle">
<img src="https://travis-ci.org/central-queensland-uni/moodle-block_cqumymoodle.svg?branch=master">
</a>


Installation
============

Installation is very straight forward. Just drop the cqumymoodle folder into the path/to/moodle/blocks directory and either navigate to http://www.yourmoodle.com/admin/index.php OR run php admin/cli/upgrade.php from the command line in your dirroot.

Web Service Configuration
=========================

1. Make sure you have webservices and REST is turned on

* Site administration > Advanced features
* Make sure "Enable web services" is checked
* Site administration > Plugins > Web services > Manage protocols > Enable REST protocol

2. Create a new user for webservice functions, if user does not exist(this is for added security)

* Site administration > Users > Accounts > Add a new user
* Fill in the form, and make sure "Choose an authentication method" is set to "Web services authentication"

3. Create a custom role for webservice functions and assign the webservice user to this role

* Site administration > Users > Permissions > Define roles
* Add a new role > "Use role or archetype" > Set to "No role"
* Context types where this role may be assigned, set to "System"
* Assign the following permissions:

Permissions
-----------

moodle/user:update
moodle/course:useremail
moodle/course:viewparticipants
moodle/course:view
moodle/user:viewdetails
moodle/user:viewhiddendetails
webservice/rest:use

4. Create a new external service

* Site administration > Plugins > Web services > External services
* Under Custom services click "Add"
* Give it a unique name, make sure "Enabled" and "Authorised users only" is checked
* Click "Add service"
* Click "Add functions"
* Search for cqu_get_user_courses
* Click "Add functions"
* Go back to External services, under your newly created custom service, click on Authorised users
* Add the webservice user you created in step 2

5. Create a token for this external service

* Site administration > Plugins > Web services > Manage tokens
* Click on "Add"
* Search for the webservice user you created in step 2 in "User"
* Choose the custom external service that you created in step 4 in "Service"
* You can choose to configure "IP restriction" and "Valid until" for more security

Block Configuration
===================

Note: You may add this block wherever blocks are allowed but for the sake of this tutorial, we are going to assume that you are adding it to the /my page and have configured your site to go to /my instead of /index.php or SITE

* Site adminsitration > Appearance > Default My home page
* Turn "Blocks editing on"
* Add block of type "CQU MyMoodle"
* Move it to wherever you want to on the page
* Edit the block settings (click on the hand icon or gear icon)
* Give the block a unique name
* For Endpoint, the normal path is "www.yourmoodle.com/webservice/rest/server.php"
* "External Moodle" is checked if we are connecting to another Moodle site that also has this block installed
* WebService Token, is the token we created in the Web Service configuration steps above
