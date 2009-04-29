<?php
/**
 * Plugin Name: One Sport Event
 * Plugin URI: http://www.onesportevent.com
 * Description: WordPress Plugin to display Sport events from OneSportEvent.com using their API.
 * Author: sporty - sport@onesportevent.com
 * Author URI: www.OneSportEvent.com/AboutUs.aspx
 * Version: 1.0.2
 * 
 * CHANGELOG
 * 1.0.2 - ADDED - Detailed documentation - readme.doc
 *         FIXED - Minor fix to the API call
 * 1.0.1 - ADDED - Screenshot of admin page
 * 1.0   - INFO  - Added to WordPress Plugin repository
 * 0.3   - ADDED - parameter clubID and additional parameters string
 * 0.2   - ADDED - parameters oseAreaLevel and oseAreaID
 * 0.1   - Initial version
 */

/**
* prevent file from being accessed directly
*/
if ('onesportevent.php' == basename($_SERVER['SCRIPT_FILENAME'])) {
	die ('Please do not access this file directly. Thanks!');
}

/**
* Load Includes files
*/
if(!defined('TAMIL_ONESPORTEVENT_PATH')) {
	define('TAMIL_ONESPORTEVENT_PATH', dirname(__FILE__) . "/");
}

if( !class_exists('OneSportEvent') )
{
	class OneSportEvent {
		var $error;
		var $message;

		function __construct($post_id = '') { //constructor

			// Plugin Activation
			add_action('activate_onesportevent/onesportevent.php', array(&$this, 'install'));

			// Add header includes
			add_action('init', array($this, 'init'));
			add_action('wp_head', array($this, 'wp_head'));

			// Add the admin menu
			add_action('admin_menu', array($this, 'onesportevent_menu'));
			$this->error = '';
			$this->message = '';
		}

		function __destruct() {
			// Nothing to do...
		}

		function install() {
			$settings = array();
			$settings['includeCrumb'] = 1;
			$settings['includeAreas'] = 1;
			$this->saveSettings($settings);
		}

		function init() {
			wp_enqueue_script('jquery');
		}

		function wp_head() {
			if (is_feed()) {
				return;
			}
			echo "<!-- OneSportEvent Plugin - stylesheet and API link. -->\n";
			$settings = array();
			$this->getSettings($settings);
			if($settings['stylesheet'] != '') {
				echo "<link rel='stylesheet' href='" . $settings['stylesheet'] . "' type='text/css' media='screen' />\n";
			}

			$optional_parameters_boolean = array('activityPanel','eventPanel','stylePanel','datePanel','searchPanel','includeCrumb','includeAreas');
			$optional_parameters_integer = array('pageNo','perPage');
			$optional_parameters = array('oseActivities','oseEvents','oseStyles','afterDate','keyWord', 'clubID');

			$optional_string = '';

			foreach($optional_parameters_boolean as $param) {
				$optional_string .= $param . (($settings[$param]) ? " : true" : " : false") . ", ";
			}
			foreach($optional_parameters_integer as $param) {
				if($settings[$param] != '') {
					$optional_string .= $param . " : " . $settings[$param] . ", ";
				}
			}
			foreach($optional_parameters as $param) {
				if($settings[$param] != '') {
					$optional_string .= $param . " : '" . $settings[$param] . "', ";
				}
			}
			if($settings['oseAreaLevel'] != '') {
				$optional_string .= "oseAreaLevel : '" . $settings['oseAreaLevel'] . "', oseAreaID : ". $settings['oseAreaID'];
			} else {
				$optional_string = substr($optional_string, 0, -2);
			}
			if($settings['extraParam'] != '') {
				$optional_string .= ", " . $settings['extraParam'];
			}

			echo "<script type='text/javascript' src='".$settings['path']."?WebKey=".$settings['WebKey']."'></script>\n";
			echo "<script type='text/javascript'>\n";
			echo "jQuery(document).ready(function($){";
			echo '  oseInitEvents({'.$optional_string.'});// do stuff'."\n";
			echo "});";

			echo "</script>\n";
			echo "<!-- /OneSportEvent Plugin - stylesheet and API link. -->\n";
		}

		function onesportevent_menu() {

			add_submenu_page('options-general.php', 'One Sport Event' ,'One Sport Event', '10', __FILE__, array($this, 'manageSettings'));
		}

		function getSettings(&$settings) {
			$settings = unserialize(get_option('onesportevent_settings'));
			// Default Values
			if($settings['path'] == '') {
				$settings['path'] = 'http://onesportevent.com/API/v2/EventAPI.aspx';
			}
			if($settings['stylesheet'] == '') {
				$settings['stylesheet'] = 'http://onesportevent.com/API/v2/Styles/oseDefault.css';
			}

			return;
		}

		function saveSettings(&$settings) {
			update_option('onesportevent_settings', serialize($settings));
			return;
		}

		function getAreaLevelList($selected) {
			$list = Array('Auto' => '', 'Country' => 'countryID', 'Region' => 'regionID', 'City' => 'cityID');
			$output = '<select name="oseAreaLevel">'."\n";
			foreach($list as $key => $item ) {
				$output .= '<option value="'.$item.'"';
				$output .= ($selected == $item) ? ' selected' : '';
				$output .= '>'.$key.'</option>'."\n";
			}
			$output .= '</select>';
			return $output;
		}

		function manageSettings() {

			// Variables
			$base_name = plugin_basename('onesportevent/onesportevent.php');
			$base_page = 'admin.php?page='.$base_name;

			// Form Processing
			if(!empty($_POST['save'])) {
				$settings = array();
				$settings['WebKey'] = $_POST['WebKey'];
				$settings['stylesheet'] = $_POST['stylesheet'];
				$settings['path'] = $_POST['path'];
				$settings['activityPanel'] = ($_POST['activityPanel'] == 'on') ? 1 : 0;
				$settings['eventPanel'] = ($_POST['eventPanel'] == 'on') ? 1 : 0;
				$settings['stylePanel'] = ($_POST['stylePanel'] == 'on') ? 1 : 0;
				$settings['datePanel'] = ($_POST['datePanel'] == 'on') ? 1 : 0;
				$settings['searchPanel'] = ($_POST['searchPanel'] == 'on') ? 1 : 0;
				$settings['oseActivities'] = $_POST['oseActivities'];
				$settings['oseEvents'] = $_POST['oseEvents'];
				$settings['oseStyles'] = $_POST['oseStyles'];
				$settings['oseAreaLevel'] = $_POST['oseAreaLevel'];
				$settings['oseAreaID'] = $_POST['oseAreaID'];
				$settings['clubID'] = $_POST['clubID'];
				$settings['afterDate'] = $_POST['afterDate'];
				$settings['pageNo'] = $_POST['pageNo'];
				$settings['includeCrumb'] = ($_POST['includeCrumb'] == 'on') ? 1 : 0;
				$settings['includeAreas'] = ($_POST['includeAreas'] == 'on') ? 1 : 0;
				$settings['perPage'] = $_POST['perPage'];
				$settings['keyWord'] = $_POST['keyWord'];
				$settings['extraParam'] = $_POST['extraParam'];
				$this->saveSettings($settings);
				$this->message = 'Settings saved successfully...';
			}

			$settings = array();
			$this->getSettings($settings);

			if(!empty($this->message)) { echo '<!-- Last Message --><div id="message" class="updated fade"><p style="color:green;">'.stripslashes($this->message).'</p></div>'; } else { echo '<div id="message" class="updated" style="display: none;"></div>'; }
?>
			<form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post">
				<div class="wrap">
					<h2>One Sport Event - WebKey</h2>
					<br class="clear" />
					<table class="form-table">
						<tr>
							<th width="20%" scope="row" valign="top">Your API WebKey</th>
							<td width="80%"><input name="WebKey" type="text" value="<?php echo $settings['WebKey'];?>"  size="60"/></td>
						</tr>
						<tr>
							<th width="20%" scope="row" valign="top">OneSportEvent API Url</th>
							<td width="80%"><input name="path" type="text" value="<?php echo $settings['path'];?>"  size="60"/></td>
						</tr>
						<tr>
							<th width="20%" scope="row" valign="top">Stylesheet for OneSportEvent Canvas</th>
							<td width="80%"><input name="stylesheet" type="text" value="<?php echo $settings['stylesheet'];?>"  size="60"/></td>
						</tr>
					</table>
					<h2>One Sport Event - Optional Parameters</h2>
					<table class="form-table">
						<tr>
							<th width="40%" scope="row" valign="top" style="text-align:right;">Display the Activities panel filter</th>
							<td width="60%"><input name="activityPanel" type="checkbox" <?php echo ($settings['activityPanel']) ? "checked" : "";?> /></td>
						</tr>
						<tr>
							<th width="40%" scope="row" valign="top" style="text-align:right;">Display the Event panel filter</th>
							<td width="60%"><input name="eventPanel" type="checkbox" <?php echo ($settings['eventPanel']) ? "checked" : "";?> /></td>
						</tr>
						<tr>
							<th width="40%" scope="row" valign="top" style="text-align:right;">Display the Style panel filter</th>
							<td width="60%"><input name="stylePanel" type="checkbox" <?php echo ($settings['stylePanel']) ? "checked" : "";?> /></td>
						</tr>
						<tr>
							<th width="40%" scope="row" valign="top" style="text-align:right;">Display the Date panel filter</th>
							<td width="60%"><input name="datePanel" type="checkbox" <?php echo ($settings['datePanel']) ? "checked" : "";?> /></td>
						</tr>
						<tr>
							<th width="40%" scope="row" valign="top" style="text-align:right;">Display the text Search panel filter</th>
							<td width="60%"><input name="searchPanel" type="checkbox" <?php echo ($settings['searchPanel']) ? "checked" : "";?> /></td>
						</tr>
						<tr>
							<th width="40%" scope="row" valign="top" style="text-align:right;">Select Area Level<br/><small>If set to Auto, Area ID will be ignored</small></th>
							<td width="60%"><?php echo $this->getAreaLevelList($settings['oseAreaLevel']);?></td>
						</tr>
						<tr>
							<th width="40%" scope="row" valign="top" style="text-align:right;">Enter Area ID<br/><small>(CountryID, CityID or RegionID you wish to default the view to)</small></th>
							<td width="60%"><input name="oseAreaID" type="text" value="<?php echo $settings['oseAreaID'];?>" /></td>
						</tr>
						<tr>
							<th width="40%" scope="row" valign="top" style="text-align:right;">Enter Club ID<br/><small>Includes non-shared events for this specific club</small></th>
							<td width="60%"><input name="clubID" type="text" value="<?php echo $settings['clubID'];?>" /></td>
						</tr>
						<tr>
							<th width="40%" scope="row" valign="top" style="text-align:right;">A comma separated list of activity codes to display.<br/><small>e.g. '1,3' - Displays running and walking activities only</small></th>
							<td width="60%"><input name="oseActivities" type="text" value="<?php echo $settings['oseActivities'];?>" /></td>
						</tr>
						<tr>
							<th width="40%" scope="row" valign="top" style="text-align:right;">A comma separated list of event codes to display.<br/><small>e.g. '2,3' - Displays 10km and Half Marathon events only</small></th>
							<td width="60%"><input name="oseEvents" type="text" value="<?php echo $settings['oseEvents'];?>" /></td>
						</tr>
						<tr>
							<th width="40%" scope="row" valign="top" style="text-align:right;">A comma separated list of style codes to display.<br/><small>e.g. '2,3' - Offroad and mountain events only</small></th>
							<td width="60%"><input name="oseStyles" type="text" value="<?php echo $settings['oseStyles'];?>" /></td>
						</tr>
						<tr>
							<th width="40%" scope="row" valign="top" style="text-align:right;">Get events only after the specified date.<br/><small>e.g. '1-jan-09' - Use dd-MMM-yy format so there is no confusion</small></th>
							<td width="60%"><input name="afterDate" type="text" value="<?php echo $settings['afterDate'];?>" /></td>
						</tr>  
						<tr>
							<th width="40%" scope="row" valign="top" style="text-align:right;">Request a specific page number (integer).<br/><small>e.g. 3 - Displays the 3rd page</small></th>
							<td width="60%"><input name="pageNo" type="text" value="<?php echo $settings['pageNo'];?>" /></td>
						</tr>
						<tr>
							<th width="40%" scope="row" valign="top" style="text-align:right;">Display the crumb</th>
							<td width="60%"><input name="includeCrumb" type="checkbox" <?php echo ($settings['includeCrumb']) ? "checked" : "";?> /></td>
						</tr>
						<tr>
							<th width="40%" scope="row" valign="top" style="text-align:right;">Include the areas</th>
							<td width="60%"><input name="includeAreas" type="checkbox" <?php echo ($settings['includeAreas']) ? "checked" : "";?> /></td>
						</tr>
						<tr>
							<th width="40%" scope="row" valign="top" style="text-align:right;">Number of records per page to display (integer).<br/><small>e.g. 20 - By default 10 records per page are shown</small></th>
							<td width="60%"><input name="perPage" type="text" value="<?php echo $settings['perPage'];?>" /></td>
						</tr>
						<tr>
							<th width="40%" scope="row" valign="top" style="text-align:right;">Search for keywords.<br/><small>e.g. 'muddy' - All events with 'muddy' in the name</small></th>
							<td width="60%"><input name="keyWord" type="text" value="<?php echo $settings['keyWord'];?>" /></td>
						</tr>
						<tr>
							<th width="40%" scope="row" valign="top" style="text-align:right;">Additional Parameter(s) string.<br/><small>Leave it blank if you are not sure</small></th>
							<td width="60%"><input name="extraParam" type="text" value="<?php echo $settings['extraParam'];?>" /></td>
						</tr>
					</table>
					<p style="text-align: center;">
						<input type="submit" name="save" value="Save Settings" class="button" />&nbsp;&nbsp;
						<input type="button" name="cancel" value="Cancel" class="button" onclick="javascript:history.go(-1)" />
					</p>
				</div>
			</form>
<?php
		}

	}// END Class OneSportEvent
}

// Run The Plugin!
if( class_exists('OneSportEvent') ){
	$events = new OneSportEvent();
}
