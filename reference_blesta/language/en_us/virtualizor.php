<?php



// Basics
$lang['virtualizor.name'] = "Virtualizor";
$lang['virtualizor.module_row'] = "Virtualizor Master Server";
$lang['virtualizor.module_row_plural'] = "Servers";
$lang['virtualizor.module_group'] = "Virtualizor Master Group";


// Add module row
$lang['virtualizor.add_row.box_title'] = "Add Virtualizor Server";
$lang['virtualizor.add_row.basic_title'] = "Basic Settings";
$lang['virtualizor.add_row.add_btn'] = "Add Server";

// Module row meta data
$lang['virtualizor.row_meta.server_name'] = "Hostname";
$lang['virtualizor.row_meta.keypass'] = "Key Pass";
$lang['virtualizor.row_meta.key'] = "Key";
$lang['virtualizor.row_meta.host'] = "IP Address";
$lang['virtualizor.row_meta.port'] = "SSL Port Number";

$lang['virtualizor.package_fields.random_password'] = "Randomize Password";
$lang['virtualizor.package_fields.random_password_tooltip'] = "If checked, there will be no prompts for a password when creating a service.";

// Edit module row
$lang['virtualizor.edit_row.box_title'] = "Edit Virtualizor Server";
$lang['virtualizor.edit_row.basic_title'] = "Basic Settings";
$lang['virtualizor.edit_row.add_btn'] = "Update Server";




// Module management
$lang['virtualizor.add_module_row'] = "Add Server";
$lang['virtualizor.add_module_group'] = "Add Server Group";
$lang['virtualizor.manage.module_rows_title'] = "Virtualizor Master Servers";
$lang['virtualizor.manage.module_groups_title'] = "Virtualizor Master Server Groups";
$lang['virtualizor.manage.module_rows_heading.server_label'] = "Server Label";
$lang['virtualizor.manage.module_rows_heading.host'] = "Hostname";
$lang['virtualizor.manage.module_rows_heading.options'] = "Options";
$lang['virtualizor.manage.module_groups_heading.name'] = "Group Name";
$lang['virtualizor.manage.module_groups_heading.servers'] = "Server Count";
$lang['virtualizor.manage.module_groups_heading.options'] = "Options";
$lang['virtualizor.manage.module_rows.edit'] = "Edit";
$lang['virtualizor.manage.module_groups.edit'] = "Edit";
$lang['virtualizor.manage.module_rows.delete'] = "Delete";
$lang['virtualizor.manage.module_groups.delete'] = "Delete";
$lang['virtualizor.manage.module_rows.confirm_delete'] = "Are you sure you want to delete this server?";
$lang['virtualizor.manage.module_groups.confirm_delete'] = "Are you sure you want to delete this server group?";
$lang['virtualizor.manage.module_rows_no_results'] = "There are no servers.";
$lang['virtualizor.manage.module_groups_no_results'] = "There are no server groups.";

$lang['virtualizor.order_options.first'] = "First non-full server";



// Server types
$lang['virtualizor.types.openvz'] = "OpenVZ";
$lang['virtualizor.types.xen'] = "Xen";
$lang['virtualizor.types.xen_hvm'] = "Xen HVM";
$lang['virtualizor.types.kvm'] = "KVM";
$lang['virtualizor.types.xcp'] = "XCP";
$lang['virtualizor.types.xcp_hvm'] = "XCP HVM";
$lang['virtualizor.types.lxc'] = "LXC";
$lang['virtualizor.types.vzk'] = "Virtuozzo KVM";
$lang['virtualizor.types.vzo'] = "Virtuozzo OpenVZ";
$lang['virtualizor.types.proxo'] = "Proxmox OpenVZ";
$lang['virtualizor.types.proxk'] = "Proxmox KVM";
$lang['virtualizor.types.proxl'] = "Proxmox LXC";

// Common
$lang['virtualizor.please_select'] = "-- Please Select --";
$lang['virtualizor.hostname'] = "Hostname";
$lang['virtualizor.password'] = "Password";
$lang['virtualizor.confirm_password'] = "Confirm Password";
$lang['virtualizor.os'] = "OS";
$lang['virtualizor.username'] = "Username";
$lang['virtualizor.os'] = "OS";

// Package fields
$lang['virtualizor.package_fields.type'] = "Type";
$lang['virtualizor.package_fields.os'] = "OS Template";
$lang['virtualizor.package_fields.template'] = "Template";
$lang['virtualizor.package_fields.admin_set_template'] = "Select a template";
$lang['virtualizor.package_fields.client_set_template'] = "Let client set template";
$lang['virtualizor.package_fields.plan'] = "Plan";

$lang['virtualizor.package_fields.assigned_nodes'] = "Assigned Nodes";
$lang['virtualizor.package_fields.available_nodes'] = "Available Nodes";



