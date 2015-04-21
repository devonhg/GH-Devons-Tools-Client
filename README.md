# GH-Devons-Tools-Client
This is a really simple plugin that adds a "client" role to a wordpress website and an administrator webmaster user. Do note however that this plugin is intended to be TEMPORARY! Due to the nature of the administrator webmaster role this can be a significant security risk. Should the client role need to continue however, create a webmaster user manually and remove the webmaster functionality from the file (Lines 38-42).

##Inherits
The Client Role inherits all powers of the admin role. 

##Removals
The Client Role removes the following capabilities from the inherited admin role.   

*promote_users   
*remove_users   
*switch_themes   
*update_plugins   
*update_themes   
*list_users   
*delete_plugins   
*create_users   
*add_users   
*edit_themes   
*manage_options   