# Changelog

### dev (2014-07-09)
* Updated/created timestamps were refactored to use Gedmo Timestampable
* Soft deletes were refactored to use Gedmo Softdeleteable
* The NodeTranslationRepository::getNodeTranslationForUrl method signature has changed due to this, the includeDeleted
flag has been removed
* If you use queries that use "deleted = false" (or 0) in the where clause you will have to modify these to use
"deleted_at IS NULL".
* NodeAdminController action methods always pass Request as first parameter (as Controller::getRequest is deprecated).

### dev (2012-08-30)
* Support for ACL permissions instead of our own custom implementation.

### dev (2012-08-24)
* Added the functionality to configure the page action menus. (see Resources/doc/configurable_action_menu.md on how to use this)
