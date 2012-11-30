<?php

/**
 * This is view file for syncoptions
 *
 * PHP version 5
 *
 * @category   JFusion
 * @package    ViewsAdmin
 * @subpackage Syncoptions
 * @author     JFusion Team <webmaster@jfusion.org>
 * @copyright  2008 JFusion. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.jfusion.org
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
//display the paypal donation button
JFusionFunctionAdmin::displayDonate();
?>

<script type="text/javascript">
<!--
var slave_data = <?php echo json_encode($this->slave_data);?>;
var response = { 'completed' : false , 'slave_data' : [] , 'errors' : [] };
var sync_mode = '<?php echo $this->sync_mode;?>';
var syncid = '<?php echo $this->syncid; ?>';

var periodical;

var url = '<?php echo JURI::current(); ?>';
// refresh every 10 seconds
var syncRunning = false;
var counter = 10;

function renderSyncHead() {
    var root = new Element('thead');
    var tr = new Element('tr');

    new Element('th',{'html': '<?php echo JText::_('PLUGIN',true) . ' ' . JText::_('NAME',true); ?>'}).inject(tr);
    new Element('th',{'html': '<?php echo JText::_('SYNC_PROGRESS',true); ?>', 'width': 200}).inject(tr);
    new Element('th',{'html': '<?php echo JText::_('SYNC_USERS_TODO',true); ?>'}).inject(tr);
    new Element('th',{'html': '<?php echo JText::_('USERS',true) . ' ' . JText::_('CREATED',true); ?>'}).inject(tr);
    new Element('th',{'html': '<?php echo JText::_('USERS',true) . ' ' . JText::_('DELETED',true); ?>'}).inject(tr);
    new Element('th',{'html': '<?php echo JText::_('USERS',true) . ' ' . JText::_('UPDATED',true); ?>'}).inject(tr);
    new Element('th',{'html': '<?php echo JText::_('USER',true) . ' ' . JText::_('CONFLICTS',true); ?>'}).inject(tr);
    new Element('th',{'html': '<?php echo JText::_('USERS',true) . ' ' . JText::_('UNCHANGED',true); ?>'}).inject(tr);

    tr.inject(root);
    return root;
}

function renderSyncBody(data) {
    var root = new Element('tBody');
    for (var i=0; i<data.slave_data.length; i++) {
        var info = data.slave_data[i];
        var tr = new Element('tr');

        //NAME
        new Element('td',{'html': info.jname , 'width': 200}).inject(tr);

        // SYNC_PROGRESS
        var outer = new Element('div').inject(tr);
        var pct = ((info.total_to_sync-info.total)/info.total_to_sync) * 100;
        var color = 'blue';
        if (pct == 100) {
            color = 'green';
        }
        new Element('div',{'style': 'background-color:'+color+'; width:'+pct+'%','html': '&nbsp;'}).inject(outer);

        var progress = new Element('td');
        outer.inject(progress);
        progress.inject(tr);

        //SYNC_USERS_TODO
        new Element('td',{'html': info.total_to_sync-(info.total_to_sync-info.total)}).inject(tr);
        //CREATED
        new Element('td',{'html': info.created}).inject(tr);
        //DELETED
        new Element('td',{'html': info.deleted}).inject(tr);
        //UPDATED
        new Element('td',{'html': info.updated}).inject(tr);
        //CONFLICTS
        new Element('td',{'html': info.error}).inject(tr);
        //UNCHANGED
        new Element('td',{'html': info.unchanged}).inject(tr);

        tr.inject(root);
    }
    return root;
}

function renderSync(data) {
    $('log_res').empty();

    var root = new Element('table',{ 'class': 'adminlist' });
    renderSyncHead().inject(root);
    renderSyncBody(data).inject(root);

    root.inject($('log_res'));
}

function update() {
    var text;
    var start = $('start');
    if (!syncRunning) {
        $clear(periodical);

        start.innerHTML = '<?php echo JText::_('RESUME',true); ?>';

        text = '<?php echo JText::_('PAUSED',true); ?>';
    } else if (response.completed) {
        // let's stop our timed ajax
        $clear(periodical);

        text = '<?php echo JText::_('FINISHED',true); ?>';

        start.innerHTML = '<b><?php echo JText::_('CLICK_FOR_MORE_DETAILS',true); ?></b>';
        start.href = 'index.php?option=com_jfusion&task=syncstatus&syncid='+syncid;
        start.removeEvents('click');
    } else {
        text = '<?php echo JText::_('UPDATE_IN'); ?> ' + counter + ' <?php echo JText::_('SECONDS',true); ?>';

        start.innerHTML = '<?php echo JText::_('PAUSE',true); ?>';
    }
    $("counter").innerHTML = '<b>'+text+'</b>';
}

function render(JSONobject) {
    response = JSONobject;
    if (JSONobject.errors.length) {
        $clear(periodical);
        for(var i=0; i<JSONobject.errors.length; i++) {
            alert(JSONobject.errors[i]);
        }
    } else {
        renderSync(JSONobject);

        if (JSONobject.completed) {
            update();
        }
    }
}

window.addEvent('domready', function() {
        /* our ajax istance for starting the sync */
        var ajax = new Request.JSON({
            url: url,
            method: 'get',
            onSuccess: function(JSONobject) {
                render(JSONobject);
            }, onError: function(text) {
                alert(text);
            }
        });

        var ajaxsync = new Request.JSON({
            url: url,
            method: 'get',
            onSuccess: function(JSONobject) {
                render(JSONobject);
            }, onError: function(text) {
                alert(text);
            }
        });

        /* our usersync status update function: */
        var refresh = (function() {
            //add another second to the counter
            counter -= 1;
            if (counter < 1) {
                if (!response.completed) {
                    counter = 10;
                    // dummy to prevent caching of php
                    var dummy = $time() + $random(0, 100);
                    //generate the get variable for submission

                    var subvars = 'option=com_jfusion&task=syncresume&tmpl=component&dummy=' + dummy + '&syncid=' + syncid;
                    var form = $('adminForm');
                    if (form) {
                        for (var i = 0; i < form.elements.length; i++) {
                            if (form.elements[i].name == 'userbatch') {
                                subvars = subvars + '&' + form.elements[i].name + '=' + form.elements[i].value;
                            }
                        }
                    }
                    ajax.send('option=com_jfusion&tmpl=component&task=syncprogress&syncid=' + syncid);
                    ajaxsync.send(subvars);
                }
            } else {
                update();
            }
        });

        // start and stop click events
        $('start').addEvent('click', function(e) {
            // prevent default
            new Event(e).stop();
            if (!syncRunning) {
                // prevent insane clicks to start numerous requests
                $clear(periodical);

                if (sync_mode == 'new') {
                    var form = $('adminForm');
                    var count = 0;
                    var i;

                    if (form) {
                        for(i=0; i<form.elements.length; i++) {
                            if (form.elements[i].type=="select-one") {
                                if (form.elements[i].options[form.elements[i].selectedIndex].value == 1) {
                                    response.slave_data[count] = {"jname":form.elements[i].id,
                                        "total":slave_data[form.elements[i].id]['total'],
                                        "total_to_sync":slave_data[form.elements[i].id]['total'],
                                        "created":0,
                                        "deleted":0,
                                        "updated":0,
                                        "error":0,
                                        "unchanged":0};
                                    count++;
                                }
                            }
                        }
                    }
                    if (response.slave_data.length) {
                        //give the user a last chance to opt-out
                        var answer = confirm("<?php echo JText::_('SYNC_CONFIRM_START',true); ?>");
                        if (answer) {
                            sync_mode = 'resume';
                            //do start
                            syncRunning = true;
                            var paramString = 'option=com_jfusion&task=syncinitiate&tmpl=component&syncid=' + syncid;
                            for(i=0; i<form.elements.length; i++) {
                                if (form.elements[i].type=="select-one") {
                                    if (form.elements[i].options[form.elements[i].selectedIndex].value) {
                                        paramString = paramString + '&' + form.elements[i].name + '=' + form.elements[i].options[form.elements[i].selectedIndex].value;
                                    }
                                }
                                if (form.elements[i].name=='userbatch') {
                                    paramString = paramString + '&' + form.elements[i].name + '=' + form.elements[i].value;
                                }
                            }
                            new Request.JSON({url: url,
                                method: 'get' ,onSuccess: function(JSONobject) {
                                    render(JSONobject);
                                }, onError: function(text) {
                                    alert(text);
                                }}).send(paramString);
                        }
                    } else {
                        alert("<?php echo JText::_('SYNC_NODATA',true); ?>")
                    }
                } else {
                    syncRunning = true;
                }
                if (syncRunning) {
                    periodical = refresh.periodical(1000, this);

                    renderSync(response);
                }
            } else {
                syncRunning = false;
            }
            update();
        });
    }
);
// -->
</script>
<h3><?php echo JText::_('SYNC_WARNING'); ?></h3><br/>

