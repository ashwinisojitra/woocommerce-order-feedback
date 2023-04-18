<?php

/*
 * Plugin Name:       Woocommerce Feedback Form
 * Description:       Feedback Form for Woocommerce
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Ashwini
 */

global $wpdb;

define('FEEDBACK_TABLE', $wpdb->prefix.'feedback_info');

function feedback_form_plugin($order_id)
{
	$order = new WC_Order($order_id);
	$user = $order->get_user();
	$customerName = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();

	$content = '<div class="feedback_message"></div>';
	$content .= '<div class="feedback_form">';
	$content .= '<h2>Feedback Form</h2>';
	$content .= '<form method="post" id="order_feedback_form" action="'. site_url() .'/">';
	$content .= '<label for="name">Name</label>';
	$content .= '<input type="text" name="name" required class="form_control" placehoder= "Name" value="'.$customerName.'" /><br><br>';

    $content .= '<label for="feedback">Feedback</label>';
	$content .= '<textarea name="feedback" required class="form_control" placehoder = "Enter Your Feedback Here"></textarea><br><br>';

	$content .= '<br /><input type="submit" name="feedback_form_button" class="form_control" value="SUBMIT YOUR FEEDBACK" />';

	$content .= '<br /><input type="hidden" name="feedback_form_submit" value="1" />';
	$content .= '<br /><input type="hidden" name="order_id" value="' . $order_id .'" />';
	$content .= '</form>';
	$content .= '</div>';

	echo $content;
}

add_shortcode('feedback_form','feedback_form_plugin');

wp_register_script( 'feedback-script', plugins_url( 'js/feedback.js', __FILE__ ) );

wp_enqueue_script( 'feedback-script' );


if (!empty($_POST) && $_POST['feedback_form_submit'] == 1)
{
    $data = array(
        'customer_name'       => $_POST['name'],
        'feedback_details'    => $_POST['feedback'],
        'feedback_date'       => date('Y-m-d H:i:s'),
        'order_number'        => $_POST['order_id']
    );

    $success=$wpdb->insert( FEEDBACK_TABLE, $data);

    if($success) {
        echo 'Feedback saved successfully' ; 
    }
    wp_die();
 }

 //Create Database Table

function database_creation()
{
 	$feedback_det = "CREATE TABLE ". FEEDBACK_TABLE . " (
	 		feedback_id int  NOT NULL auto_increment,
	 		customer_name  varchar(100), 
	 		feedback_details varchar(1000),
	 		feedback_date date,
	 		order_number varchar(100),
	 		PRIMARY KEY(feedback_id)
 		)  AUTO_INCREMENT=1";

 	require_once (ABSPATH . 'wp-admin/includes/upgrade.php');

 	dbDelta( $feedback_det );
} 

register_activation_hook(__FILE__, 'database_creation');

add_action( 'woocommerce_thankyou', 'feedback_form_plugin', 100, 1 );
   
require_once(__DIR__ . '/feedback_list.class.php');


function feedback_admin_menu()
{
    add_menu_page(
    	__('Order Feedback', 'woocommerce_feedback'),
     	__('Feedbacks', 'woocommerce_feedback'),
     	 'activate_plugins', 
     	 'feedbacks', 
     	 'woocommerce_feedback_page_handler'
    );    
}

add_action('admin_menu', 'feedback_admin_menu'); 

function woocommerce_feedback_page_handler()
{
    global $wpdb;

    $table = new Woocommerce_Feedback_List_Table();
    $table->prepare_items();

    $message = '';
    if ('delete' === $table->current_action()) {
        $message = '<div class="updated below-h2" id="message"><p>' . sprintf(__('Items deleted: %d', 'woocommerce_feedback'), count($_REQUEST['id'])) . '</p></div>';
 }
 ?>
<div class="wrap">
	<div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
	   	<h2>
	    	<?php _e('Order Feedbacks', 'woocommerce_feedback')?>
	    </h2>

	    <?php echo $message; ?>

	    <form id="woocommerce-feedback" method="GET">
	        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>
	        <?php $table->display() ?>
	    </form>

</div>
<?php
}
?>