// ERROR CODES


$lang['virtualizor.!error.server_name.empty'] = "Please enter a Server Hostname.";
$lang['virtualizor.!error.key.empty'] = "Please enter a Key.";
$lang['virtualizor.!error.keypass.format'] = "Please enter a Keypass.";
$lang['virtualizor.!error.host.format'] = "Please enter a host (IP address or FQDN).";

$lang['virtualizor.!error.api.internal'] = "An internal error occurred, or the server did not respond to the request.";



// ACTION TABS

// Actions Tab
$lang['virtualizor.tab_actions.heading_actions'] = "Actions";

$lang['virtualizor.tab_actions.status_online'] = "Online";
$lang['virtualizor.tab_actions.status_offline'] = "Offline";
$lang['virtualizor.tab_actions.status_disabled'] = "Disabled";
$lang['virtualizor.tab_actions.server_status'] = "Server Status";

$lang['virtualizor.tab_actions.heading_reinstall'] = "Reinstall";
$lang['virtualizor.tab_actions.field_template'] = "Template";
$lang['virtualizor.tab_actions.field_confirm'] = "I understand that by reinstalling, all data on the server will be permanently deleted, and the selected template will be installed.";
$lang['virtualizor.tab_actions.field_reinstall_submit'] = "Reinstall";

$lang['virtualizor.tab_actions.heading_hostname'] = "Change Hostname";
$lang['virtualizor.tab_actions.text_hostname_reboot'] = "A change to the hostname will only take effect after the server has been rebooted.";
$lang['virtualizor.tab_actions.field_hostname'] = "Hostname";
$lang['virtualizor.tab_actions.field_hostname_submit'] = "Change Hostname";

$lang['virtualizor.tab_actions.heading_password'] = "Change Password";
$lang['virtualizor.tab_actions.field_password'] = "New Root Password";
$lang['virtualizor.tab_actions.field_confirm_password'] = "Confirm Password";
$lang['virtualizor.tab_actions.field_password_submit'] = "Change Password";


// Client Actions Tab
$lang['virtualizor.tab_client_actions.heading_actions'] = "Actions";
$lang['virtualizor.tab_client_actions.heading_server_status'] = "Server Status";

$lang['virtualizor.tab_client_actions.status_online'] = "Online";
$lang['virtualizor.tab_client_actions.status_offline'] = "Offline";
$lang['virtualizor.tab_client_actions.status_disabled'] = "Disabled";

$lang['virtualizor.tab_client_actions.heading_reinstall'] = "Reinstall";
$lang['virtualizor.tab_client_actions.field_template'] = "Template";
$lang['virtualizor.tab_client_actions.field_confirm'] = "I understand that by reinstalling, all data on the server will be permanently deleted, and the selected template will be installed.";
$lang['virtualizor.tab_client_actions.field_reinstall_submit'] = "Reinstall";

$lang['virtualizor.tab_client_actions.heading_hostname'] = "Change Hostname";
$lang['virtualizor.tab_client_actions.text_hostname_reboot'] = "A change to the hostname will only take effect after the server has been rebooted.";
$lang['virtualizor.tab_client_actions.field_hostname'] = "Hostname";
$lang['virtualizor.tab_client_actions.field_hostname_submit'] = "Change Hostname";

$lang['virtualizor.tab_client_actions.heading_password'] = "Change Password";
$lang['virtualizor.tab_client_actions.field_password'] = "New Root Password";
$lang['virtualizor.tab_client_actions.field_confirm_password'] = "Confirm Password";
$lang['virtualizor.tab_client_actions.field_password_submit'] = "Change Password";

// Console Tab
$lang['virtualizor.tab_console.heading_console'] = "Launch VNC";
$lang['virtualizor.tab_console.console_username'] = "VNC Username:";
$lang['virtualizor.tab_console.console_password'] = "VNC Password:";

$lang['virtualizor.tab_console.vnc_ip'] = "VNC Host:";
$lang['virtualizor.tab_console.vnc_port'] = "VNC Port:";
$lang['virtualizor.tab_console.vnc_password'] = "VNC Password:";

$lang['virtualizor.!error.virtualizor_hostname.format'] = "The hostname appears to be invalid.";

$lang['virtualizor.!error.virtualizor_root_password.length'] = "The root password must be at least 6 characters in length.";
$lang['virtualizor.!error.virtualizor_root_password.matches'] = "The root passwords do not match.";

$lang['virtualizor.!error.api.template.valid'] = "The selected template is invalid.";
$lang['virtualizor.!error.api.confirm.valid'] = "You must acknowledge that you understand the reinstall action in order to perform the template reinstallation.";

//Client package fields


?>