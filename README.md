# admin tool Import_user_datas
This is a tool to import user datas and preferences from a remote moodle (server moodle) into the current moodle (client moodle)
## Installation

## Settings
### webservice install
Generate token on user server moodle (migr)
```shell
php /moodle_path/admin/tool/import_user_datas/cli/init_webservice.php
```

or
```shell
moosh -n webservice-install wstoolimportuserdatas 'tool/import_user_datas:get_user_preferences_and_datas_for_user,tool/import_user_datas:set_user_preferences_and_datas_for_user,moodle/site:config'
```

### Plugin Settings
* Fill the required plugin settings on web moodle interface
  * activated : checked if the plugin is activated
  * trigger_on_create : trigger user datas import when a user is created
  * remote_url : remote moodle url
  * paging : number of import task to treat per task launch
  * preference : user preferences to import
  * user_datas : user datas to import

```shell
moosh -n config-set activated 1 tool_import_user_datas # 0 if not activated
moosh -n config-set trigger_on_create 1 tool_import_user_datas # 0 if not activated
moosh -n config-set remote_url <URL> tool_import_user_datas
moosh -n config-set remote_token <REMOTE_TOKEN> tool_import_user_datas
moosh -n config-set paging 0 tool_import_user_datas # to 0 take all tasks
moosh -n config-set preferences <preferences_separated_by_;> tool_import_user_datas #defaults are provided
moosh -n config-set user_datas <user_datas_separated_by_;> tool_import_user_datas #defaults are provided
```
## Set schedule task 
set frequency parameters for task Import user datas task that launch user datas and preferences sync beetween remote and current moodle
  * \tool_import_user_datas\task\perform_scheduled_preferences_import

## Script to program user imports 
Script to program user imports in a moodle containing already users
```shell
php admin/tool/import_user_datas/cli/program_import_user_datas.php --auth=<auth>
# where auth is required and is the auth method name e.g CAS, manual
```