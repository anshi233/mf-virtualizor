<?php


/**
 * Virtualizor Module for Blesta
 * Last Updated : 04-03-2023
 * Version : 2.1.3
 *
*/

error_reporting(0);
// error_reporting(~E_NOTICE & ~E_WARNING);
// ini_set('display_errors', 0);

include_once(dirname(__FILE__).'/functions.php');
include(dirname(__FILE__).'/virtualizor_conf.php');

class virtualizor extends Module {
	
	/**
	 * @var string The version of this module
	 */
	private static $version = "2.1.3";
	/**
	 * @var string The authors of this module
	 */
	private static $authors = array(array('name'=>"Softaculous Ltd.",'url'=>"https://www.virtualizor.com"));
	
	/**
	 * Initializes the module
	 */
	public function __construct() {
		// Load components required by this module
		Loader::loadComponents($this, array("Input"));
		
		// Load the language required by this module
		Language::loadLang("virtualizor", null, dirname(__FILE__) . DS . "language" . DS);
	}	
	
	/**
	 * Returns the name of this module
	 *
	 * @return string The common name of this module
	 */
	public function getName(){
		return Language::_("virtualizor.name", true);
	}
	
	/**
	 * Returns the version of this module
	 *
	 * @return string The current version of this module
	 */
	public function getVersion() {
		return self::$version;
	}

	/**
	 * Returns the name and URL for the authors of this module
	 *
	 * @return array A numerically indexed array that contains an array with key/value pairs for 'name' and 'url', representing the name and URL of the authors of this module
	 */
	public function getAuthors() {
		return self::$authors;
	}
	
	/**
	 * Returns a noun used to refer to a module row (e.g. "Server", "VPS", "Reseller Account", etc.)
	 *
	 * @return string The noun used to refer to a module row
	 */
	public function moduleRowName() {
		return Language::_("virtualizor.module_row", true);
	}
	
	/**
	 * Returns the value used to identify a particular service
	 *
	 * @param stdClass $service A stdClass object representing the service
	 * @return string A value used to identify this service amongst other similar services
	 */
	public function getServiceName($service) {
		foreach ($service->fields as $field) {
			if ($field->key == "virtualizor_domain")
				return $field->value;
		}
		return null;
	}
	
	/**
	 * Returns the key used to identify the primary field from the set of module row meta fields.
	 * This value can be any of the module row meta fields.
	 *
	 * @return string The key used to identify the primary field from the set of module row meta fields
	 */
	public function moduleRowMetaKey() {
		return "server_name";
	}
	
	/**
	 * Returns a noun used to refer to a module row in plural form (e.g. "Servers", "VPSs", "Reseller Accounts", etc.)
	 *
	 * @return string The noun used to refer to a module row in plural form
	 */
	public function moduleRowNamePlural() {
		return Language::_("virtualizor.module_row_plural", true);
	}
	
	/**
	 * Returns a noun used to refer to a module group (e.g. "Server Group", "Cloud", etc.)
	 *
	 * @return string The noun used to refer to a module group
	 */
	public function moduleGroupName() {
		return Language::_("virtualizor.module_group", true);
	}
	
	/**
	 * Returns an array of available service delegation order methods. The module
	 * will determine how each method is defined. For example, the method "first"
	 * may be implemented such that it returns the module row with the least number
	 * of services assigned to it.
	 *
	 * @return array An array of order methods in key/value pairs where the key is the type to be stored for the group and value is the name for that option
	 * @see Module::selectModuleRow()
	 */
	public function getGroupOrderOptions() {
		return array('first'=>Language::_("virtualizor.order_options.first", true));
	}
	
	
	/**
	 * Determines which module row should be attempted when a service is provisioned
	 * for the given group based upon the order method set for that group.
	 *
	 * @return int The module row ID to attempt to add the service with
	 * @see Module::getGroupOrderOptions()
	 */
	public function selectModuleRow($module_group_id) {
		if (!isset($this->ModuleManager))
			Loader::loadModels($this, array("ModuleManager"));
		
		$group = $this->ModuleManager->getGroup($module_group_id);
		
		if ($group) {
			switch ($group->add_order) {
				default:
				case "first":
					
					foreach ($group->rows as $row) {
						return $row->id;
					}
					
					break;
			}
		}
		return 0;
	}
	
	/**
	 * Returns the rendered view of the manage module page
	 *
	 * @param mixed $module A stdClass object representing the module and its rows
	 * @param array $vars An array of post data submitted to or on the manage module page (used to repopulate fields after an error)
	 * @return string HTML content containing information to display when viewing the manager module page
	 */
	public function manageModule($module, array &$vars) {
		// Load the view into this object, so helpers can be automatically added to the view
		$this->view = new View("manage", "default");
		$this->view->base_uri = $this->base_uri;
		$this->view->setDefaultView("components" . DS . "modules" . DS . "virtualizor" . DS);
		
		// Load the helpers required for this view
		Loader::loadHelpers($this, array("Form", "Html", "Widget"));
		
		$this->view->set("module", $module);
		
		return $this->view->fetch();
	}
	
	/**
	 * Returns the rendered view of the add module row page
	 *
	 * @param array $vars An array of post data submitted to or on the add module row page (used to repopulate fields after an error)
	 * @return string HTML content containing information to display when viewing the add module row page
	 */
	public function manageAddRow(array &$vars) {
		// Load the view into this object, so helpers can be automatically added to the view
		$this->view = new View("add_row", "default");
		$this->view->base_uri = $this->base_uri;
		$this->view->setDefaultView("components" . DS . "modules" . DS . "virtualizor" . DS);
		
		// Load the helpers required for this view
		Loader::loadHelpers($this, array("Form", "Html", "Widget"));
		
		$this->view->set("vars", (object)$vars);
		return $this->view->fetch();	
	}
	
	/**
	 * Returns the rendered view of the edit module row page
	 *
	 * @param stdClass $module_row The stdClass representation of the existing module row
	 * @param array $vars An array of post data submitted to or on the edit module row page (used to repopulate fields after an error)
	 * @return string HTML content containing information to display when viewing the edit module row page
	 */	
	public function manageEditRow($module_row, array &$vars) {
		// Load the view into this object, so helpers can be automatically added to the view
		$this->view = new View("edit_row", "default");
		$this->view->base_uri = $this->base_uri;
		$this->view->setDefaultView("components" . DS . "modules" . DS . "virtualizor" . DS);
		
		// Load the helpers required for this view
		Loader::loadHelpers($this, array("Form", "Html", "Widget"));
		
		if (empty($vars))
			$vars = $module_row->meta;
		
		$this->view->set("vars", (object)$vars);
		return $this->view->fetch();
	}
	
	/**
	 * Retrieves the module row given the server or server group
	 *
	 * @param string $module_row The module row ID
	 * @param string $module_group The module group (optional, default "")
	 * @return mixed An stdClass object representing the module row, or null if it could not be determined
	 */
	private function getModuleRowByServer($module_row, $module_group = "") {
		
		// Fetch the module row available for this package
		$row = null;
		if ($module_group == "") {
			if ($module_row > 0) {
				$row = $this->getModuleRow($module_row);
			}
			else {
				$rows = $this->getModuleRows();
				
				if (isset($rows[0]))
					$row = $rows[0];
				unset($rows);
			}
		}
		else {
			// Fetch the 1st server from the list of servers in the selected group
			$rows = $this->getModuleRows($module_group);
			
			if (isset($rows[0]))
				$row = $rows[0];
			unset($rows);
		}
		return $row;
	}	
	
	/**
	 * Returns an array of key values for fields stored for a module, package,
	 * and service under this module, used to substitute those keys with their
	 * actual module, package, or service meta values in related emails.
	 *
	 * @return array A multi-dimensional array of key/value pairs where each key is one of 'module', 'package', or 'service' and each value is a numerically indexed array of key values that match meta fields under that category.
	 * @see Modules::addModuleRow()
	 * @see Modules::editModuleRow()
	 * @see Modules::addPackage()
	 * @see Modules::editPackage()
	 * @see Modules::addService()
	 * @see Modules::editService()
	 */
	public function getEmailTags() {
		return array(
			'module' => array("host", "port"),
			'package' => array(),
			'service' => array("vpsid", "virtualizor_domain", "virtualizor_username", "virtualizor_password", "virtualizor_rootpass", "virtualizor_ip", "virtualizor_additional_ips"
			//, "virtualizor_vnc_ip", "virtualizor_vnc_port", "virtualizor_vnc_pass"
			)
		);
	}
	