<?php
if ($this->sync_active) {
    echo '<h3 style="color:red;">' . JText::_('SYNC_IN_PROGRESS_WARNING') . "</h3><br />\n" ;
}
?>
<?php if ($this->sync_mode == 'new') { ?>
<div id="log_res">
    <form method="post" action="index.php" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="com_jfusion" />
        <input type="hidden" name="task" value="syncstatus" />
        <input type="hidden" name="syncid" value="<?php echo $this->syncid; ?>" />
        <div class="ajax_bar">
            <?php echo JText::_('SYNC_DIRECTION_SELECT'); ?>
            <select name="action" style="margin-right:10px; margin-left:5px;">
                <option value="master"><?php echo JText::_('SYNC_MASTER'); ?></option>
                <option value="slave"><?php echo JText::_('SYNC_SLAVE'); ?></option>
            </select>
            <?php echo JText::_('SYNC_NUMBER_OF_USERS'); ?>
            <input name="userbatch" class="inputbox" style="margin-right:10px; margin-left:5px;" value="500"/>
        </div>
        <br/>

        <table class="adminlist" style="border-spacing:1px;">
            <thead>
            <tr>
                <th width="50px"><?php echo JText::_('NAME'); ?></th>
                <th width="50px"><?php echo JText::_('TYPE'); ?></th>
                <th width="50px"><?php echo JText::_('USERS'); ?></th>
                <th width="200px"><?php echo JText::_('OPTIONS'); ?></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><?php echo $this->master_data['jname']; ?></td>
                <td><?php echo JText::_('MASTER') ?></td>
                <td><?php echo $this->master_data['total']; ?></td>
                <td></td>
            </tr>

                <?php
                foreach ($this->slave_data as $slave) { ?>
                <tr>
                    <td><?php echo $slave['jname']; ?></td>
                    <td><?php echo JText::_('SLAVE') ?></td>
                    <td><?php echo $slave['total']; ?></td>
                    <td>
                        <select id="<?php echo $slave['jname']; ?>" name="slave[<?php echo $slave['jname']; ?>][perform_sync]">
                            <option value=""><?php echo JText::_('SYNC_EXCLUDE_PLUGIN'); ?></option>
                            <option value="1"><?php echo JText::_('SYNC_INCLUDE_PLUGIN'); ?></option>
                        </select>
                    </td>
                </tr>
                    <?php }
                ?>
            </tbody>
        </table>
    </form>
</div>
<?php
} else {
    ?>
<div id="log_res">
</div>
<script type="text/javascript">
    <!--
    response = <?php echo json_encode($this->syncdata);?>;
    renderSync(response);
    // -->
</script>
<?php
} ?>
<br/>
<div id="counter"></div>
<br/>
<div class="ajax_bar">
    <b><?php echo JText::_('SYNC_CONTROLLER'); ?></b>&nbsp;&nbsp;&nbsp;
    <a id="start" href="#"><?php echo JText::_('START'); ?></a>
</div>