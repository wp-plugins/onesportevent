<?php
/**
 * Plugin Name: One Sport - Event Calendar
 * Plugin URI: http://www.onesportevent.com/get-free-event-calendar-widget-on-your-website/
 * Description: Display Sport events (running, cyclying, triathalon etc) from OneSportEvent.com 
 * Author: sporty - sport@onesportevent.com
 * Author URI: http://www.onesportevent.com/about-us
 * Version: 2.8.0
 * 
 * CHANGELOG
 * 2.8   - Bug with keyword fixed; automatic flexible layout introduced; extra instant styling options
 * 2.7   - Added new date filter
 * 2.6	 - Improved API Performance
 * 2.5	 - Improved descriptions
 * 2.3   - Fixed up typos and screenshots
 * 2.0   - Improved admin screen - reminder message added so people know they have to click 'create page'
 * 1.9   - UPDAT - Improved on screen instructions, now api v5
 * 1.8	 - INFO  - Updated in WordPress Plugin repository, works with OneSportEvent API Version 4.
 * 1.1   - ADDED - Ability to create event page via plugin admin page
 *       - UPDAT - Updated API urls
 * 1.0   - ADDED - Detailed documentation - readme.doc
 *         FIXED - Minor fix to the API call
 * 0.9   - ADDED - Screenshot of admin page
 * 0.8   - INFO  - Added to WordPress Plugin repository
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
			$settings['styleHideBorders'] = 0;
			$settings['styleHideImage'] = 0;
			$this->saveSettings($settings);
		}

		function init() {
			wp_enqueue_script('jquery');
		}

		function wp_head() {
			if (is_feed()) {
				return;
			}

			$settings = array();
			$this->getSettings($settings);

			$list = explode(',', $settings['postId']);
			if((!is_page($list)) && (!is_single($list))) { return; }

			echo "<!-- OneSportEvent Plugin - stylesheet and API link. -->\n";
			if($settings['stylesheet'] != '') {
				echo "<link rel='stylesheet' href='" . $settings['stylesheet'] . "' type='text/css' media='screen' />\n";
			}

			$optional_parameters_boolean = array('activityPanel','eventPanel','stylePanel','datePanel','searchPanel','includeCrumb','includeAreas', 'styleHideBorders', 'styleHideImage');
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


			$style_string = '';

			if($settings['styleRed'] != '') {
			   $style_string .= ".osered { color: " . $settings['styleRed'] . " !important; }";
			}

			if($settings['styleText'] != '') {
			   $style_string .= "#oseMaster a, #oseMaster p, #oseMaster ul, #oseMaster li, #oseMaster ol { color: " . $settings['styleText'] . " !important; }";
			}

			if($settings['styleMainBackground'] != '') {
			   $style_string .= ".oseleft_block { background: " . $settings['styleMainBackground'] . " !important; }";
			}

			if( $style_string != '' ) {
				echo "<style type='text/css'>";
				echo $style_string;
				echo "</style>\n";
			}

			echo "<!-- /OneSportEvent Plugin - stylesheet and API link. -->\n";
		}

		function onesportevent_menu() {

			add_submenu_page('options-general.php', 'One Sport Event' ,'One Sport Event', '10', __FILE__, array($this, 'manageSettings'));
		}

		function getSettings(&$settings) {
			$settings = unserialize(get_option('onesportevent_settings'));
			// Default Values
			if($settings['path'] == '') {
				$settings['path'] = 'http://api.onesportevent.com/api/event/v6/EventAPI.aspx';
			}
			if($settings['stylesheet'] == '') {
				$settings['stylesheet'] = 'http://api.onesportevent.com/api/style/v2/css/osestyle.css';
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
		
				function create_page(){
			
			$settings = array();
			$this->getSettings($settings);
			$findme   = 'id="oseEventCanvas"';

			$page_data = get_page( $settings['postId'] );	// Get page info
			$content = $page_data->post_content;			// Get content
			$pos = strpos($content, $findme);				// Search content for placeholder

			if ($pos > 0) {
			}
			else
			{
			// You can also see a <a href='http://onesportevent.com/api/event/video'>video</a> on how to configure the optional parameters
				echo "<div id='message' class='updated fade'>
					  <p><b>OneSportEvents is installed</b>.  You'll need to <a href='options-general.php?page=onesportevent/onesportevent.php'>go to the configuration</a>, <strong>create a page</strong> and configure any optional parameters before your events will display on your website.  Please email sport@onesportevent.com if you can't get the event styling looking how you want it, or to request new features.</p></div>";
			}

		}
		
		function manageSettings() {

			// Variables
			$base_name = plugin_basename('onesportevent/onesportevent.php');
			$base_page = 'admin.php?page='.$base_name;

			$settings = array();
			$this->getSettings($settings);

			$autosave = false;
			// Place below getSettings purposefully.
			if(!empty($_POST['create'])) {
				$title = 'Upcoming Sporting Events';
				$content = '<div id="oseEventCanvas"><!-- This is the event placeholder, do not remove this tag --></div>';
				$type = 'page';
		        $post_id = wp_insert_post(array(
					'post_type'		=> $type,
        		    'post_title'    => $title,
		            'post_content'  => $content,
		            'post_status'   => 'publish',
		        ));	
				if($post_id > 0) {
					$post_list = array();
					$post_list = explode(',', $_POST['postId']);
					$post_list[] = $post_id;
					$post_list = array_filter(array_unique($post_list));
					$settings['postId'] = implode(',', $post_list);
					$this->message = 'One Sport Events page created and settings saved successfully';
				}
				$autosave = true;
			}

			// Form Processing
			if(!empty($_POST['save']) || $autosave) {
				if(!$autosave) {
					$settings['postId'] = $_POST['postId'];
					$this->message = 'Settings saved successfully...';
				}
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
				$settings['styleHideBorders'] = ($_POST['styleHideBorders'] == 'on') ? 1 : 0;
				$settings['styleHideImage'] = ($_POST['styleHideImage'] == 'on') ? 1 : 0;
				$settings['oseAreaID'] = $_POST['oseAreaID'];
				$settings['clubID'] = $_POST['clubID'];
				$settings['afterDate'] = $_POST['afterDate'];
				$settings['pageNo'] = $_POST['pageNo'];
				$settings['includeCrumb'] = ($_POST['includeCrumb'] == 'on') ? 1 : 0;
				$settings['includeAreas'] = ($_POST['includeAreas'] == 'on') ? 1 : 0;
				$settings['perPage'] = $_POST['perPage'];
				$settings['keyWord'] = $_POST['keyWord'];
				$settings['extraParam'] = $_POST['extraParam'];

				/* colour styles */
				$settings['styleRed'] = $_POST['styleRed'];
				$settings['styleText'] = $_POST['styleText'];
				$settings['styleMainBackground'] = $_POST['styleMainBackground'];

				$this->saveSettings($settings);
			}

			if(!empty($this->message)) { echo '<!-- Last Message --><div id="message" class="updated fade"><p style="color:green;">'.stripslashes($this->message).'</p></div>'; } else { echo '<div id="message" class="updated" style="display: none;"></div>'; }
