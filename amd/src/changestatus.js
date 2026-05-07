import Ajax from 'core/ajax';
import notification from "core/notification";

/**
 * asyncchangestatus
 * @param {int} id
 * @returns {Promise<void>}
 */
export const asyncchangestatus = async (id) => {
    //AJAX call to webservice
    var selectelt = document.getElementById('menustatus_select_'+id);
    await new Promise(resolve => {
        return Ajax.call([{
            methodname: 'tool_import_user_datas_change_task_status',
            args: {
                'taskid': id,
                'status': selectelt.value,
            },
            done: result => {
                resolve(result);

            },
            fail: notification.exception
        }]);
    });

};