	/**
	 * Returns all fields to display to an admin attempting to add a service with the module
	 *
	 * @param stdClass $package A stdClass object representing the selected package
	 * @param $vars stdClass A stdClass object representing a set of post fields
	 * @return ModuleFields A ModuleFields object, containg the fields to render as well as any additional HTML markup to include
	 */
	public function getAdminAddFields($package, $vars=null) {
		
		Loader::loadHelpers($this, array("Html"));
		$fields = new ModuleFields();
		
		// Create domain label
		$domain = $fields->label(Language::_("virtualizor.hostname", true), "virtualizor_domain");
		// Create domain field and attach to domain label
		$domain->attach($fields->fieldText("virtualizor_domain", $this->Html->ifSet($vars->virtualizor_domain), array('id'=>"virtualizor_domain")));
		// Set the label as a field
		$fields->setField($domain);
		unset($domain);
		
		// Only show the password field if the package does not use random passwords
		if (empty($package->meta->random_password)) {

			// Create password label
			$password = $fields->label(Language::_("virtualizor.password", true), "virtualizor_password");
			// Create password field and attach to password label
			$password->attach($fields->fieldText("virtualizor_password", $this->Html->ifSet($vars->virtualizor_password), array('id'=>"virtualizor_password")));
			// Set the label as a field
			$fields->setField($password);
			unset($password);

		}
		
		// OS Template as a selectable option
		$os_temp = array('' => Language::_("virtualizor.please_select", true)) + $this->getTemplates($package);
		$OS = $fields->label(Language::_("virtualizor.os", true), "OS");
		$OS->attach($fields->fieldSelect("OS", $os_temp, $this->Html->ifSet($vars->OS), array('id' => "OS")));
		$fields->setField($OS);
		unset($OS);
		
		$fields->setHTML("<script type=\"text/javascript\">
		$(document).ready(function(){
			$('#virtualizor_domain, #virtualizor_password').attr('required', true);
		});
		</script>");
		return $fields;
	}
	
	/**
	 * Returns all fields used when adding/editing a package, including any
	 * javascript to execute when the page is rendered with these fields.
	 *
	 * @param $vars stdClass A stdClass object representing a set of post fields
	 * @return ModuleFields A ModuleFields object, containg the fields to render as well as any additional HTML markup to include
	 */
	 
	public function getPackageFields($vars=null) {
		
		Loader::loadHelpers($this, array("Form", "Html"));
		$fields = new ModuleFields();
		
		// Show nodes, and set javascript field toggles
		$this->Form->setOutput(true);
		
		
		// Fetch all packages available for the given server or server group
		$module_row = $this->getModuleRowByServer((isset($vars->module_group) ? $vars->module_group : 0), (isset($vars->module_group) ? $vars->module_group : ""));
		
		$templates = array();
		$nodes = array();
		$tmp_servers = array();
		$plans = array();
		$result = new Record();
		$data = $result->select(array("module_row_id","key","value","encrypted"))->from("module_row_meta")->fetchAll();
	
		// Build the server list
		foreach($data as $k => $v){
				
			$val = $v->value;
			
			// Key and pass are stored encrypted so we have to decrypt it
			if($v->key == 'key' || $v->key == 'keypass'){
				
				// If it is encrypted then only Decrypt it
				if(!empty($v->encrypted)){
					$val = $this->getdecryptpass($v->value);
				}
				
				if(empty($val)){
					$val = $v->value;
				}
			}			
			$_servers[$v->module_row_id][$v->key] = $val;
		}
		
		$tmp_servers = $_servers;

		//Just to be on the safe side $tmp_servers is used instead of $_servers
		$_module_row = (!empty($vars->module_row) ? $vars->module_row : array_shift(array_keys($tmp_servers)));
		
		$ip = $_servers[$_module_row]['host'];
		$pass = $_servers[$_module_row]['keypass'];
		$username = $_servers[$_module_row]['key'];
		
		//Make an api call
		$data = $this->make_api_call($ip, $username, $pass, 'index.php?act=addvs');
		// $this->log($module_row->meta->host . "|List Plans", serialize($data), "input", true);
		//Configure plans
		$plans['-1'] = '(-1) - Use Old Module';
		foreach($data['plans'] as $k => $v){
			$plans[$v['plid']] = $v['plid'].' - '.$v['plan_name'];
		}
		
		// Build the default node / group field
		$tmp_node_grp['Auto Select Server'] = 'Auto Select Server';
		
		foreach ($data['servergroups'] as $k => $v){
			
			$tmp_node_grp[$k.' - [G] '.$v['sg_name']] = $k.' - [G] '.$v['sg_name'];
			
			foreach ($data['servers'] as $m => $n){
				if($n['sgid'] == $k){
					$tmp_node_grp[$m." - ".$n['server_name']] = "\t".$m." - ".$n['server_name'];
				}
			}
		}
		
		// We are taking this from URL as on change of anything it is posting the fields
		// http://DOMAIN/DIR/admin/packages/add/
		$_tmp_url = basename($GLOBALS['_ENV']['HTTP_REFERER']);
		
		$fields->setHtml("
			<script type=\"text/javascript\">
				$(document).ready(function() {
					togglevirtualizor_fields();
				});
				
				$('#kernel, #virtualizor_plan').change(function() {
					fetchModuleOptions();
				});
	
				function togglevirtualizor_fields() {
					// Hide fields dependent on this value
					if ($('#kernel').val() == '') {
						$('#virtualizor_plan').parent('li').hide();
						$('#server_groups').parent('li').hide();
					}
					// Show fields dependent on this value
					else{
						$('#virtualizor_plan').parent('li').show();
						$('#server_groups').parent('li').show();
					}
				}
			</script>");
		
		// Set the virtualizor type as a selectable option
		$types = array('' => Language::_("virtualizor.please_select", true)) + $this->getTypes();
		$type = $fields->label(Language::_("virtualizor.package_fields.type", true), "kernel");
		$type->attach($fields->fieldSelect("meta[type]", $types, $this->Html->ifSet($vars->meta['type']), array('id' => "kernel")));
		$fields->setField($type);
		unset($type);
		
		// Are you using OLD module ?
		// Because in OLD module there are no plans available
		
		if((empty($vars->meta['plan']) || $vars->meta['plan'] < 0) && $_tmp_url != 'add'){
			
			// Selecting Virtualizor Plans
			$plan = $fields->label("Select Plan", "virtualizor_plan");
			$plan->attach($fields->fieldSelect("meta[plan]", $plans,
			$this->Html->ifSet($vars->meta['plan']), array('id' => "virtualizor_plan")));					
			$fields->setField($plan);
			
			// Selecting the Server Groups
			$server_groups = $fields->label("Server Groups", "server_groups");
			$server_groups->attach($fields->fieldSelect("meta[sg]", $tmp_node_grp,
			$this->Html->ifSet($vars->meta['sg']), array('id' => "server_groups")));					
			$fields->setField($server_groups);
			
			// HDD field
			$hdd = $fields->label("Disk Space - GB", "hdd");
			$hdd->attach($fields->fieldText("meta[hdd]", $this->Html->ifSet($vars->meta['hdd']), array('id'=>"hdd")));
			$fields->setField($hdd);
			unset($hdd);
			
			// Inodes field
			$inodes = $fields->label("Inodes (OpenVZ)", "inodes");
			$inodes->attach($fields->fieldText("meta[inodes]", $this->Html->ifSet($vars->meta['inodes']), array('id'=>"inodes")));
			$fields->setField($inodes);
			unset($inodes);
			
			// RAM field
			$ram = $fields->label("Guaranteed RAM - MB", "ram");
			$ram->attach($fields->fieldText("meta[ram]", $this->Html->ifSet($vars->meta['ram']), array('id'=>"ram")));
			$fields->setField($ram);
			unset($ram);
			
			// burst field
			$burst = $fields->label("Burstable RAM - MB (OpenVZ)", "burst");
			$burst->attach($fields->fieldText("meta[burst]", $this->Html->ifSet($vars->meta['burst']), array('id'=>"burst")));
			$fields->setField($burst);
			unset($burst);
			
			// Swap RAM field
			$swapram = $fields->label("Swap RAM - MB (XEN and KVM)", "swapram");
			$swapram->attach($fields->fieldText("meta[swapram]", $this->Html->ifSet($vars->meta['swapram']), array('id'=>"swapram")));
			$fields->setField($swapram);
			unset($swapram);
			
			// Bandwidth field
			$bandwidth = $fields->label("Bandwidth - GB", "bandwidth");
			$bandwidth->attach($fields->fieldText("meta[bandwidth]", $this->Html->ifSet($vars->meta['bandwidth']), array('id'=>"bandwidth")));
			$fields->setField($bandwidth);
			unset($bandwidth);
			
			// CPU Units field
			$cpu = $fields->label("CPU Units - (OpenVZ and XEN)", "cpu");
			$cpu->attach($fields->fieldText("meta[cpu]", $this->Html->ifSet($vars->meta['cpu']), array('id'=>"cpu")));
			$fields->setField($cpu);
			unset($cpu);
			
			// CPU Cores field
			$cores = $fields->label("CPU Cores", "cores");
			$cores->attach($fields->fieldText("meta[cores]", $this->Html->ifSet($vars->meta['cores']), array('id'=>"cores")));
			$fields->setField($cores);
			unset($cores);
			
			// CPU % field
			$cpu_percent = $fields->label("CPU % - (OpenVZ and XEN)", "cpu_percent");
			$cpu_percent->attach($fields->fieldText("meta[cpu_percent]", $this->Html->ifSet($vars->meta['cpu_percent']), array('id'=>"cpu_percent")));
			$fields->setField($cpu_percent);
			unset($cpu_percent);
			
			// IO Priority field
			$priority = $fields->label("IO Priority - (OpenVZ)", "priority");
			$priority->attach($fields->fieldText("meta[priority]", $this->Html->ifSet($vars->meta['priority']), array('id'=>"priority")));
			$fields->setField($priority);
			unset($priority);
			
			// IPs field
			$ips = $fields->label("Number of IPv4 IPs", "ips");
			$ips->attach($fields->fieldText("meta[ips]", $this->Html->ifSet($vars->meta['ips']), array('id'=>"ips")));
			$fields->setField($ips);
			unset($ips);
			
			// IPv6 field
			$ips6 = $fields->label("Number of IPv6 IPs", "ips6");
			$ips6->attach($fields->fieldText("meta[ips6]", $this->Html->ifSet($vars->meta['ips6']), array('id'=>"ips6")));
			$fields->setField($ips6);
			unset($ips6);
			
			// IPv6 subnet field
			$ips6_subnet = $fields->label("Number of IPv6 Subnets", "ips6_subnet");
			$ips6_subnet->attach($fields->fieldText("meta[ips6_subnet]", $this->Html->ifSet($vars->meta['ips6_subnet']), array('id'=>"ips6_subnet")));
			$fields->setField($ips6_subnet);
			unset($ips6_subnet);
			
			// Network Speed field
			$network_speed = $fields->label("Network Speed - KB/s (Zero for unlimited)", "network_speed");
			$network_speed->attach($fields->fieldText("meta[network_speed]", $this->Html->ifSet($vars->meta['network_speed']), array('id'=>"network_speed")));
			$fields->setField($network_speed);
			unset($network_speed);
			
			// Server Speed field
			$server = $fields->label("Server - Slave Server name if any", "server");
			$server->attach($fields->fieldText("meta[server]", $this->Html->ifSet($vars->meta['server']), array('id'=>"server")));
			$fields->setField($server);
			unset($server);
			
			// Server Group field
			$server_group = $fields->label("Server Group - To choose a Server", "server_group");
			$server_group->attach($fields->fieldText("meta[server_group]", $this->Html->ifSet($vars->meta['server_group']), array('id'=>"server_group")));
			$fields->setField($server_group);
			unset($server_group);
			
			// OS field
			$OS = $fields->label("OS Templates", "OS");
			$OS->attach($fields->fieldText("meta[OS]", $this->Html->ifSet($vars->meta['OS']), array('id'=>"OS")));
			$fields->setField($OS);
			unset($OS);
			
			// Virtio field
			$virtio = $fields->label("Virt IO", "virtio");
			$virtio_label = $fields->label("Enable Virt IO", "virtio_id");
			$virtio->attach($fields->fieldCheckbox("meta[virtio]", "1", $this->Html->ifSet($vars->meta['virtio']), array('id' => "virtio_id"), $vnc_label));
			// Attach Tooltip
			$virtio->attach($fields->tooltip("Enable the VirtIO"));
			$fields->setField($virtio);
			unset($virtio);
			
			$vnc = $fields->label("VNC", "vnc");
			$vnc_label = $fields->label("Enable VNC", "vnc_id");
			$vnc->attach($fields->fieldCheckbox("meta[vnc]", "1", $this->Html->ifSet($vars->meta['vnc']), array('id' => "vnc_id"), $vnc_label));
			// Attach Tooltip
			$vnc->attach($fields->tooltip("Enable the VNC"));
			$fields->setField($vnc);
			unset($vnc);
		
		// So you are using NEW Module ?
		}else{
			
			//SHOW ONLY SERVER, PLANS, TYPE AND SERVERGROUPS
			
			// Selecting Virtualizor Plans
			$plan = $fields->label("Select Plan", "virtualizor_plan");
			$plan->attach($fields->fieldSelect("meta[plan]", $plans,
			$this->Html->ifSet($vars->meta['plan']), array('id' => "virtualizor_plan")));					
			$fields->setField($plan);
			
			// Selecting the Server Groups
			$server_groups = $fields->label("Server Groups", "server_groups");
			$server_groups->attach($fields->fieldSelect("meta[sg]", $tmp_node_grp,
			$this->Html->ifSet($vars->meta['sg']), array('id' => "server_groups")));					
			$fields->setField($server_groups);

			// OS field
			$OS = $fields->label("OS Templates", "OS");
			$OS->attach($fields->fieldText("meta[OS]", $this->Html->ifSet($vars->meta['OS']), array('id'=>"OS")));
			$fields->setField($OS);
			unset($OS);
			
			$this->log($module_row->meta->host . "|List Plans", serialize($plan), "input", true);
		}

		// Random Password field
		$random_password = $fields->label(Language::_("virtualizor.package_fields.random_password", true), "random_password");
		$random_password->attach($fields->fieldCheckbox("meta[random_password]", true, $this->Html->ifSet($vars->meta['random_password']), array('id' => "random_password")));

		$random_password->attach($fields->tooltip(Language::_('virtualizor.package_fields.random_password_tooltip', true)));    
		$fields->setField($random_password);
		unset($random_password);

		return $fields;
	}
	
	/**
	 * Retrieves a list of server types and their language
	 *
	 * @return array A list of server types and their language
	 */
	private function getTypes() {
	
		return array(
			'openvz' => Language::_("virtualizor.types.openvz", true),
			'xcp' => Language::_("virtualizor.types.xcp", true),
			'xcp hvm' => Language::_("virtualizor.types.xcp_hvm", true),
			'xen' => Language::_("virtualizor.types.xen", true),
			'xen hvm' => Language::_("virtualizor.types.xen_hvm", true),
			'kvm' => Language::_("virtualizor.types.kvm", true),
			'lxc' => Language::_("virtualizor.types.lxc", true),
			'vzk' => Language::_("virtualizor.types.vzk", true),
			'vzo' => Language::_("virtualizor.types.vzo", true),
			'proxo' => Language::_("virtualizor.types.proxo", true),
			'proxk' => Language::_("virtualizor.types.proxk", true),
			'proxl' => Language::_("virtualizor.types.proxl", true)
		);
	}	
	
	/**
	 * Returns all fields to display to a client attempting to add a service with the module
	 *
	 * @param stdClass $package A stdClass object representing the selected package
	 * @param $vars stdClass A stdClass object representing a set of post fields
	 * @return ModuleFields A ModuleFields object, containg the fields to render as well as any additional HTML markup to include
	 */	
	public function getClientAddFields($package, $vars=null) {
		Loader::loadHelpers($this, array("Html"));
		
		// Fetch all packages available for the given server or server group
		$module_row = $this->getModuleRowByServer((isset($vars->module_group) ? $vars->module_group : 0), (isset($vars->module_group) ? $vars->module_group : ""));
		
		$fields = new ModuleFields();
		
		// Create domain label
		$domain = $fields->label(Language::_("virtualizor.hostname", true), "virtualizor_domain");
		// Create domain field and attach to domain label
		$domain->attach($fields->fieldText("virtualizor_domain", $this->Html->ifSet($vars->virtualizor_domain), array('id'=>"virtualizor_domain")));
		// Set the label as a field
		$fields->setField($domain);
		unset($domain);
		
		// Only show the password field if the package does not use random passwords
		if(empty($package->meta->random_password)) {

			// Create password label
			$password = $fields->label(Language::_("virtualizor.password", true), "virtualizor_password");
			// Create password field and attach to password label
			$password->attach($fields->fieldPassword("virtualizor_password", array('id'=>"virtualizor_password")));
			// Set the label as a field
			$fields->setField($password);

			unset($password);

			// Confirm password label
			$confirm_password = $fields->label(Language::_("virtualizor.confirm_password", true), "virtualizor_confirm_password");
			// Create confirm password field and attach to password label
			$confirm_password->attach($fields->fieldPassword("virtualizor_confirm_password", array('id'=>"virtualizor_confirm_password")));
			// Set the label as a field
			$fields->setField($confirm_password);
			unset($confirm_password);

		}

		// OS Template as a selectable option
		$os_temp = array('' => Language::_("virtualizor.please_select", true)) + $this->getTemplates($package);
		$OS = $fields->label(Language::_("virtualizor.os", true), "OS");
		$OS->attach($fields->fieldSelect("OS", $os_temp, $this->Html->ifSet($vars->OS), array('id' => "OS")));
		$fields->setField($OS);
		unset($OS);
		
		$fields->setHTML("<script type=\"text/javascript\">
		$(document).ready(function(){
			$('#virtualizor_domain, #virtualizor_confirm_password, #virtualizor_password').attr('required', true);
		});
		</script>");

		return $fields;
	}	
	
	/**
	 * Returns all fields to display to an admin attempting to edit a service with the module
	 *
	 * @param stdClass $package A stdClass object representing the selected package
	 * @param $vars stdClass A stdClass object representing a set of post fields
	 * @return ModuleFields A ModuleFields object, containg the fields to render as well as any additional HTML markup to include
	 */	
	public function getAdminEditFields($package, $vars=null) {
		Loader::loadHelpers($this, array("Html"));
		$fields = new ModuleFields();
		
		// Create domain label
		$domain = $fields->label(Language::_("virtualizor.hostname", true), "virtualizor_domain");
		// Create domain field and attach to domain label
		$domain->attach($fields->fieldText("virtualizor_domain", $this->Html->ifSet($vars->virtualizor_domain), array('id'=>"virtualizor_domain")));
		// Set the label as a field
		$fields->setField($domain);

		// Create username label
		$username = $fields->label(Language::_("virtualizor.username", true), "virtualizor_username");
		// Create username field and attach to username label
		$username->attach($fields->fieldText("virtualizor_username", $this->Html->ifSet($vars->virtualizor_username), array('id'=>"virtualizor_username")));
		// Set the label as a field
		$fields->setField($username);
		
		// Create password label
		$password = $fields->label(Language::_("virtualizor.password", true), "virtualizor_password");
		// Create password field and attach to password label
		$password->attach($fields->fieldText("virtualizor_password", array('id'=>"virtualizor_password")));
		// Set the label as a field
		$fields->setField($password);

		// OS Template as a selectable option
		$os_temp = array('' => Language::_("virtualizor.please_select", true)) + $this->getTemplates($package);
		$OS = $fields->label(Language::_("virtualizor.os", true), "OS");
		$OS->attach($fields->fieldSelect("OS", $os_temp, $this->Html->ifSet($vars->OS), array('id' => "OS")));
		$fields->setField($OS);
		unset($OS);
		
		// vpsid
		$vpsid = $fields->label("vpsid", "vpsid");
		$vpsid->attach($fields->fieldText("vpsid", $this->Html->ifSet($vars->vpsid), array('id'=>"vpsid")));
		$fields->setField($vpsid);

		$fields->setHTML("<script type=\"text/javascript\">
		$(document).ready(function(){
			$('#virtualizor_domain, #virtualizor_username, #vpsid, #virtualizor_password').attr('required', true);
		});
		</script>");

		return $fields;
	}	
	
	/**
	 * Fetches the HTML content to display when viewing the service info in the
	 * admin interface.
	 *
	 * @param stdClass $service A stdClass object representing the service
	 * @param stdClass $package A stdClass object representing the service's package
	 * @return string HTML content containing information to display when viewing the service info
	 */
	public function getAdminServiceInfo($service, $package) {
		$row = $this->getModuleRow();
		// Get the service fields
		$service_fields = $this->serviceFieldsToObject($service->fields);
		
		// Load the view into this object, so helpers can be automatically added to the view
		$this->view = new View("admin_service_info", "default");
		$this->view->base_uri = $this->base_uri;
		$this->view->setDefaultView("components" . DS . "modules" . DS . "virtualizor" . DS);
		
		// Load the helpers required for this view
		Loader::loadHelpers($this, array("Form", "Html"));

		$this->view->set("module_row", $row);
		$this->view->set("package", $package);
		$this->view->set("service", $service);
		$this->view->set("service_fields", $this->serviceFieldsToObject($service->fields));
		$this->view->set("view", $this->view->view);
		
		return 'Hostname - '.$service_fields->virtualizor_domain.' IP Address - '.$service_fields->virtualizor_ip;
		return $this->view->fetch();
	}
	
	/**
	 * Fetches the HTML content to display when viewing the service info in the
	 * client interface.
	 *
	 * @param stdClass $service A stdClass object representing the service
	 * @param stdClass $package A stdClass object representing the service's package
	 * @return string HTML content containing information to display when viewing the service info
	 */
	public function getClientServiceInfo($service, $package) {
		$row = $this->getModuleRow();
		// Get the service fields
		$service_fields = $this->serviceFieldsToObject($service->fields);
		
		// Load the view into this object, so helpers can be automatically added to the view
		$this->view = new View("client_service_info", "default");
		$this->view->base_uri = $this->base_uri;
		$this->view->setDefaultView("components" . DS . "modules" . DS . "virtualizor" . DS);
		
		// Load the helpers required for this view
		Loader::loadHelpers($this, array("Form", "Html"));

		$this->view->set("module_row", $row);
		$this->view->set("package", $package);
		$this->view->set("service", $service);
		$this->view->set("service_fields", $this->serviceFieldsToObject($service->fields));
		$this->view->set("view", $this->view->view);
		
		return 'Hostname - '.$service_fields->virtualizor_domain.' IP Address - '.$service_fields->virtualizor_ip;
		
		return $this->view->fetch();
	}
	
	/**
	 * Returns all tabs to display to an admin when managing a service whose
	 * package uses this module
	 *
	 * @param stdClass $package A stdClass object representing the selected package
	 * @return array An array of tabs in the format of method => title. Example: array('methodName' => "Title", 'methodName2' => "Title2")
	 */
	public function getAdminTabs($package) {
		return array(
			'tabActions' => "Control Panel",
		);
	}
	
	/**
	 * Statistics tab (bandwidth/disk usage)
	 *
	 * @param stdClass $package A stdClass object representing the current package
	 * @param stdClass $service A stdClass object representing the current service
	 * @param array $get Any GET parameters
	 * @param array $post Any POST parameters
	 * @param array $files Any FILES parameters
	 * @return string The string representing the contents of this tab
	 */
	public function tabStats($package, $service, array $get=null, array $post=null, array $files=null) {
	
		$this->view = new View("tab_stats", "default");
		$this->view->base_uri = $this->base_uri;
		
		Loader::loadHelpers($this, array("Form", "Html"));
		
		// Get the service fields
		$service_fields = $this->serviceFieldsToObject($service->fields);
		$module_row = $this->getModuleRow($package->module_row);
		
		// For the Call
		$username = $module_row->meta->key;
		$pass = $module_row->meta->keypass;
		$ip = $module_row->meta->host;
		$path = 'index.php?act=vpsmanage&';
		$response = $this->e_make_api_call($ip, $username, $pass, $service_fields->vpsid, $path);
		
		if(empty($response)){
			$this->Input->setErrors(array('api' => array('internal' => Language::_("virtualizor.!error.api.internal", true))));
		}
		
		// Set default vars // usually used for theme
		if (empty($vars))
			$vars = array('vpsid' => $service_fields->vpsid, 'stats' => $response, 'virt' => $package->meta->type);
		
		$this->view->set("vars", (object)$vars);
		$this->view->set("client_id", $service->client_id);
		$this->view->set("service_id", $service->id);
		
		$this->view->set("view", $this->view->view);
		$this->view->setDefaultView("components" . DS . "modules" . DS . "virtualizor" . DS);
		return $this->view->fetch();
	}
	
	/**
	 * Console tab
	 *
	 * @param stdClass $package A stdClass object representing the current package
	 * @param stdClass $service A stdClass object representing the current service
	 * @param array $get Any GET parameters
	 * @param array $post Any POST parameters
	 * @param array $files Any FILES parameters
	 * @return string The string representing the contents of this tab
	 */
	public function tabConsole($package, $service, array $get=null, array $post=null, array $files=null) {
	
		$this->view = new View("tab_console", "default");
		$this->view->base_uri = $this->base_uri;
		
		Loader::loadHelpers($this, array("Form", "Html"));
		
		// Get the service fields
		$service_fields = $this->serviceFieldsToObject($service->fields);
		$module_row = $this->getModuleRow($package->module_row);
		
		if($package->meta->type != 'openvz'){
		
			// For the Call
			$username = $module_row->meta->key;
			$pass = $module_row->meta->keypass;
			$ip = $module_row->meta->host;
			$path = 'index.php?act=vnc&launch=1';
			
			$params = array('vpsid' => $service_fields->vpsid, 'access' => "enable");

			$this->log($module_row->meta->host . "|VPS Console", serialize($params), "input", true);
			
			$response = $this->e_make_api_call($ip, $username,  $pass, $service_fields->vpsid, $path);
			
			if(empty($response)){
				return false;
			}
		}
		
		// Set default vars // usually used for theme
		if (empty($vars))
			$vars = array('vpsid' => $service_fields->vpsid, 'vncip' => $response['info']['ip'], 'vncport' => $response['info']['port'], 'vncpassword' => $response['info']['password'], 'virt' => $package->meta->type);
		
		
		$this->view->set("vars", (object)$vars);
		$this->view->set("client_id", $service->client_id);
		$this->view->set("service_id", $service->id);
		
		$this->view->set("view", $this->view->view);
		$this->view->setDefaultView("components" . DS . "modules" . DS . "virtualizor" . DS);
		return $this->view->fetch();
	}
	
	public function virtualizor_UI($package, $service, $url, $modules_url, array $get=null, array $post=null) {
		
		global $virt_verify, $virt_errors, $virtualizor_conf, $db;
		$service_fields = $this->serviceFieldsToObject($service->fields);
		$module_row = $this->getModuleRow($package->module_row);
		
		$params['package_id'] = $package->pricing[0]->package_id;
		$params['client_id'] = $service->client_id;
		$params['vpsid'] = $service->fields[9]->value;
		$params['service_id'] = $service->id;

		if(empty($params['vpsid'])){ //Checks for vps
			$string = array(
					'value'=>'VPS is not provisioned'
			);
			
			return implode("\n*", array_values($string));
		}

		if(isset($get['give'])) {
			
			$var['APP'] = 'Virtualizor';
			$var['site_name'] = 'Blesta';
			$var['copyright'] = 'Virtualizor';
			$var['API'] = $url.'?&api=json&';
			$var['url'] = $url.'/?';
			$var['giver'] = $url.'?';
			$var['version'] = '2.1.3';
			$var['logo'] = '';
			$var['theme'] = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$this->base_uri.'../components/modules/virtualizor/ui/';
			$var['theme_path'] = dirname(__FILE__).'/ui/';
			$var['images'] = $var['theme'].'images/';
			$var['virt_dev_license'] = ' ';
			$var['virt_pirated_license'] = ' ';
			
			// If it is Admin panel
			if(preg_match('/tabActions/', $url)){
				$var['font_path'] = '../../../../../';
			}else{
				$var['font_path'] = '../../../../';
			}
			
			if($get['give'] == 'index.html'){
				
				if(function_exists('ob_gzhandler')){
					ob_start('ob_gzhandler');
				}
				
				$data = file_get_contents($var['theme_path'].'index.html');
			
				$filetime = filemtime($var['theme_path'].'index.html');
				
			}
			
			if($get['give'] == 'combined.js'){
				$data = '';
				$jspath = $var['theme_path'].'js2/';
				$files = array('jquery.min.js',
							'jquery.dataTables.min.js',
							'jquery.tablesorter.min.js',
							'jquery.flot.min.js',
							'jquery.flot.pie.min.js',
							'jquery.flot.stack.min.js',
							'jquery.flot.time.min.js',
							'jquery.flot.tooltip.min.js',
							'jquery.flot.symbol.min.js',
							'jquery.flot.axislabels.js',
							'jquery.flot.selection.min.js',
							'jquery.flot.resize.min.js',
							'jquery.scrollbar.min.js',
							'popper.min.js',
							'select2.js',
							'bootstrap.min.js',
							'jquery.responsivetabs.js',
							'virtualizor.js',
							'haproxy.js',
							'jquery-simple-tree-table.js'
						);
								
				foreach($files as $k=>$v){
					
					$data .= file_get_contents($jspath.'/'.$v)."\n\n";
				}
				
				if(function_exists('ob_gzhandler')){
					ob_start('ob_gzhandler');
				}
				
				header("Content-type: text/javascript; charset: UTF-8");
				
				$filetime = filemtime($var['theme_path'].'js2/virtualizor.js');					
			}
			
			if($get['give'] == 'style.css'){				
				$data = '';
				$csspath = $var['theme_path'].'css2';
				$files = array('bootstrap.min.css',
								'all.min.css',
								'jquery.dataTables.min.css',
								'select2.css',
								'jquery.scrollbar.css',
								'style.css'
							);
				foreach($files as $k => $v){					
					$data .= file_get_contents($csspath.'/'.$v)."\n\n";
				}
				@header("Content-Type: text/CSS");
				
				if(function_exists('ob_gzhandler')){
					ob_start('ob_gzhandler');
				}
			}
			
			foreach($var as $k => $v){
				$data = str_replace('[['.$k.']]', $v, $data);
			}

			if(@load_database()){
				
				//$this->log("db - object : ", serialize($db), "input", true);
				//check if client has permissions to set lang if yes than we will load langauge set by client default is english 
				$res = @makequery('SELECT `value` FROM `company_settings` WHERE `key` = "client_set_lang"');
				$use_client_lang = @vsql_fetch_assoc($res);
				$use_client_lang = $use_client_lang['value'];

				$table_name = 'company_settings';
				$where = '';

				if($use_client_lang == "true"){
					$table_name = 'client_settings';
					$where = ' AND client_id = '.$service->client_id;
				}

				$res = @makequery('SELECT `value` FROM `'.$table_name.'` 
									WHERE `key` = "language"'.$where);
				$lang = @vsql_fetch_assoc($res);
				$lang = $lang['value'];
			}

			if(empty($lang)){
				$lang = 'en_us';
			}
			
			$vm_short = 'VPS';
			if(!empty($virtualizor_conf['vm_short'])){
				$vm_short = $virtualizor_conf['vm_short'];
			}
			define('VM_SHORT', $vm_short);
			
			$vm_long = 'Virtualizor Servers';
			if(!empty($virtualizor_conf['vm_long'])){
				$vm_long = $virtualizor_conf['vm_long'];
			}
			define('VM_LONG', $vm_long);
			
			vload_lang($lang);
			echo vparse_lang($data);
			die();			
			exit(0);
		}
		
		if(@$get['api'] == 'json'){
			
			$get['svs'] = $service_fields->vpsid;
			$get['SET_REMOTE_IP'] = $_SERVER['REMOTE_ADDR'];
			$username = $module_row->meta->key;
			$pass = $module_row->meta->keypass;
			$ip = $module_row->meta->host;
			$path = 'index.php?'.http_build_query($get).'&';
			
			$res = $this->e_make_api_call($ip, $username, $pass, $get['svs'], $path, $_POST);
			
			$res['uid'] = 0;
			
			echo json_encode($res);
			die();	
			exit(0);
			
		}
		
		$get['svs'] = $service_fields->vpsid;
		$username = $module_row->meta->key;
		$pass = $module_row->meta->keypass;
		$ip = $module_row->meta->host;
		
		if(@$get['b'] == 'novnc' || (!empty($get['novnc'])) && $get['act'] == 'vnc'){				

			$path = 'index.php?';
			$data = $this->e_make_api_call($ip, $username, $pass, $get['svs'], $path.'act=vnc&novnc=1');
			
			//Get Server Hostname
			$host = $module_row->meta->host;
			
			//fetch the novnc file			
			$novnc_viewer = file_get_contents($modules_url.'/virtualizor/novnc/novnc.html');
			$novnc_password = $data['info']['password'];
			$vpsid = $service_fields->vpsid;
			$novnc_serverip = empty($host) ? $ip : $host;
			$proto = 'http';
			$port = 4081;
			$virt_port = 4082;
			$websockify = 'websockify';
			
			//Incase if HTTPS is enabled
			if(!empty($_SERVER['HTTPS'])){
				$proto = 'https';
				$port = 4083;
				$virt_port = 4083;	
				$websockify = 'novnc/';
				$novnc_serverip = empty($host) ? $ip : $host;
			}
			
			if($data['info']['virt'] == 'xcp'){
				$vpsid .= '-'.$data['info']['password'];
			}
			
			echo $novnc_viewer = vlang_vars_name($novnc_viewer, array('HOST' => $novnc_serverip,
															'PORT' => $port,
															'VIRTPORT' => $virt_port,
															'PROTO' => $proto,
															'WEBSOCKET' => $websockify,
															'TOKEN' => $vpsid,
															'PASSWORD' => $novnc_password,
															'BASE_URL' => $GLOBALS['container']['minphp.constants']['WEBDIR']));
            		
			die();
		}

		if(@$get['act'] == 'vnc' && !empty($get['launch'])){
			$path = 'index.php?';
			$response = $this->e_make_api_call($ip, $username, $pass, $get['svs'], $path.'act=vnc&launch=1&giveapplet=1');
			
			if(empty($response)){
				return false;
			}
			
			//Is the applet code in API Response?
			if(!empty($response['info']['applet'])){
				
				$applet = $response['info']['applet'];
			}else{
				$type = $package->meta->type;
				$virttype = preg_match('/xcp/is', $type) ? 'xcp' : strtolower($type);
				
				//NonXCP
				if($virttype!= 'xcp'){
					if(!empty($response['info']['port'])&& !empty($response['info']['ip']) && !empty($response['info']['password'])){
						$applet = '<APPLET ARCHIVE="https://s2.softaculous.com/a/virtualizor/files/VncViewer.jar" CODE="com.tigervnc.vncviewer.VncViewer" WIDTH="1" HEIGHT="1">
						<PARAM NAME="HOST" VALUE="'.$response['info']['ip'].'">
						<PARAM NAME="PORT" VALUE="'.$response['info']['port'].'">
						<PARAM NAME="PASSWORD" VALUE="'.$response['info']['password'].'">
						<PARAM NAME="Open New Window" VALUE="yes">
					</APPLET>';
					}
					//XCP
				}else{
					if(!empty($response['info']['port']) && !empty($response['info']['ip'])){
						$applet = '<APPLET ARCHIVE="https://s2.softaculous.com/a/virtualizor/files/TightVncViewer.jar" CODE="com.tightvnc.vncviewer.VncViewer" WIDTH="1" HEIGHT="1">
						<PARAM NAME="SOCKETFACTORY" value="com.tightvnc.vncviewer.SshTunneledSocketFactory">
						<PARAM NAME="SSHHOST" value="'.$response['info']['ip'].'">
						<PARAM NAME="HOST" value="localhost">
						<PARAM NAME="PORT" value="'.$response['info']['port'].'">
						<PARAM NAME="Open New Window" VALUE="yes">
					</APPLET>';
					}
				}				
			}
			echo $applet;
			die();
		}
		
		return;
	}
	
	/**
	 * Actions tab (boot, reboot, shutdown, etc.)
	 *
	 * @param stdClass $package A stdClass object representing the current package
	 * @param stdClass $service A stdClass object representing the current service
	 * @param array $get Any GET parameters
	 * @param array $post Any POST parameters
	 * @param array $files Any FILES parameters
	 * @return string The string representing the contents of this tab
	 */
	public function tabActions($package, $service, array $get=null, array $post=null, array $files=null) {
		
		
		$params['client_id'] = $service->client_id;
		$params['service_id'] = $service->id;
		
		$modules_url = 'components/modules';
		
		$url = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$this->base_uri.'clients/servicetab/'.$params['client_id'].'/'.$params['service_id'].'/tabActions';		
		//Virtualizor Panel will be displayed
		echo $this->virtualizor_UI($package, $service, $url, $modules_url, $get, $post);
		
		$this->view = new View("tab_actions", "default");
		$this->view->base_uri = $this->base_uri;
		
		// Load the helpers required for this view
		Loader::loadHelpers($this, array("Form", "Html"));		
		
		// Get the service fields
		$service_fields = $this->serviceFieldsToObject($service->fields);
		$module_row = $this->getModuleRow($package->module_row);
		$templates = $this->getTemplates($package);
		
		// Perform the actions
		$vars = $this->actionsTab($package, $service, $templates, false, $get, $post);
		
		$pass = $module_row->meta->keypass;
		$ip = $module_row->meta->host;
		//$this->view->set("vars", (object)$vars);
		$this->view->set("client_id", $service->client_id);
		$this->view->set("service_id", $service->id);
		
		$this->view->set("view", $this->view->view);
		$this->view->setDefaultView("components" . DS . "modules" . DS . "virtualizor" . DS);
		return $this->view->fetch();
	}
	
	// Handle the Action TAB
	private function actionsTab($package, $service, $templates, $client=false, array $get=null, array $post=null) {
		
		$vars = array();
		
		// Get the service fields
		$service_fields = $this->serviceFieldsToObject($service->fields);
		$module_row = $this->getModuleRow($package->module_row);
		
		$username = $module_row->meta->key;
		$pass = $module_row->meta->keypass;
		$ip = $module_row->meta->host;
		
		$get_key = "3";
		if ($client)
			$get_key = "2";
			
		// Perform actions
		if (isset($get[$get_key])) {
			
			switch ($get[$get_key]) {
				case "start":
				
					$path = 'index.php?act=start&do=1';
					$virt_resp = $this->e_make_api_call($ip, $username, $pass, $service_fields->vpsid, $path);
					
					if(empty($virt_resp)){
						$this->Input->setErrors(array('api' => array('internal' => Language::_("virtualizor.!error.api.internal", true))));
					}
					
					break;
				case "stop":
				
					$path = 'index.php?act=stop&do=1';
					$virt_resp = $this->e_make_api_call($ip, $username, $pass, $service_fields->vpsid, $path);
					
					if(empty($virt_resp)){
						$this->Input->setErrors(array('api' => array('internal' => Language::_("virtualizor.!error.api.internal", true))));
					}
					
					break;
				case "reboot":
				
					$path = 'index.php?act=restart&do=1';
					$virt_resp = $this->e_make_api_call($ip, $pass, $service_fields->vpsid, $path);
					
					if(empty($virt_resp)){
						$this->Input->setErrors(array('api' => array('internal' => Language::_("virtualizor.!error.api.internal", true))));
					}
				
					break;
				case "poweroff":
				
					$path = 'index.php?act=poweroff&do=1';
					$virt_resp = $this->e_make_api_call($ip, $username, $pass, $service_fields->vpsid, $path);
					
					if(empty($virt_resp)){
						$this->Input->setErrors(array('api' => array('internal' => Language::_("virtualizor.!error.api.internal", true))));
					}
					
					break;
				case "hostname":
					// Show the hostname section
					$this->view->set("hostname", true);
					
					if (!empty($post)) {
						$rules = array(
							'hostname' => array(
								'format' => array(
									'rule' => array(array($this, "validateHostName")),
									'message' => Language::_("virtualizor.!error.virtualizor_hostname.format", true)
								)
							)
						);
						
						// Validate the template and perform the reinstallation
						$this->Input->setRules($rules);
						if ($this->Input->validates($post)) {
						
							$fields = array(
											'newhost' => $post['hostname'],
											'changehost' => 'Change Hostname'
											);
		
							$path = 'index.php?act=hostname&';
							$virt_resp = $this->e_make_api_call($ip, $username, $pass, $service_fields->vpsid, $path, $fields);
							
							if(empty($virt_resp['done'])){
								$this->Input->setErrors(array('api' => array('internal' => 'Error in changing the Hostname')));
							}
							
							// Do not show the hostname section again
							$this->view->set("hostname", false);
						}
					}
					break;
				case "password":
					// Show the root password section
					$this->view->set("password", true);
					
					if (!empty($post)) {
						$rules = array(
								'password' => array(
										'length' => array(
														'rule' => array("minLength", 6),
														'message' => Language::_("virtualizor.!error.virtualizor_root_password.length", true)
														),
										'matches' => array(
														'rule' => array("compares", "==", (isset($post['confirm_password']) ? $post['confirm_password'] : null)),
														'message' => Language::_("virtualizor.!error.virtualizor_root_password.matches", true)
														)
													)
						);
						
						// Validate the template and perform the reinstallation
						$this->Input->setRules($rules);
						if ($this->Input->validates($post)) {
						
							$fields = array(
											'newpass' => $post['password'],
											'conf' => $post['confirm_password'],
											'changepass' => 'Change Password'
											);
		
							$path = 'index.php?act=changepassword&';
							$virt_resp = $this->e_make_api_call($ip, $username, $pass, $service_fields->vpsid, $path, $fields);
							
							if(empty($virt_resp['done'])){
								$this->Input->setErrors(array('api' => array('internal' => 'Error changing the Password')));
							}
                            
							// Do not show the password section again
							$this->view->set("password", false);
						}
					}
					break;
				case "reinstall":
					// Show the reinstall section
					$this->view->set("reinstall", true);
					
					// Get the template list for error validation
					$template_list = array();
					foreach($templates as $kk => $vv){
						foreach($vv as $k => $v){
							$template_list[$k] = $v['name'];
						}
					}
					
					if (!empty($post)) {
						$rules = array(
							'template' => array(
											'valid' => array(
															'rule' => array("array_key_exists", $template_list),
															'message' => Language::_("virtualizor.!error.api.template.valid", true)
															)
												),
							'confirm' => array(
											'valid' => array(
															'rule' => array("compares", "==", "1"),
															'message' => Language::_("virtualizor.!error.api.confirm.valid", true)
															)
												),

							'virt_newpass' => array(
									'length' => array(
													'rule' => array("minLength", 6),
													'message' => Language::_("virtualizor.!error.virtualizor_root_password.length", true)
													),
									'matches' => array(
													'rule' => array("compares", "==", (isset($post['virt_passconf']) ? $post['virt_passconf'] : null)),
													'message' => Language::_("virtualizor.!error.virtualizor_root_password.matches", true)
													)
												)												
						);
						
						// Validate the template and perform the reinstallation
						$this->Input->setRules($rules);
						if ($this->Input->validates($post)) {
							
							$fields = array(
										'newos' => $post['template'],
										'newpass' => $post['virt_newpass'],
										'conf' => $post['virt_passconf'],
										'reinsos' => 'Reinstall'
										);
										
							$path = 'index.php?act=ostemplate&';
							$virt_resp = $this->e_make_api_call($ip, $username, $pass, $service_fields->vpsid, $path, $fields);
							
							if(empty($virt_resp['done'])){
								$create_error = implode('<br>', $ret['error']);
								$this->Input->setErrors(array('api' => array('internal' => 'Error Rebuilding the VPS<br>'.$create_error)));
							}
							
							// Do not show the reinstall section again
							$this->view->set("reinstall", false);
						}
					}
					break;
				default:
					break;
			}
		}
		
		return $vars;
	}
	
	
	/**
	 * Returns all tabs to display to a client when managing a service whose
	 * package uses this module
	 *
	 * @param stdClass $package A stdClass object representing the selected package
	 * @return array An array of tabs in the format of method => title. Example: array('methodName' => "Title", 'methodName2' => "Title2")
	 */
	public function getClientTabs($package) {
		return array(
			'tabClientActions' => "Control Panel",
		);
	}
	
	// Client Panel service Tabs
	public function tabClientActions($package, $service, array $get=null, array $post=null, array $files=null) {
	
		$params['package_id'] = $package->pricing[0]->package_id;
		$params['service_id'] = $service->id;
		
		$modules_url = 'components/modules';
		
		$url = (!empty($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$this->base_uri.'services/manage/'.$params['service_id'].'/tabClientActions';
				
		echo $this->virtualizor_UI($package, $service, $url, $modules_url, $get, $post);
		
		$this->view = new View("tab_client_actions", "default");
		$this->view->base_uri = $this->base_uri;
		
		// Load the helpers required for this view
		Loader::loadHelpers($this, array("Form", "Html"));
		
		// Get the service fields
		$service_fields = $this->serviceFieldsToObject($service->fields);
		$module_row = $this->getModuleRow($package->module_row);
		
		$virt = preg_match('/xen/is', $package->meta->type) ? 'xen' : (preg_match('/xcp/is', $package->meta->type) ? 'xcp' : strtolower($package->meta->type));
		
		// Fetch our template
		$templates = $this->virtTemplates($module_row, $service_fields->vpsid);
		
		// Perform the actions
		$vars = $this->actionsTab($package, $service, $templates[$virt], true, $get, $post);
		
		$username = $module_row->meta->key;
		$pass = $module_row->meta->keypass;
		$ip = $module_row->meta->host;
		
		// We need Status of the VPS
		$path = 'index.php?act=vpsmanage&';
		$virt_resp = $this->e_make_api_call($ip, $username, $pass, $service_fields->vpsid, $path);
		
		$vpsdata['status'] = $virt_resp['info']['status'];
		$vpsdata['hostname'] = $virt_resp['info']['hostname'];
		$vpsdata['os'] = $virt_resp['info']['os']['osid'];

		// Set default vars // usually used for theme
		if (empty($vars))
			$vars = array('vpsid' => $service_fields->vpsid, 'vpsdata' => $vpsdata, 'status' => $virt_resp['info']['status'], 'hostname' => $virt_resp['info']['hostname'], 'templates' => $templates[$virt], 'resp' => $virt_resp['info']);
		
		$this->view->set("vars", (object)$vars);
		$this->view->set("client_id", $service->client_id);
		$this->view->set("service_id", $service->id);
		
		$this->view->set("view", $this->view->view);
		$this->view->setDefaultView("components" . DS . "modules" . DS . "virtualizor" . DS);
		
		return $this->view->fetch();
	}
	
	public function tabClientStats($package, $service, array $get=null, array $post=null, array $files=null) {
	
		$this->view = new View("tab_client_stats", "default");
		$this->view->base_uri = $this->base_uri;
		
		Loader::loadHelpers($this, array("Form", "Html"));
		
		// Get the service fields
		$service_fields = $this->serviceFieldsToObject($service->fields);
		$module_row = $this->getModuleRow($package->module_row);
		
		// For the Call
		$username = $module_row->meta->key;
		$pass = $module_row->meta->keypass;
		$ip = $module_row->meta->host;
		$path = 'index.php?act=vpsmanage&';
		
		$response = $this->e_make_api_call($ip, $username, $pass, $service_fields->vpsid, $path);
		
		if(empty($response)){
			$this->Input->setErrors(array('api' => array('internal' => Language::_("virtualizor.!error.api.internal", true))));
		}
		
		// Set default vars // usually used for theme
		if (empty($vars))
			$vars = array('vpsid' => $service_fields->vpsid, 'stats' => $response, 'virt' => $package->meta->type);
		
		$this->view->set("vars", (object)$vars);
		$this->view->set("client_id", $service->client_id);
		$this->view->set("service_id", $service->id);
		
		$this->view->set("view", $this->view->view);
		$this->view->setDefaultView("components" . DS . "modules" . DS . "virtualizor" . DS);
		return $this->view->fetch();
	}
	
	public function tabClientConsole($package, $service, array $get=null, array $post=null, array $files=null) {
	
		$this->view = new View("tab_client_console", "default");
		$this->view->base_uri = $this->base_uri;
		
		Loader::loadHelpers($this, array("Form", "Html"));
		
		// Get the service fields
		$service_fields = $this->serviceFieldsToObject($service->fields);
		$module_row = $this->getModuleRow($package->module_row);
		
		if($package->meta->type != 'openvz'){
		
			// For the Call
			$username = $module_row->meta->key;
			$pass = $module_row->meta->keypass;
			$ip = $module_row->meta->host;
			$path = 'index.php?act=vnc&launch=1';
			$response = $this->e_make_api_call($ip, $username, $pass, $service_fields->vpsid, $path);
			
			if(empty($response)){
				return false;
			}
		}
		
		// Set default vars // usually used for theme
		if (empty($vars))
			$vars = array('vpsid' => $service_fields->vpsid, 'vncip' => $response['info']['ip'], 'vncport' => $response['info']['port'], 'vncpassword' => $response['info']['password'], 'virt' => $package->meta->type);
		
		$this->view->set("vars", (object)$vars);
		$this->view->set("client_id", $service->client_id);
		$this->view->set("service_id", $service->id);
		
		$this->view->set("view", $this->view->view);
		$this->view->setDefaultView("components" . DS . "modules" . DS . "virtualizor" . DS);
		return $this->view->fetch();
	}
	
	
	/**
	 * Returns an array of service field to set for the service using the given input
	 *
	 * @param array $vars An array of key/value input pairs
	 * @param stdClass $package A stdClass object representing the package for the service
	 * @return array An array of key/value pairs representing service fields
	 */
	private function getFieldsFromInput(array $vars, $package) {
		
		
		$fields = array(
			'virtualizor_domain' => isset($vars['virtualizor_domain']) ? $vars['virtualizor_domain'] : null,
			'virtualizor_username' => isset($vars['virtualizor_username']) ? $vars['virtualizor_username']: null,
			'virtualizor_password' => isset($vars['virtualizor_password']) ? $vars['virtualizor_password'] : null,
			'virtualizor_confirm_password' => isset($vars['virtualizor_confirm_password']) ? $vars['virtualizor_confirm_password'] : null,
			'virtualizor_ns1' => isset($vars['virtualizor_ns1']) ? $vars['virtualizor_ns1'] : null,
			'virtualizor_ns2' => isset($vars['virtualizor_ns2']) ? $vars['virtualizor_ns2'] : null,
			'OS' => isset($vars['OS']) ? $vars['OS'] : null
		);
		
		
		
		return $fields;
	}
	
	
	/**
	 * Adds the service to the remote server. Sets Input errors on failure,
	 * preventing the service from being added.
	 *
	 * @param stdClass $package A stdClass object representing the selected package
	 * @param array $vars An array of user supplied info to satisfy the request
	 * @param stdClass $parent_package A stdClass object representing the parent service's selected package (if the current service is an addon service)
	 * @param stdClass $parent_service A stdClass object representing the parent service of the service being added (if the current service is an addon service service and parent service has already been provisioned)
	 * @param string $status The status of the service being added. These include:
	 * 	- active
	 * 	- canceled
	 * 	- pending
	 * 	- suspended
	 * @return array A numerically indexed array of meta fields to be stored for this service containing:
	 * 	- key The key for this meta field
	 * 	- value The value for this key
	 * 	- encrypted Whether or not this field should be encrypted (default 0, not encrypted)
	 * @see Module::getModule()
	 * @see Module::getModuleRow()
	 */
	public function addService($package, array $vars=null, $parent_package=null, $parent_service=null, $status="pending") {
	
		// This fetches the Server according to package selected.
		$row = $this->getModuleRow();
		
		if(!empty($package->meta->random_password)){
			$vars['virtualizor_password'] = virtualizor_generateRandPwd(12);
		}
		
		$params = $this->getFieldsFromInput((array)$vars, $package);
		
		// Get the client details 
		Loader::loadModels($this, array("Clients"));
		$clients = $this->Clients->get($vars['client_id'], false);
		
		$this->validateService($package, $vars);
		
		// Is there a virt
		if(empty($package->meta->type)){
			$this->Input->setErrors(array('api' => array('internal' => 'Virtualization Type is empty : '.$package->meta->type)));
			return;
		}

		if ($this->Input->errors())
			return;
		
		// Only provision the service if 'use_module' is true
		if ($vars['use_module'] == "true") {
			
			// Get the info from the server

			$username = $row->meta->key;
			$pass = $row->meta->keypass;
			$ip = $row->meta->host;
			$path = 'index.php?act=addvs';
			
			// Is it NEW MODULE ?
			if(!empty($package->meta->plan)){
				
				$server_group = '';
				$slave_server = '';
				
				// If user has configurable option for server group we give the priority to that so we will override the product value with configoption
				if(!empty($vars['configoptions']['server_group'])){
				    $package->meta->sg = $vars['configoptions']['server_group'].' - [G]';
				}
				
				// Is it a Server group ?
				if(preg_match('/\[G\]/s', $package->meta->sg)){
					
					$tmp_sg = array();
					$tmp_sg = explode('- [', $package->meta->sg);
					$server_group = trim_check($tmp_sg[0]);
				}
				
				// If we do not get server group we will search it for slave server
				if($server_group == ''){
					// Is user wants auto selection from server?
					if($package->meta->sg == 'Auto Select Server'){
						
						$slave_server = 'auto';
						
					// Or is it a particular Slave server ?
					}else{
						
						$tmp_ss = array();
						$tmp_ss = explode("-", (string)$package->meta->sg);
						$slave_server = trim_check($tmp_ss[0]);
					}
				}
				
				$post['server_group'] = $server_group;
				$post['slave_server'] = $slave_server;
				$post['plid'] = $package->meta->plan;
				$virttype = (preg_match('/xen/is', $package->meta->type) ? 'xen' : (preg_match('/xcp/is', $package->meta->type) ? 'xcp' : strtolower($package->meta->type)));
				
				// If its Virtuozzo
				if(preg_match('/virtuozzo/is', $virttype)){
					
					$tmp_virt = explode(' ', $virttype);
					
					if($tmp_virt[1] == 'openvz'){
						$virttype = 'vzo';
					}elseif($tmp_virt[1] == 'kvm'){
						$virttype = 'vzk';
					}
				}
				
				// If its Proxmox
				if(preg_match('/proxmox/is', $virttype)){
					
					$tmp_virt = explode(' ', $virttype);
					
					if($tmp_virt[1] == 'openvz'){
						$virttype = 'proxo';
					}elseif($tmp_virt[1] == 'kvm'){
						$virttype = 'proxk';
					}elseif($tmp_virt[1] == 'lxc'){
						$virttype = 'proxl';
					}
				}
				
				$OSlist = explode(",", $package->meta->OS);
				
				if(isset($vars['OS'])){
					$post['os_name'] = $OS = strtolower(trim_check($OSlist[$vars['OS']]));
				}
				
				$post['hostname'] = $params["virtualizor_domain"];
				$post['rootpass'] = $params["virtualizor_password"];
				// Pass the user details 
				$post['user_email'] = $clients->email;
				$post['user_pass'] = $vars['virtualizor_password'];
				
				$post['fname'] = $clients->first_name;
				$post['lname'] = $clients->last_name;
		
				$post['node_select'] = 1;
				$post['addvps'] = 1;
				$cookies = array();
				
				$masked_array = $post;
				$masked_array['password'] = '***';
				$masked_array['rootpass'] = '***';
				$masked_array['user_pass'] = '***';
				
				$ret = array();
				//Log the creation of service
				$this->log($row->meta->host . "|create service", serialize($masked_array), "input", true);
				// print_r($vars);
				// Are there any configurable options
				if(!empty($vars['configoptions'])){
					foreach($vars['configoptions'] as $k => $v){
						if(!isset($post[$k])){
							$post[$k] = $v;
						}
					}
				}
				
				// Virtualizor API call
				$ret = $this->make_api_call($ip, $username, $pass, $path.'&virt='.$virttype, array(), $post, $cookies);
				
				$this->log($row->meta->host."| ret from addvs : ", serialize($ret), "output", true);
				//print_r($ret);
				if(empty($ret['done'])){
					$create_error = implode('<br>', $ret['error']);
					$this->Input->setErrors(array('api' => array('internal' => 'Create Error :'.$create_error)));
					return;
				}
				
				// Fill the variables as per the OLD module as it will be inserted to WHMCS. Like ips, ips6, etc..
				if(!empty($ret['newvs']['ips'])){
					$_ips = $ret['newvs']['ips'];
				}
				
				if(!empty($ret['newvs']['ipv6'])){
					$_ips6 = $ret['newvs']['ipv6'];
				}
				
				if(!empty($ret['newvs']['ipv6_subnet'])){
					$_ips6_subnet = $ret['newvs']['ipv6_subnet'];
				}
				
			// Are you using OLD Module
			}else{
				
				//Log the creation of service
				$this->log($row->meta->host . "|create service(Old Module)", serialize($ip), "input", true);
				
				// Virtualizor API call
				$data = $this->make_api_call($ip, $username, $pass, $path);
				
				$this->log($module_row->meta->host . "|Virtualizor Service(Old Module)", serialize($data), "output", true);
				
				if(empty($data)){
					$this->Input->setErrors(array('api' => array('internal' => 'Could not load the server data - API CALL ERROR')));
					return;
				}
				
				// What is the virt type ?
				$virttype = (preg_match('/xen/is', $package->meta->type) ? 'xen' : (preg_match('/xcp/is', $package->meta->type) ? 'xcp' : strtolower($package->meta->type)));
				$hvm = (preg_match('/hvm/is', $package->meta->type) ? 1 : 0);
				
				// Slave server selection
				if(!empty($package->meta->server)){
					$slave_name = trim_check(strtolower($package->meta->server));
				}
				
				// Server Group Name
				if(!empty($package->meta->server_group)){
					$sg_name = trim_check(strtolower($package->meta->server_group));
				}

				$cookies = array();
				
				// Is there a Slave server ?
				if(!empty($slave_name) && $slave_name != 'localhost'){
					
					// Do we have to Auto Select
					if($slave_name == 'auto'){
						
						foreach($data['servers'] as $k => $v){
							
							// Master servers cannot be here
							if(empty($k)) continue;
							
							// Only the Same type of Virtualization is supported
							if($virttype != $v['virt']){
								continue;
							}
							
							// Xen HVM additional check
							if(!empty($hvm) && empty($v['hvm'])){
								continue;
							}
							
							$tmpsort[$k] = $v['numvps'];
							
						}
						
						// Did we get a list of Slave Servers
						if(empty($tmpsort)){
							$this->Input->setErrors(array('api' => array('internal' => 'No server present in the Cluster which is of the Virtualization Type : '.$package->meta->type)));
							return;
						}
						
						asort($tmpsort);
						
						$newserid = key($tmpsort);
						
					}else{
					
						foreach($data['servers'] as $k => $v){
							if(trim_check(strtolower($v['server_name'])) == $slave_name){
								$newserid = $k;
							}
						}
					
					}
					
					// Is there a valid slave server ?
					if(empty($newserid)){
						$this->Input->setErrors(array('api' => array('internal' => 'There is no slave server - '.$slave_name.'. Please correct the <b>Product / Service</b> with the right slave server name.')));
						return;
					}
					
				// Is there a Server Group ?
				}elseif(!empty($sg_name)){
					
					foreach($data['servergroups'] as $k => $v){
						
						// Match the Server Group
						if(trim_check(strtolower($v['sg_name'])) == $sg_name){					
							$sgid = $k;					
						}
						
					}
				
					if(!isset($sgid)){
						$this->Input->setErrors(array('api' => array('internal' => 'Could not find the server group - '.$sg_name.'. Please correct the <b>Product / Service</b> with the right slave server name.')));
						return;
					}
					
					// Make an array of available servers in this group
					foreach($data['servers'] as $k => $v){
						
						// Do you belong to this group
						if($v['sgid'] != $sgid){
							continue;
						}
						
						// Only the Same type of Virtualization is supported
						if($virttype != $v['virt']){
							continue;
						}
						
						// Xen HVM additional check
						if(!empty($hvm) && empty($v['hvm'])){
							continue;
						}
						
						$tmpsort[$k] = $v['numvps'];
						
					}
					
					asort($tmpsort);
					
					// Is there a valid slave server ?
					if(empty($tmpsort)){
						$this->Input->setErrors(array('api' => array('No server present in the Server Group which is of the Virtualization Type : '.$slave_name.'. Please correct the <b>Product / Service</b> with the right slave server name.')));
						return;
					}
					
					$newserid = key($tmpsort);
				
				}
				
				// If a new server ID was found. Even if its 0 (Zero) then there is no need to reload data as the DATA is by default of 0
				if(!empty($newserid)){
					
					$cookies[$data['globals']['cookie_name'].'_server'] = $newserid;
					
					// Virtualizor API call
					$data = $this->make_api_call($ip, $username, $pass, $path, array(), array(), $cookies);
					
					if(empty($data)){
						$this->Input->setErrors(array('api' => array('internal' => 'Could not load the slave server data')));
						return;
					}
				}
				
				// Now prepare the Post array
				$post = array();
				
				// Search the user, create a user if not present
				foreach($data['users'] as $v => $k){
					if($k['email'] == $clients->email){
						$post['uid'] = $k['uid'];
					}
				}
				
				// Was the user there ?
				if(empty($post['uid'])){
					$post['user_email'] = $clients->email;
					$post['user_pass'] = $vars['virtualizor_password'];
				}
				
				// OS list set by the blesta admin package
				$OSlist = explode(",", $package->meta->OS);
				
				foreach($data['oslist'][$virttype] as $k => $v){
					foreach($v as $kk => $vv){
					
						// Xen Stuff!
						if($virttype == 'xen' || $virttype == 'xcp'){
						
							// Xen HVM templates
							if(!empty($hvm) && empty($vv['hvm'])){
								continue;
								
							// Xen PV templates
							}elseif(empty($hvm) && !empty($vv['hvm'])){
								continue;
							}
						}
						
						// Does the String match ?
						if(strtolower($vv['name']) == strtolower(trim_check($OSlist[$vars['OS']]))){
							$post['osid'] = $kk;
						}
					}
				}
				
				// No Email check 
				$post['noemail'] = 1;
				
				$numips = $package->meta->ips;
				
				if($numips > 0){
					// Assign the IPs
					foreach($data['ips'] as $k => $v){
						$i = $numips;
						$_ips[] = $v['ip'];
						
						if($i == count($_ips)){
							break;
						}
					}
					
					// Were there enough IPs
					if(empty($_ips) || count($_ips) < $numips){
						$this->Input->setErrors(array('api' => array('internal' => 'There are insufficient IPs on the server')));
					}
				}
				
				$numips6 = $package->meta->ips6;
				
				// Are there any IPv6 to assign ?
				if($numips6 > 0){
					
					$_ips6 = array();
					
					// Assign the IPs
					foreach($data['ips6'] as $k => $v){
						
						if($numips6 == count($_ips6)){
							break;
						}
						
						$_ips6[] = $v['ip'];
					}
					
					// Were there enough IPs
					if(empty($_ips6) || count($_ips6) < $numips6){
						$this->Input->setErrors(array('api' => array('internal' => 'There are insufficient IPv6 Addresses on the server')));
					}
				
				}
				
				$numips6_subnet = $package->meta->ips6_subnet;
				
				// Are there any IPv6 Subnets to assign ?
				if($numips6_subnet > 0){
					
					$_ips6_subnet = array();
					
					// Assign the IPs
					foreach($data['ips6_subnet'] as $k => $v){
						
						if($numips6_subnet == count($_ips6_subnet)){
							break;
						}
						
						$_ips6_subnet[] = $v['ip'];
					}
					
					// Were there enough IPs
					if(empty($_ips6_subnet) || count($_ips6_subnet) < $numips6_subnet){
						$this->Input->setErrors(array('api' => array('internal' => 'There are insufficient IPv6 Subnets on the server')));
					}
				}
				
				// Add the IPs
				if(!empty($_ips)){
					$post['ips'] = $_ips;
				}
				
				// Add the IPv6
				if(!empty($_ips6)){
					$post['ipv6'] = $_ips6;
				}
				
				// Add the IPv6 Subnet
				if(!empty($_ips6_subnet)){
					$post['ipv6_subnet'] = $_ips6_subnet;
				}
				
				// Other Details as well
				$post['hostname'] = $params["virtualizor_domain"];
				$post['rootpass'] = $params["virtualizor_password"];
				$post['space'] = (empty($package->meta->hdd) ? 0 : $package->meta->hdd);
				$post['ram'] = (empty($package->meta->ram) ? 0 : $package->meta->ram);
				$post['bandwidth'] = (empty($package->meta->bandwidth) ? 0 : $package->meta->bandwidth);
				$post['cores'] = (empty($package->meta->cores) ? 0 : $package->meta->cores);
				$post['network_speed'] = (empty($package->meta->network_speed) ? 0 : $package->meta->network_speed);
				$post['cpu_percent'] = (empty($package->meta->cpu_percent) ? 0 : $package->meta->cpu_percent);
				$post['cpu'] = (empty($package->meta->cpu) ? 0 : $package->meta->cpu);
				$post['virtio'] = (empty($package->meta->virtio) ? 0 : $package->meta->virtio);
				$post['addvps'] = 1;
				
				// Is is OpenVZ
				if($virttype == 'openvz'){
				
					$post['inodes'] = (empty($package->meta->inodes) ? 0 : $package->meta->inodes);
					$post['burst'] = (empty($package->meta->burst) ? 0 : $package->meta->burst);
					$post['priority'] = (empty($package->meta->priority) ? 0 : $package->meta->priority);
					
				// Is it Xen/XCP PV?
				}elseif(($virttype == 'xen' || $virttype == 'xcp') && empty($hvm)){
					$post['swapram'] = (empty($package->meta->swapram) ? 0 : $package->meta->swapram);
					
				// Is it Xen/XCP HVM?
				}elseif(($virttype == 'xen' || $virttype == 'xcp') && !empty($hvm)){
					$post['hvm'] = 1;
					$post['shadow'] = 8;
					$post['swapram'] = (empty($package->meta->swapram) ? 0 : $package->meta->swapram);
					
				// Is it KVM ?
				}elseif($virttype == 'kvm'){
					$post['swapram'] = (empty($package->meta->swapram) ? 0 : $package->meta->swapram);
				}
				
				// Set the VNC
				if($virttype != 'openvz'){
				
					if($package->meta->vnc == 'yes' || $package->meta->vnc == true){
						$post['vnc'] = 1;
						$post['vncpass'] = $this->generateRandStr(8);
					}
				}
					
				// Return from here if any error found
				if ($this->Input->errors())
					return;

				// Are there any configurable options
				if(!empty($vars['configoptions'])){
					foreach($vars['configoptions'] as $k => $v){
						if(!isset($post[$k])){
							$post[$k] = $v;
						}
					}
				}
				
				// Get the info from the server
				$pass = $row->meta->keypass;
				$ip = $row->meta->host;
				$path = 'index.php?act=addvs';
				
				// Virtualizor API call
				$ret = $this->make_api_call($ip, $username, $pass, $path, array(), $post, $cookies);
				
				if(empty($ret['done'])){
					$create_error = implode('<br>', $ret['error']);
					$this->Input->setErrors(array('api' => array('internal' => 'Create Error :'.$create_error)));
					return;
				}
			
			}// End of else (OLD Module)
		}// End of if(use_module)
		
		$tmp_ips = empty($_ips) ? array() : $_ips;
		
		if(!empty($_ips6_subnet)){
			foreach($_ips6_subnet as $k => $v){
				$tmp_ips[] = $v;
			}
		}
		
		if(!empty($_ips6)){
			foreach($_ips6 as $k => $v){
				$tmp_ips[] = $v;
			}
		}
		
		if(!empty($tmp_ips[0])){
			
			$primary_ip = $tmp_ips[0];
			
			// Extra IPs
			unset($tmp_ips[0]);
		}

		$a = array(
			array(
				'key' => "virtualizor_domain",
				'value' => $vars['virtualizor_domain'],
				'encrypted' => 0
			),
			array(
				'key' => "virtualizor_username",
				'value' => $clients->email,
				'encrypted' => 0
			),
			array(
				'key' => "virtualizor_password",
				'value' => $vars['virtualizor_password'],
				'encrypted' => 1
			),
			array(
				'key' => "virtualizor_rootpass",
				'value' => $vars['virtualizor_password'],
				'encrypted' => 1
			),
			array(
				'key' => "virtualizor_ip",
				'value' => @$primary_ip,
				'encrypted' => 0
			),
			array(
				'key' => "virtualizor_additional_ips",
				'value' => (!empty($tmp_ips) ? implode(', ', $tmp_ips) : null),
				'encrypted' => 0
			),
			array(
				'key' => "virtualizor_ns1",
				'value' => @$vars['virtualizor_ns1'],
				'encrypted' => 0
			),
			array(
				'key' => "virtualizor_ns2",
				'value' => @$vars['virtualizor_ns2'],
				'encrypted' => 0
			),
			array(
				'key' => "OS",
				'value' => $vars['OS'],
				'encrypted' => 0
			),
			array(
				'key' => "vpsid",
				'value' => (isset($ret['newvs']['vpsid']) ? $ret['newvs']['vpsid'] : null),
				'encrypted' => 0
			)
		);
		$this->log($row->meta->host . "|return values ", json_encode($a), "input", true);
		// $this->Input->setErrors(array('api' => array('internal' => 'Create Error :'.json_encode($a))));
		// 			return;
		// Return service fields
		return $a;
	}
	
	//Edit the Service
	public function editService($package, $service, array $vars= null, $parent_package=null, $parent_service=null) {
		
		$module_row = $this->getModuleRow($package->module_row);
		$encrypted_fields = array("virtualizor_rootpass", "virtualizor_password");
		
		// Get the service fields
		$service_fields = $this->serviceFieldsToObject($service->fields);
		
		$params = $this->getFieldsFromInput((array)$vars, $package);
				
		// If the service was created wihtout using the module, we wonr get vpsid.
		// If user wants to add it afterward we will get the vpsid from $_POST
		if(empty($service_fields->vpsid) && !empty($_POST['vpsid'])){
			$service_fields->vpsid = $_POST['vpsid'];
		}
		
		// Get the client details 
		Loader::loadModels($this, array("Clients"));
		$clients = $this->Clients->get((array)$service->client_id, false);
		
		$username = $module_row->meta->key;
		$pass = $module_row->meta->keypass;
		$ip = $module_row->meta->host;
		$path = 'index.php?act=editvs&vpsid='.$service_fields->vpsid;
		
		$data = $this->make_api_call($ip, $username, $pass, $path);
		
		if(!empty($data['vps']['uid'])){
			$uid = $data['vps']['uid'];
		}
		
		if(empty($data)){
			return 'Could not load the server data.'.$module_row->meta->host;
		}
		
		// Fetch the current service plan
		$plan = '';
		$type = (isset($package->meta->type) ? $package->meta->type : "");
		
		if (isset($package->meta->plan)) {
			$plan = $package->meta->plan;
		}
		
		if(!empty($service_fields->vpsid)){
			$service_fields->vpsid = $vars['vpsid'];
		}
		
		$post_vps = array();
		$post_vps = $data['vps'];
		
		if ($vars['use_module'] == "true") {

			// Is it NEW MODULE ?
			if(!empty($plan)){	
				$post_vps = array();
				$this->validateService($package, $vars);
				$post_vps['plid'] = $plan;		
			}

			// Since validating the service rules does not update data in pre/post formatting,
			// re-apply the formatting changes manually
			if (isset($vars['name'])) {
				$vars['name'] = $this->replaceText($vars['name'], "", "/^\s*www\./i");
			}

			// Check for fields that changed
			$edit_fields = array();
			foreach($vars as $key=>$value){
				
				if(isset($service_fields->$key) || $vars[$key] !=$service_fields->$key){
					$edit_fields[$key] = $value;
				}
			}
				
			//Update Hostname
			if (isset($edit_fields['virtualizor_domain'])) {
				$post_vps['hostname'] = $edit_fields['virtualizor_domain'];
				$service_fields->virtualizor_domain = $edit_fields['virtualizor_domain'];
			}
			
			// Update root password (if changed)
			if (isset($edit_fields['virtualizor_password'])) {				
				$post_vps['rootpass'] = $edit_fields['virtualizor_password'];
				$service_fields->virtualizor_rootpass = $edit_fields['virtualizor_password'];
				$service_fields->virtualizor_password = $edit_fields['virtualizor_password'];
			}
			
			// Set virtual server ID if changed
			$vpsid = (array)$service_fields->vpsid;
			
			if(!empty($service_fields->vpsid)){
				$service_fields->vpsid = $vars['vpsid'];
				$post_vps['vpsid'] = $vars['vpsid'];
			}
			$post_vps['uid'] = $uid;
			$post_vps['user_email'] = $service_fields->virtualizor_username;
			$post_vps['editvps'] = 1;

			// Are there any configurable options
			if(!empty($vars['configoptions'])){
				foreach($vars['configoptions'] as $k => $v){
					if(!isset($post_vps[$k])){
						$post_vps[$k] = $v;
					}
				}
			}
			
			// Virtualizor API call
			$ret = $this->make_api_call($ip, $username, $pass, $path, array(), $post_vps);
			//$this->log($module_row->meta->host . "Edit error", $ret, "output", true);
			
			
			//$this->log($row->meta->host . "|check error", serialize($ret), "input", true);
			
			if(empty($ret['done'])){							
				$edit_error = implode('<br>', array($ret['error']));
			 	$this->Input->setErrors(array('api' => array('internal' => 'Edit Error :'.$edit_error)));
				// $this->log($module_row->meta->host . "|check error", serialize($abc), "output", true);
				return;
			}

			$tmp_ips = array();
	
			if(!empty($ret['vps']['ips'])){
				foreach($ret['vps']['ips'] as $k => $v){
					$tmp_ips[] = $v;
				}
			}
			
			if(!empty($ret['vps']['ips6_subnet'])){
				foreach($ret['vps']['ips6_subnet'] as $k => $v){
					$tmp_ips[] = $v;
				}
			}
			
			if(!empty($ret['vps']['ips6'])){
				foreach($ret['vps']['ips6'] as $k => $v){
					$tmp_ips[] = $v;
				}
			}
			
			if(!empty($ret['vps']['ips_int'])){
				foreach($ret['vps']['ips_int'] as $k => $v){
					$tmp_ips[] = $v;
				}
			}

			if(!empty($tmp_ips)){

				$service_fields->virtualizor_ip = $tmp_ips[0];
				unset($tmp_ips[0]);
				$service_fields->virtualizor_additional_ips = !empty($tmp_ips) ? implode(', ', $tmp_ips) : null;

			}

		}
	
		$fields = array();
	
		// Return all the service fields
		foreach ($service_fields as $key => $value){
			$fields[] = array('key' => $key,
							'value' => $value,
							'encrypted' => (in_array($key, $encrypted_fields) ? 1 : 0));
		}

		return $fields;
	}
	
	// Cancel the Service
	public function cancelService($package, $service, $parent_package=null, $parent_service=null) {
		
		$service_fields = $this->serviceFieldsToObject($service->fields);
		
		// Will get the server key and pass
		$module_row = $this->getModuleRow($package->module_row);
		
		$username = $module_row->meta->key;
		$pass = $module_row->meta->keypass;
		$ip = $module_row->meta->host;
		$path = 'index.php?act=vs&delete='.$service_fields->vpsid;
		
		$params = array('vpsid' => $service_fields->vpsid);
		
		$this->log($row->meta->host . "|terminate vps", serialize($params), "input", true);
		
		$virt_resp = $this->make_api_call($ip, $username, $pass, $path);
		
		$this->log($module_row->meta->host . "|Vps Terminated", serialize($virt_resp), "output", true);
		
		if(empty($virt_resp)){
			$this->Input->setErrors(array('api' => array('internal' => 'Unable to cancel the service')));
		}
		
		return null;
	}
	
	// Cancel the Service
	public function suspendService($package, $service, $parent_package=null, $parent_service=null) {
		
		$service_fields = $this->serviceFieldsToObject($service->fields);
		
		// Will get the server key and pass
		$module_row = $this->getModuleRow($package->module_row);
		
		$username = $module_row->meta->key;
		$pass = $module_row->meta->keypass;
		$ip = $module_row->meta->host;
		$path = 'index.php?act=vs&suspend='.$service_fields->vpsid;
		
		$params = array('vpsid' => $service_fields->vpsid);
		
		$this->log($row->meta->host . "|suspend vps", serialize($params), "input", true);
		
		$virt_resp = $this->make_api_call($ip, $username, $pass, $path);
		
		$this->log($row->meta->host . "|VPS Suspended", serialize($virt_resp), "output", true);
		
		if(empty($virt_resp)){
			$this->Input->setErrors(array('api' => array('internal' => 'Unable to cancel the service')));
		}
		
		return null;
	}
	
	// Cancel the Service
	public function unsuspendService($package, $service, $parent_package=null, $parent_service=null) {
		
		$service_fields = $this->serviceFieldsToObject($service->fields);
		
		// Will get the server key and pass
		$module_row = $this->getModuleRow($package->module_row);
		
		$username = $module_row->meta->key;
		$pass = $module_row->meta->keypass;
		$ip = $module_row->meta->host;
		$path = 'index.php?act=vs&unsuspend='.$service_fields->vpsid;
		
		$params = array('vpsid' => $service_fields->vpsid);
		
		$this->log($row->meta->host . "|Unsuspend Service/VPS", serialize($params), "input", true);
		
		$virt_resp = $this->make_api_call($ip, $username, $pass, $path);
		
		$this->log($row->meta->host . "|VPS Unsuspension Successful", serialize($virt_resp), "output", true);
		
		if(empty($virt_resp)){
			$this->Input->setErrors(array('api' => array('internal' => 'Unable to cancel the service')));
		}
		
		return null;
	}
	
	
	/**
	 * Adds the module row on the remote server. Sets Input errors on failure,
	 * preventing the row from being added.
	 *
	 * @param array $vars An array of module info to add
	 * @return array A numerically indexed array of meta fields for the module row containing:
	 * 	- key The key for this meta field
	 * 	- value The value for this key
	 * 	- encrypted Whether or not this field should be encrypted (default 0, not encrypted)
	 */
	public function addModuleRow(array &$vars) {
		$meta_fields = array("server_name", "key", "keypass", "host");
		$encrypted_fields = array("key", "keypass");
		
		$this->Input->setRules($this->getRowRules($vars));
		
		// Validate module row
		if ($this->Input->validates($vars)) {

			// Build the meta data for this row
			$meta = array();
			foreach ($vars as $key => $value) {
				
				if (in_array($key, $meta_fields)) {
					$meta[] = array(
						'key' => $key,
						'value' => $value,
						'encrypted' => (in_array($key, $encrypted_fields) ? 1 : 0)
					);
				}
			}
			
			return $meta;
		}
	}

	public function editModuleRow($module_row, array &$vars) {

		$meta_fields = array("server_name", "key", "keypass", "host");
		$encrypted_fields = array("key", "keypass");
		
		$this->Input->setRules($this->getRowRules($vars));
		
		// Validate module row
		if ($this->Input->validates($vars)) {

			// Build the meta data for this row
			$meta = array();
			foreach ($vars as $key => $value) {
				
				if (in_array($key, $meta_fields)) {
					$meta[] = array(
						'key' => $key,
						'value' => $value,
						'encrypted' => (in_array($key, $encrypted_fields) ? 1 : 0)
					);
				}
			}
			
			return $meta;
		}
	}

	/**
	 * Generates a username from the given host name
	 *
	 * @param string $host_name The host name to use to generate the username
	 * @return string The username generated from the given hostname
	 */
	private function generateUsername($host_name) {
		// Remove everything except letters and numbers from the domain
		// ensure no number appears in the beginning
		$username = ltrim(preg_replace('/[^a-z0-9]/i', '', $host_name), '0123456789');

		$length = strlen($username);
		$pool = "abcdefghijklmnopqrstuvwxyz0123456789";
		$pool_size = strlen($pool);
		
		if ($length < 5) {
			for ($i=$length; $i<8; $i++) {
				$username .= substr($pool, mt_rand(0, $pool_size-1), 1);
			}
			$length = strlen($username);
		}
		
		return substr($username, 0, min($length, 8));
	}
	
	/**
	 * Retrieves a list of rules for validating adding/editing a module row
	 *
	 * @param array $vars A list of input vars
	 * @return array A list of rules
	 */
	private function getRowRules(array &$vars) {
		$meta_fields = array("server_name", "key", "keypass", "host");
		$encrypted_fields = array("key", "keypass");
		return array(
			'server_name' => array(
				'empty' => array(
					'rule' => "isEmpty",
					'negate' => true,
					'message' => Language::_("virtualizor.!error.server_name.empty", true)
				)
			),
			'key' => array(
				'empty' => array(
					'rule' => "isEmpty",
					'negate' => true,
					'message' => Language::_("virtualizor.!error.key.empty", true)
				)
			),
			'keypass' => array(
				'empty' => array(
					'rule' => "isEmpty",
					'negate' => true,
					'message' => Language::_("virtualizor.!error.keypass.empty", true)
				)
			),
			'host' => array(
				'format' => array(
					'rule' => "isEmpty",
					'negate' => true,
					'message' => Language::_("virtualizor.!error.host.format", true)
				)
			)
		);
	}
	
	
	
	/**
	 * Fetches the templates available for the Virtualizor server of the given type
	 *
	 * @param string $type The type of server (i.e. openvz, xen, xen hvm, kvm)
	 * @param stdClass $module_row A stdClass object representing a single server
	 * @return array A list of templates
	 */
	private function getTemplates($package) {
	
		// Get the List
		$oslist = $package->meta->OS;
		
		// Just break it
		$_os = explode(',', $oslist);
		
		return $_os;
	}
	
	/**
	 * Attempts to validate service info. This is the top-level error checking method. Sets Input errors on failure.
	 *
	 * @param stdClass $package A stdClass object representing the selected package
	 * @param array $vars An array of user supplied info to satisfy the request
	 * @return boolean True if the service validates, false otherwise. Sets Input errors when false.
	 */
	public function validateService($package, array $vars=null, $edit=false) {
		// Set rules
		$rules = array(
			'virtualizor_domain' => array(
				'format' => array(
					'rule' => array(array($this, "validateHostName")),
					'message' => 'Hostname Not Valid'
				)
			)
		);
		
		$this->Input->setRules($rules);
		return $this->Input->validates($vars);
	}
	
	// Fetch Virtualizor Templates available for a VPS
	public function virtTemplates($module_row, $vpsid) {
	
		// Get the info from the server
		$username = $module_row->meta->key;
		$pass = $module_row->meta->keypass;
		$ip = $module_row->meta->host;
		$path = 'index.php?act=ostemplate&';
		
		$params = array('ip' => $ip, 'listpipefriendly' => "true");

		$this->log($module_row->meta->host . "|listtemplates", serialize($params), "input", true);
		
		$virt_resp = $this->e_make_api_call($ip, $username, $pass, $vpsid, $path);
		
		$this->log($module_row->meta->host . "|Templates", serialize($virt_resp), "output", true);
		
		return $virt_resp['oslist'];
	}
	
	/**
	 * Validates that the given hostname is valid
	 *
	 * @param string $host_name The host name to validate
	 * @return boolean True if the hostname is valid, false otherwise
	 */
	public function validateHostName($host_name) {
		if (strlen($host_name) > 255)
			return false;
		
		return $this->Input->matches($host_name, "/^([a-z0-9]|[a-z0-9][a-z0-9\-]{0,61}[a-z0-9])(\.([a-z0-9]|[a-z0-9][a-z0-9\-]{0,61}[a-z0-9]))+$/");
	
		
	}
	
	// Error function
	public function error($ip = ''){
		
		$err = '';
		
		if(!empty($GLOBALS['virt_curl_err'])){
			$err .= ' Curl Error: '.$GLOBALS['virt_curl_err'];
		}
		
		if(!empty($ip)){
			$err .= ' (Server IP : '.$ip.')';
		}
		
		return $err;
	}
	
	public function make_api_call($ip, $username, $pass, $path, $data = array(), $post = array(), $cookies = array()){
		
		$key = $this->generateRandStr(8);
		$apikey = $this->make_apikey($key, $pass);
		$url = 'https://'.$ip.':4085/'.$path;	
		$url .= (strstr($url, '?') ? '' : '?');
		$url .= '&adminapikey='.rawurlencode($username).'&adminapipass='.rawurlencode($pass);
		$url .= '&api=serialize&apikey='.rawurlencode($apikey).'&skip_callback=blesta';
		
		// Pass some data if there
		if(!empty($data)){
			$url .= '&apidata='.rawurlencode(base64_encode(serialize($data)));
		}
		
		// Set the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
			
		// Time OUT
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
		
		// Turn off the server and peer verification (TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			
		// UserAgent
		curl_setopt($ch, CURLOPT_USERAGENT, 'Softaculous');
		
		// Cookies
		if(!empty($cookies)){
			curl_setopt($ch, CURLOPT_COOKIESESSION, true);
			curl_setopt($ch, CURLOPT_COOKIE, http_build_query($cookies, '', '; '));
		}
		
		if(!empty($post)){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
		}
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		// Get response from the server.
		$resp = curl_exec($ch);
		
		if(empty($resp)){
			$GLOBALS['virt_curl_err'] = curl_error($ch);
		}
			
		curl_close($ch);
		
		if(empty($resp)){
			return false;
		}
		
		// As a security prevention measure - Though this cannot happen
		$resp = str_replace($pass, '12345678901234567890123456789012', $resp);
		
		$r = $this->_unserialize($resp);
		
		if(empty($r)){
			return false;
		}
		
		return $r;
	}	

	public function e_make_api_call($ip, $username, $pass, $vid, $path, $post = array()){
		
		$key = $this->generateRandStr(8);
		$apikey = $this->make_apikey($key, $pass);
		
		$url = 'https://'.$ip.':4083/'.$path;	
		$url .= (strstr($url, '?') ? '' : '?');	
		$url .= '&adminapikey='.rawurlencode($username);
		$url .= '&svs='.$vid.'&api=serialize&apikey='.rawurlencode($apikey).'&skip_callback=blesta';
		
		// Set the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
			
		// Time OUT
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		
		// Turn off the server and peer verification (TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			
		// UserAgent and Cookies
		curl_setopt($ch, CURLOPT_USERAGENT, 'Softaculous');
		
		if(!empty($post)){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
		}
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		// Get response from the server.
		$resp = curl_exec($ch);
		curl_close($ch);
		
		if(empty($resp)){
			return false;
		}
		
		// As a security prevention measure - Though this cannot happen
		$resp = str_replace($pass, '12345678901234567890123456789012', $resp);
		
		$r = $this->_unserialize($resp);
		
		if(empty($r)){
			return false;
		}
		
		return $r;
	}	
	
	public function make_apikey($key, $pass){
		return $key.md5($pass.$key);
	}

	function _unserialize($str){

		$var = @unserialize($str);
		
		if(empty($var)){
			
			preg_match_all('!s:(\d+):"(.*?)";!s', $str, $matches);
			foreach($matches[2] as $mk => $mv){
				$tmp_str = 's:'.strlen($mv).':"'.$mv.'";';
				$str = str_replace($matches[0][$mk], $tmp_str, $str);
			}
			$var = @unserialize($str);
		
		}
		
		//If it is still empty false
		if(empty($var)){
		
			return false;
		
		}else{
		
			return $var;
		
		}
	
	}

	//generates random strings
	public function generateRandStr($length){	
		$randstr = "";	
		for($i = 0; $i < $length; $i++){	
			$randnum = mt_rand(0,61);		
			if($randnum < 10){		
				$randstr .= chr($randnum+48);			
			}elseif($randnum < 36){		
				$randstr .= chr($randnum+55);			
			}else{		
				$randstr .= chr($randnum+61);			
			}		
		}	
		return strtolower($randstr);	
	}
	
	public function r_print($data){
	
		echo '<pre>';
		print_r($data);
		echo '</pre>';
	}
	
	public function getdecryptpass($enc_pass){
		
		$dec_path = dirname(dirname(dirname(dirname(__FILE__)))).'/app/app_model.php';
		
		include_once($dec_path);
		
		$obj = new AppModel();
		
		return $obj->systemDecrypt($enc_pass);
		
	}
}

?>