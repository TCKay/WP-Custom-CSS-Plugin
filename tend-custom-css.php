<?php

/*
Plugin Name: 10° Custom CSS
Plugin URI: https://10degrees.uk
Description: Add Mobile-First CSS from the Dashboard and output it into a Cacheable file.
Author: Tom Kay (10 Degrees)
Version: 1.0.0
Author URI: https://10degrees.uk
*/


add_action('admin_menu', 'tend_custom_css');

function tend_custom_css(){
  add_options_page('10° CSS', 'Custom CSS', 'manage_options', '10d-custom-css', 'tend_custom_css_page');

  //call register settings function
	add_action( 'admin_init', 'register_tend_css_settings' );
}

function tend_ccss_load_admin_style($hook) {
        // Load only on ?page=10d-custom-css
        if($hook != 'settings_page_10d-custom-css') {
                return;
        }
        // wp_enqueue_style( 'tend_ccss_wp_admin_css', plugins_url('tend-custom-css/CodeMirror/theme/neat.css', dirname(__FILE__) ));
        wp_enqueue_style( 'tend_ccss_wp_admin_css_theme', plugins_url('tend-custom-css/CodeMirror/lib/codemirror.css', dirname(__FILE__) ));
        wp_enqueue_script( 'tend_ccss_wp_admin_cmjs', plugins_url('tend-custom-css/CodeMirror/lib/codemirror.js', dirname(__FILE__) ));
       wp_enqueue_script( 'tend_ccss_wp_admin_js', plugins_url('tend-custom-css/CodeMirror/mode/css/css.js', dirname(__FILE__) ));
}
add_action( 'admin_enqueue_scripts', 'tend_ccss_load_admin_style' );


function register_tend_css_settings() {
	//register our settings
	register_setting( 'tend-css-settings-group', 'mobilecss' );
	register_setting( 'tend-css-settings-group', 'tabletcss' );
	register_setting( 'tend-css-settings-group', 'desktopcss' );
	register_setting( 'tend-css-settings-group', 'tablet_breakpoint' );
	register_setting( 'tend-css-settings-group', 'tablet_breakpoint' );
}

function tend_custom_css_page() {

  if (!current_user_can('manage_options'))  {
    wp_die( __('You do not have permission to access this page.')    );
  }

  // Start building the page

  echo '<div class="wrap">';

  echo '<h2>10° Custom CSS</h2>';

  // Check whether the button has been pressed AND also check the nonce
  if (isset($_POST['gen_css']) && check_admin_referer('update_button_clicked')) {
    // the button has been pressed AND we've passed the security check
    tend_ccss_update_the_stylesheet();
    tend_ccss_save_css_options();
  }

  echo '<form action="options-general.php?page=10d-custom-css" method="post">';

  settings_fields( 'tend-css-settings-group' );
  do_settings_sections( 'tend-css-settings-group' );

  // this is a WordPress security feature - see: https://codex.wordpress.org/WordPress_Nonces
  wp_nonce_field('update_button_clicked');


  echo '<h3>Mobile Styles</h3><textarea rows="10" cols="100" class="csseditor" id="csseditor" name="mobilecss">' . esc_attr( get_option('tend_mobilecss') ) . '</textarea>';
  echo '<h3>Tablet Styles</h3>';
  echo '<b>Tablet Breakpoint:</b><br /><input type="text" style="margin-bottom: 15px;" name="tablet_breakpoint" value="' . esc_attr( get_option('tend_tabletbreak') ) . '" /> (pixels) <br />';
  echo '<textarea rows="10" cols="100" class="csseditor" name="tabletcss">' . esc_attr( get_option('tend_tabletcss') ) . '</textarea>';
  echo '<h3>Desktop Styles</h3>';
  echo '<b>Desktop Breakpoint:</b><br /><input type="text" style="margin-bottom: 15px;" name="desktop_breakpoint" value="' . esc_attr( get_option('tend_desktopbreak') ) . '" /> (pixels)';
  echo '<textarea rows="10" cols="100" class="csseditor" name="desktopcss">' . esc_attr( get_option('tend_desktopcss') ) . '</textarea>';

  echo '<hr />';


  echo '<b>Change Log:</b><br /><input style="width:100%;" required type="text" name="changelog" value="" /> (You must make a note describing your change before updating)';
  echo '<input type="hidden" value="true" name="gen_css" />';
  submit_button('Update your stylesheet');
  echo '</form>';


  $history = get_option('tend_changelog');
  if($history){
  echo '<h3>Recent Changes</h3>';
  echo '<div style="width:100%; height:400px; overflow:auto;">';
  foreach($history as $entry) {

    echo '<p>' . $entry . '</p> <hr />';

  }
  echo '</div>';
  }



  echo '</div>';


  ?>
<script>
jQuery(document).ready(function($) {
var myTextArea = $('.csseditor');

var myCodeMirror = CodeMirror.fromTextArea(myTextArea.get(0), {
        lineNumbers: true,
        matchBrackets: true,
         extraKeys: {"Ctrl-Space": "autocomplete"},
         mode: "text/css",
});
var myCodeMirror2 = CodeMirror.fromTextArea(myTextArea.get(1), {
        lineNumbers: true,
        matchBrackets: true,
         extraKeys: {"Ctrl-Space": "autocomplete"},
         mode: "text/css",
});
var myCodeMirror3 = CodeMirror.fromTextArea(myTextArea.get(2), {
        lineNumbers: true,
        matchBrackets: true,
         extraKeys: {"Ctrl-Space": "autocomplete"},
         mode: "text/css",
});
});
</script>
  <?php

}

