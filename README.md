# GH-Devons-Tools-Client
This is a really simple plugin that adds a "client" role to a wordpress website and an administrator webmaster user. Do note that after the plugin is activated log into webmaster and change the password! ( Default password is "devonstools" ) 


##Client Role

###Capability Inherits
The Client Role inherits all powers of the admin role. 

###Capability Removals
The Client Role removes the following capabilities from the inherited admin role.   

*promote_users   
*remove_users   
*switch_themes   
*update_plugins   
*update_themes   
*list_users   
*delete_plugins   
*install_plugins   
*edit_plugins   
*delete_plugins   
*activate_plugins   
*create_users   
*add_users   
*edit_themes   
*manage_options   

The client role also has most dashboard notifications hidden. 


##Uninstallation 
When removing the plugin, all clients are changed to administrators. Whether or not the webmaster user is removed depends on if the webmaster is the one that removed the plugin. If the webmaster removes the plugin, the webmaster user remains active on the website. However if another account removes it, the webmaster user is removed as well. 