<?php 
    /*
    Plugin Name: Date Time Count Down Timer
    Plugin URI: http://www.hadikhan.com
    Description: This is a custom plugin created by hadi Khan, for the church mangement system.
    Author: Hadi Khan
    Version: 1.0
    Author URI: http://localhost/
    */


// Database table creation for counter pluggin

    global $wpdb;
  	global $table_name;
  	$table_name = $wpdb->prefix . 'countdown_timer';

function create_database_for_pluggin () {
	// $table_name = $wpdb->prefix . 'countdown_timer';
 
	// create the ECPT metabox database table
	if($wpdb->get_var("show tables like '".$table_name."'") != $table_name) 
	{
		$sql = "CREATE TABLE " . $table_name . " (
		`id` INT NOT NULL AUTO_INCREMENT,
		`prayer_day` VARCHAR(100) NULL,
		`prayer_start_time` DATETIME NULL, 
		`prayer_end_time` DATETIME NOT NULL,
		`current_date_time` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `daysaving_is_enabled` INT(2) NOT NULL DEFAULT '0',
		UNIQUE KEY id (id)
		);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}
// Counter display Option
function wp_dateTime_counter() { 
	date_default_timezone_set('EST');
	$pray_start_time = new DateTime('11:30:00');
	$pray_end_time = new DateTime('1:30:00');
	$currenttime = new DateTime(date('h:i:s'));
	$today_date = date('Y-m-d h:i:s');
	$day = date('l', strtotime($today_date));
	if(($day === 'Sunday') && ($currenttime >= $pray_start_time) && ($currenttime <= $pray_end_time)) {
		echo "<a href='http://gkm.churchonline.org/' target='_blank'>Live</a>";
	} else {
		$current_date_time_new = date('Y-m-d H:i:s', strtotime('+1 hour', strtotime(date('Y-m-d h:i:s'))));
        $next_sunday = date('Y-m-d H:i:s', strtotime('next Sunday', strtotime(date('Y-m-d h:i:s'))));
		$next_sunday = date('Y-m-d H:i:s', strtotime($next_sunday)+41400);
		select_data_from_database();
		?>
		<div id="getting-started"></div>
		<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery("#getting-started")
		   	.countdown("<?php echo $next_sunday; ?>", function(event) {
		     jQuery(this).text(
		       event.strftime('(BETA TESTING): %D days %H:%M:%S')
		    );
		   });
		 });
 </script>
		<?php
	}
}

// WP option display on left navigation bar
function wp_dateTime_counter_admin_page_display() {
    $page_title = 'Prayer counter';
    $menu_title = 'Prayer Counter';
    $capability = 'edit_posts';
    $menu_slug = 'countdown_timer';
    $function = 'wp_dateTime_counter_admin_page';
    $icon_url = 'dashicons-clock';
    $position = 24;

    add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
}


// Display Admin page in admin panel
function wp_dateTime_counter_admin_page () { ?>
	<div class="wrap">
		<h1>Prayer Reverse Count Down Timer</h1>
		<p>This is a custom pluggin created specifically for Global Kingdom. Incase of any carification contact the administrator of the pluggin</p>
		<p>To display the counter, use [sp_countdown_timer] shortcode at decided place </p>
		<?php form_display(); ?>
	</div>
	
<?php }



//Extracting data from the database
function select_data_from_database() {
	global $wpdb;
  	global $table_name;
	$data_from_database = $wpdb->get_results('SELECT * FROM '.$table_name.' ');
	switch(count($data_from_database)) {
		case 0: 
			echo "<p> Nothing in the Database. Kindly add new records</p>";
			break;
		case 1:
			foreach($data_from_database as $values) {
				return $data = array(
					'id' => $values->id,
					'prayer_day' => $values->prayer_day,
					'prayer_start_time' => $values->prayer_start_time,
					'prayer_end_time' => $values->prayer_end_time,
					'daysaving_is_enabled' => $values->daysaving_is_enabled	
					);
			}
			break;
		case 2:
			echo "<p> Database Configuration Error!</p>"; 
			/***
			I am assuming there will alwaays remain single entiry in the database. Incase of Multiple      entries change the code according to you requirements. ***/
			break;
	}
	
}

//display form in admin section
function form_display () { 
    if(isset($_POST['form-submission'])) {
        $results = $wpdb->insert('$table_name', 
                                    array(
                                        'prayer_day' => $_POST['day'],
                                        'prayer_start_time' => $_POST['start_time'] ,
                                        'prayer_end_time' => $_POST['end_time'],
                                        'daysaving_is_enabled' => $_POST['daylight'],
                                    )
                                );
    } else {                      

?>
	<div id="respond">
			<?php //echo $response; ?>
			<form action="<?php esc_url( $_SERVER['REQUEST_URI'] ); ?> " method="POST">
	    		<p><label for="name">Prayer Day: <span style="color:red">*</span> <br>
		    		<input type="radio" name="day" value="Monday" > Monday<br>
	  				<input type="radio" name="day" value="Tuesday"> Tuesday<br>
	  				<input type="radio" name="day" value="Wednesday"> Wednesday<br>
	  				<input type="radio" name="day" value="Thursday"> Thursday<br>
	  				<input type="radio" name="day" value="Friday"> Friday<br>
	  				<input type="radio" name="day" value="Saturday"> Saturday<br>
	  				<input type="radio" name="day" value="Sunday" checked> Sunday<br></label></p>
	    		
	    		<p><label for="start_time">Prayer Start Time: <span style="color:red">*</span> <br>
	    			<input type="time" name="start_time" value=""></label></p>
	    		
	    		<p><label for="end_time">Prayer End Time: <span style="color:red">*</span> <br>
	    		<input type="time" name="end_time" value=""></label></p>
	    		
	    		<p><label for="message_human">Daylight Savings: <span style="color:red">*</span> <br>
	    			<input type="radio" name="daylight" value="0" checked> Enable<br>
	  				<input type="radio" name="daylight" value="1" > Disable<br></label></p>
	    		<input type="hidden" name="submitted" value="1">
	    		<p><input type="submit" name="form-submission"></p>
	  		</form>
		</div>
	
<?php }	
}

function load_cutom_plugin_scripts () {
    wp_enqueue_script ('customJsPlugin', plugin_dir_url ( __FILE__ ) . "assets/js/jquery.countdown.min.js", "", "". true);
}



//** Action hooks **//
add_action('admin_menu', 'wp_dateTime_counter_admin_page_display'); // Set option in wordpress admin panel
add_shortcode('sp_countdown_timer', 'wp_dateTime_counter');
register_activation_hook(__FILE__,'create_database_for_pluggin'); // create database when pluggin is activated
add_action('admin_engueue_scripts', 'load_cutom_plugin_scripts');
?>