function tend_ccss_update_the_stylesheet()
{
  echo '<div id="message" class="updated fade"><p>'
    .'Your stylesheet was updated' . '</p></div>';



   $myFile = plugin_dir_path( __FILE__ ) . "/tend_custom.css";


   $tabletbreak = $_POST["tablet_breakpoint"];
   $desktopbreak = $_POST["desktop_breakpoint"];

   $mobile = $_POST["mobilecss"];
   $tablet = $_POST["tabletcss"];
   $desktop = $_POST["desktopcss"];


   $filecontents = $mobile . '@media (min-width:' . $tabletbreak . 'px) {' . $tablet . '} @media (min-width:' . $desktopbreak . 'px) {' . $desktop . '}';

    //  var_dump($filecontents);

      //write contents in to css file
      if(file_put_contents($myFile, $filecontents)) {
				echo 'The stylesheet has been updated';
       }
      else {
           echo "error";
         }
   }



   function tend_ccss_save_css_options() {

     $tabletbreak = $_POST["tablet_breakpoint"];
     $desktopbreak = $_POST["desktop_breakpoint"];

     $mobile = $_POST["mobilecss"];
     $tablet = $_POST["tabletcss"];
     $desktop = $_POST["desktopcss"];
     $current_user = wp_get_current_user();
     $changelogentry = '<b>User:</b> '. $current_user->display_name . '<br /><b>Log:</b> ' . $_POST["changelog"] . '<br /><b>Date:</b> ' . date("l jS \of F Y h:i:s A");

     $changelog = array($changelogentry);

     if (get_option('tend_changelog')) {
     $oldlog = get_option('tend_changelog');
     $newchangelog = array_merge( $changelog , $oldlog);
   } else {
     $newchangelog = $changelog;
   }

     $cachebuster = date('zoB');


     update_option( 'tend_mobilecss', $mobile );
     update_option( 'tend_tabletcss', $tablet );
     update_option( 'tend_desktopcss', $desktop );

     update_option( 'tend_tabletbreak', $tabletbreak );
     update_option( 'tend_desktopbreak', $desktopbreak );

     update_option('tend_changelog',  $newchangelog );

    update_option( 'tend_cachebuster', $cachebuster );


   }



   function tend_load_custom_styles() {
      $cachebuster = get_option( 'tend_cachebuster');
      $stylesheeruri = plugins_url( 'tend-custom-css/tend_custom.css', dirname(__FILE__) );
      ?>
      <link rel="stylesheet" id="tend-custom-styles-css" href="<?php echo $stylesheeruri;?>?ver=<?php echo $cachebuster;?>" type="text/css" media="all">
      <?php

   }
   add_action( 'wp_head', 'tend_load_custom_styles' , 200);
