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

function create_database_for_pluggin () {
	
	global $wpdb;
  	global $table_name;
    
    $table_name = $wpdb->prefix . 'countdown_timer';
 
	// create the ECPT metabox database table
	if($wpdb->get_var("show tables like '".$table_name."'") != $table_name) 
	{
		$sql = "CREATE TABLE " . $table_name . " (
		`id` INT NOT NULL AUTO_INCREMENT,
		`prayer_day` VARCHAR(100) NULL,
		`prayer_start_time` DATETIME NULL, 
		`prayer_end_time` DATETIME NOT NULL,
		`current_date_time` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
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
        ?>
		<div id="getting-started"></div>
		<script type="text/javascript">
		jQuery(document).ready(function(){
			//var nextYear = moment.tz("<?php echo $current_date_time->date; ?>", "EST");
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


//display form in admin section
function form_display () { ?>
	<div id="respond">
			<?php //echo $response; ?>
			<form action="" method="post">
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
	    		<input type="time" name="end_time" value=""></label></p></label></p>
	    		
	    		<p><label for="message_human">Daylight Savings: <span style="color:red">*</span> <br>
	    			<input type="radio" name="daylight" value="0" checked> Enable<br>
	  				<input type="radio" name="daylight" value="1" > Disable<br></label></p>
	    		<input type="hidden" name="submitted" value="1">
	    		<p><input type="submit"></p>
	  		</form>
		</div>
	</div>
<?php }	



//** Action hooks **//
add_action('admin_menu', 'wp_dateTime_counter_admin_page_display'); // Set option in wordpress admin panel
add_shortcode('sp_countdown_timer', 'wp_dateTime_counter');
register_activation_hook(__FILE__,'create_database_for_pluggin'); // create database when pluggin is activated
?>