?>
			<form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post">
				<div class="wrap">
					<h2>One Sport Event - WebKey</h2>
					<br class="clear" />
					<table class="form-table">
						<tr>
							<th width="20%" scope="row" valign="top">Your API WebKey</th>
							<td width="80%"><input name="WebKey" type="text" value="<?php echo $settings['WebKey'];?>"  size="60"/><a href="http://www.onesportevent.com/get-widget-key" title="Get Webkey" style="text-decoration:none;font-weight:bold;margin-left:10px;" target="_blank">Get Webkey</a></td>
						</tr>
						<tr>
							<th width="20%" scope="row" valign="top">OneSportEvent API Url</th>
							<td width="80%"><input name="path" type="text" value="<?php echo $settings['path'];?>"  size="60"/></td>
						</tr>
						<tr>
							<th width="20%" scope="row" valign="top">Stylesheet for branding your events (default is common)</th>
							<td width="80%"><input name="stylesheet" type="text" value="<?php echo $settings['stylesheet'];?>"  size="60"/></td>
						</tr>
					</table>
					<h2>One Sport Event - Create Event Page to show events</h2>
					<br class="clear" />
					<p style="margin-left:1em;">
						<input type="submit" name="create" value="Create New Event Page" class="button" />
					</p>
					<?php if($settings['postId'] != '') { ?>
					<table class="form-table">
						<tr>
							<th width="30%" scope="row" valign="top">Enable API on pages/posts with ID<br/><small style="color:red;">Please ignore this if you are not sure what this is!</small></th>
							<td width="70%"><input name="postId" type="text" value="<?php echo $settings['postId'];?>"  size="20"/></td>
						</tr>
					</table>
					<?php } ?>
					<h2 style="float:left;">One Sport Event - Optional Parameters</h2><a href="http://www.onesportevent.com/event-api-documentation/" title="Visit configuration documentation" style="float:left; font-weight:bold; margin-top:25px; text-decoration:none;" target="_blank">Visit configuration documentation</a>
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
							<th width="30%" scope="row" valign="top" style="text-align:right;">Enter Area ID<br/><small>The <a style="background-color: transparent;" target="_blank" href="http://www.onesportevent.com/route-mapping-api-documentation/#regions">number</a> of the CountryID, CityID or RegionID you wish to default the view to</small></th>
							<td width="60%"><input name="oseAreaID" type="text" value="<?php echo $settings['oseAreaID'];?>" /></td>
						</tr>
						<tr>
							<th width="40%" scope="row" valign="top" style="text-align:right;">Enter Club ID<br/><small>Includes non-shared events for this specific club</small></th>
							<td width="60%"><input name="clubID" type="text" value="<?php echo $settings['clubID'];?>" /></td>
						</tr>
						<tr>
							<th width="40%" scope="row" valign="top" style="text-align:right;">A comma separated list of <a href="http://www.onesportevent.com/route-mapping-api-documentation/#optionalparameters" title="See configuration documentation" style="font-weight:bold; text-decoration:none;" target="_blank">activity codes</a> to display.<br/><small>e.g. '1,3' - Displays running and walking activities only.  Leave empty for all activities</small></th>
							<td width="60%"><input name="oseActivities" type="text" value="<?php echo $settings['oseActivities'];?>" /></td>
						</tr>
						<tr>
							<th width="40%" scope="row" valign="top" style="text-align:right;">A comma separated list of event codes to display.<br/><small>e.g. '2,3' - Displays 10km and Half Marathon events only.  Leave empty for all events</small></th>
							<td width="60%"><input name="oseEvents" type="text" value="<?php echo $settings['oseEvents'];?>" /></td>
						</tr>
						<tr>
							<th width="40%" scope="row" valign="top" style="text-align:right;">A comma separated list of style codes to display.<br/><small>e.g. '2,3' - Offroad and mountain events only.  Leave empty for all styles</small></th>
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
					
					<div class="updated fade" id="stylingoptions" style="background-color: #FFFBCC"><p style="color: blue;">Use your own stylesheet link above to completely define your own look and feel, or instead use these quick styling options for minor changes</p></div>

					<table class="form-table">
						<tr>
							<th width="30%" scope="row" valign="top" style="text-align:right;">Hilight Text<br/><small>e.g. number of events per area, event date.  Suggested value #EB6909</small></th>
							<td width="70%"><input name="styleRed" type="text" value="<?php echo $settings['styleRed'];?>" /></td>
						</tr>
						<tr>
							<th width="30%" scope="row" valign="top" style="text-align:right;">Normal Text<br/><small>e.g. text in lists, paragraphs and links.  Suggested value #404041</small></th>
							<td width="70%"><input name="styleText" type="text" value="<?php echo $settings['styleText'];?>" /></td>
						</tr>
						<tr>
							<th width="30%" scope="row" valign="top" style="text-align:right;">Main panel background<br/><small>e.g. Suggested value white (#FFFFFF), transparent or use an image</small></th>
							<td width="70%"><input name="styleMainBackground" type="text" value="<?php echo $settings['styleMainBackground'];?>" /></td>
						</tr>
						<tr>
							<th width="30%" scope="row" valign="top" style="text-align:right;">Tick to hide the white borders<br/><small>On dark backgrounds if may look better to change border colors using css, or use this to turn borders off completely.</small></th>
							<td width="70%"><input name="styleHideBorders" type="checkbox" <?php echo ($settings['styleHideBorders']) ? "checked" : "";?> /></td>
						</tr>
						<tr>
							<th width="30%" scope="row" valign="top" style="text-align:right;">Hide activity image<br/><small>Hides the running, walking, cycling.. etc image </small></th>
							<td width="70%"><input name="styleHideImage" type="checkbox" <?php echo ($settings['styleHideImage']) ? "checked" : "";?> /></td>
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
	add_action( 'admin_notices', array($events,'create_page') );
}

?>