<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
* License, v. 2.0. If a copy of the MPL was not distributed with this
* file, You can obtain one at http://mozilla.org/MPL/2.0/. */

// Render the header and sidebar
require_once(realpath(__DIR__ . '/../includes/renderutils.php'));
render_header_and_sidebar(['CUSTOM:permissions-widget.js'], ['check_admin' => true]);

// Include required functions file
require_once(realpath(__DIR__ . '/../includes/mail.php'));

$admin = 0;
$default = 0;
// Check if save role responsibilites was submitted
if(isset($_POST['save_role_responsibilities']))
{
    $role_id = (int)$_POST['role'];
    $responsibilities = isset($_POST['permissions']) ? array_filter($_POST['permissions'], 'ctype_digit') : [];
    $admin = isset($_POST['admin']) ? '1' : '0';
    $default = isset($_POST['default']) ? '1' : '0';
    
    // Check if role was submitted
    if($role_id)
    {
        save_role_responsibilities($role_id, $admin, $default, $responsibilities);
        set_alert(true, "good", $lang['SavedSuccess']);
    }
    else
    {
        set_alert(true, "bad", "Role is required.");
    }
//    refresh();
}
//Check if adding role was submitted 
elseif(isset($_POST['add_role']))
{
    $role_name = $_POST['role_name'];
    if($role_name)
    {
        add_name("role", $role_name);
        set_alert(true, "good", $escaper->escapeHtml($lang['AddedSuccess']));
        refresh();
    }
}
//Check if deleting role was submitted 
elseif(isset($_POST['delete_role']))
{
    $role_id = $_POST['role'];
    if(!$role_id || $role_id==1)
    {
        set_alert(true, "bad", $escaper->escapeHtml($lang['CantDeleteAdministratorRole']));
    }
    else
    {
        delete_role($role_id);
        set_alert(true, "good", $escaper->escapeHtml($lang['DeletedSuccess']));
        refresh();
    }
}
?>
<div class="row bg-white">
    <div class="col-12 ">
        <div class="card-body my-2 border">
            <form method="post" action="">
                <h4><?php echo $escaper->escapeHtml($lang['AddNewRole']); ?></h4>
                <div class="row">
                    <div class="col-md-4">
                        <input name="role_name" value="" class="form-field form-control" type="text" placeholder="<?php echo $escaper->escapeHtml($lang['RoleName']); ?>"  class="form-control"/>                        
                    </div>
                    <div class="col-md-2">
                        <button name="add_role" class="btn btn-submit"><?php echo $escaper->escapeHtml($lang['Add']) ?></button>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body my-2 border">
            <form method="post" action="">
                <h4><?php echo $escaper->escapeHtml($lang['EditRole']); ?></h4>
                <div class="row">
                    <div class="col-md-4">
    <?php
                        create_dropdown("role", (isset($_POST['role']) ? $_POST['role'] : ""), "role", true, false, false, "required");

        if(isset($_POST['role']))
        {
            echo "
                        <script>
                            $(document).ready(function() {
                                get_responsibilities(" . (isset($_POST['role']) ? $_POST['role'] : "") . ");
                            });
                        </script>
            ";
        }
    ?>
                    </div>  
                     <div class="col-md-2">
                        <button class="btn btn-submit" name="delete_role" type="submit"><?php echo $escaper->escapeHtml($lang['Delete']); ?></button>
                     </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12 form-group">
                        <div class="form-check">
                            <input  type="checkbox" name="default" id="default" <?php if ($default) echo "checked='checked'";?> class="form-check-input">
                            <label class="form-check-label mb-0" for="default">
                            &nbsp;&nbsp;<?php echo $escaper->escapeHtml($lang['DefaultUserRole']);?></label>
                        </div>
                    </div>
                    <div class="col-12 form-group admin-button">
                        <button id="admin_button" type="button" class="btn btn-dark" data-grant="<?php echo $escaper->escapeHtml($lang['GrantAdmin']); ?>" data-remove="<?php echo $escaper->escapeHtml($lang['RemoveAdmin']); ?>" title="<?php echo $escaper->escapeHtml($lang['AdminRoleDescription']);?>"><?php echo $escaper->escapeHtml($lang['GrantAdmin']);?></button>
                        <input type="checkbox" name="admin" id="admin">
                        <div class="mt-2 col-4 form-group alert alert-danger admin-alert" role="alert">
                            <?= $escaper->escapeHtml($lang['UserResponsibilitiesCannotBeEditedWhenUserIsAnAdmin']); ?>
                        </div>
                    </div>
                    <div class="col-12 form-group">
                        <h4><?php echo $escaper->escapeHtml($lang['UserResponsibilities']); ?></h4>
                        <div class="permissions-widget">
                            <ul>
                                <li>
                                    <input class="form-check-input" type="checkbox" id="check_all">
                                    <label for="check_all" class="form-check-label mb-0">
                                        &nbsp;&nbsp;<?php echo $escaper->escapeHtml($lang['CheckAll']); ?>
                                    </label>
                                    <ul>
    <?php
        $permission_groups = get_grouped_permissions();

        foreach ($permission_groups as $permission_group_name => $permission_group) {
            $permission_group_id = $escaper->escapeHtml("pg-" . $permission_group[0]['permission_group_id']);
            $permission_group_name = $escaper->escapeHtml($permission_group_name);
            $permission_group_description = $escaper->escapeHtml($permission_group[0]['permission_group_description']);
    ?>       
                                        <li>
                                            <input class="form-check-input permission-group" type="checkbox" id="<?php echo $permission_group_id;?>">
                                            <label for="<?php echo $permission_group_id;?>" title="<?php echo $permission_group_description;?>" class="form-check-label mb-0">
                                                &nbsp;&nbsp;<?php echo $permission_group_name;?>
                                            </label>
                                            <ul>
    <?php
            foreach ($permission_group as $permission) {
                $permission_id = $escaper->escapeHtml($permission['permission_id']);
                $permission_key = $escaper->escapeHtml($permission['key']);
                $permission_name = $escaper->escapeHtml($permission['permission_name']);
                $permission_description = $escaper->escapeHtml($permission['permission_description']);
    ?>       
                                                <li>
                                                    <input class="form-check-input permission" type="checkbox" name="permissions[]" id="<?php echo $permission_key;?>" value="<?php echo $permission_id;?>">
                                                    <label for="<?php echo $permission_key;?>" title="<?php echo $permission_description;?>" class="form-check-label mb-0">
                                                        &nbsp;&nbsp;<?php echo $permission_name;?>
                                                    </label>
                                                </li>
    <?php
            }
    ?>  
                                            </ul>
                                        </li>
    <?php
        }
    ?>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-submit" name="save_role_responsibilities" type="submit"><?php echo $escaper->escapeHtml($lang['Update']); ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){

        if ($("#admin").is(":checked")) {
            check_indeterminate_checkboxes($(".permissions-widget #check_all"));
            make_checkboxes_readonly();
        }

        $("#role").change(function(){
            // If role is unselected, uncheck all responsibilities
            if(!$(this).val()) {
                $("#admin").prop("checked", false);
                $("#default").prop("checked", false);
                $("#admin").prop("readonly", false);

                $(".permissions-widget input[type=checkbox]").prop("checked", false);
                $(".permissions-widget input[type=checkbox]").prop("indeterminate", false);
                make_checkboxes_editable();

                update_admin_button();
            } else {
                $("#admin").prop("checked", false);
                get_responsibilities($(this).val());
            }
        });

        $("#admin_button").click(function(){
            $("#admin").prop("checked", !$("#admin").prop("checked"));
            if ($("#admin").prop("checked")) {
                $(".permissions-widget input[type=checkbox]").prop("checked", true);
                make_checkboxes_readonly();

                check_indeterminate_checkboxes($(".permissions-widget #check_all"));
            } else {
                make_checkboxes_editable();
            }
            update_admin_button();
        });

        update_admin_button();
    });

    function update_admin_button() {
        admin = $("#admin").prop("checked");
        admin_button = $("#admin_button");
        remove_text = admin_button.data("remove");
        grant_text = admin_button.data("grant");

        $("#admin_button").text(admin ? remove_text : grant_text);
        $("#admin_button").prop("disabled", $("#admin").prop("readonly"));
    }

    function get_responsibilities(role_id) {
        $.ajax({
            type: "GET",
            url: BASE_URL + "/api/role_responsibilities/get_responsibilities",
            data: {
                role_id: role_id
            },
            success: function(data) {

                if (data.data) {

                    $("#admin").prop("checked", data.data.admin);
                    $("#default").prop("checked", data.data.default);
                    $("#admin").prop("readonly", data.data.value == "1");

                    update_widget(data.data.responsibilities);

                    if (data.data.admin) {
                        check_indeterminate_checkboxes($(".permissions-widget #check_all"));
                        make_checkboxes_readonly();
                    }
                    update_admin_button();
                }
            },
            error: function(xhr,status,error) {
                if(xhr.responseJSON && xhr.responseJSON.status_message) {
                    showAlertsFromArray(xhr.responseJSON.status_message);
                }
            }
        });
    }
</script>
<?php
    // Render the footer of the page. Please don't put code after this part.
    render_footer();
?>