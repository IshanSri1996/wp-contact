<?php 
/*
Plugin Name: Contact Form
Description: Used to sent contact infomation
Version: 1.0
Author URI: Ishan Sriyakhan
*/

if (!defined('ABSPATH')){
	echo "Can't access.";
	exit();
}

class contactForm {

	public function __construct(){

		add_action ('init', array( $this, 'create_custom_post_type'));

		add_action ('wp_enqueue_scripts', array( $this, 'load_assets'));

		add_shortcode ('contact-form', array( $this, 'load_short_code'));

		add_action ('wp_footer', array( $this, 'load_scripts'));

		add_action ('rest_api_init', array( $this, 'register_rest_api'));
	}

	public function create_custom_post_type(){

		$args = array(
			"public"=> true,
			'has_archive'=> true,
			'supports'=> array('title'),
			'exclude_from_search'=> true,
			'publicly_queryable'=> false,
			'capability'=> 'manage_options',
			'labels'=> array(
				'name'=>'Contact Form',
				'singular_name'=>'Contact Form Entry'
			),
			'menu_icon'=> 'dashicons-media-text',
		);

		register_post_type('contact_form', $args);
	}

	public function load_assets(){

		wp_enqueue_style(
			'contact_form_css',
			plugin_dir_url(__FILE__).'css/contact-form.css',
			array(),
			1.0,
			'all'
		);
		wp_enqueue_script(
			'contact-form_js',
			plugin_dir_url(__FILE__).'js/contact-form.js',
			array(),
			1.0,
			true
		);
	}

	public function load_short_code(){ ?>
	
					<div class="form col-md-6">
                        <h2 class="mb-3 text-start">Welcome Back</h2>
                        <p class="lead mb-6 text-start">Fill your email and password to sign in.</p>
                        <form class="text-start mb-3" id="contact-form">
                          <div class="form-floating mb-4">
                            <input name="name" type="text" class="form-control">
                            <label>Name</label>
                          </div>
                          <div class="form-floating mb-4">
                            <input name="email" type="email" class="form-control">
                            <label>Email</label>
                          </div>
                          <div class="form-floating mb-4">
                            <input name="phone" type="tel" class="form-control">
                            <label>Phone</label>
                          </div>
                          <div class="form-floating mb-4">
                            <textarea name="message" class="form-control"></textarea>
                            <label>Message</label>
                          </div>
                          <button class="btn btn-primary rounded-pill btn-login w-100 mb-2" type="submit" value="submit">Submit</button>
                        </form>
                    </div>

	<?php }

	public function load_scripts(){ ?>
	
					<script type="text/javascript">
						var nonce = '<?php echo wp_create_nonce('wp_rest');?>';

						$('#contact-form').submit( function (e) {
							event.preventDefault();
							var form = $(this).serialize();
							
							$.ajax({
								method:'post',
								url: '<?php echo get_rest_url(null, 'contact-form/v1/send-mail'); ?>',
								headers: { 'X-WP-Nonce': nonce },
								data: form

							})
						})
					</script>

	<?php }

	public function register_rest_api(){

		register_rest_route('contact-form/v1', 'send-mail', array(

			'methods'=> 'POST',
			'callback'=> array($this, 'handle_contact_form')
		));
	}

	public function handle_contact_form($data){
		$headers = $data->get_headers();
		$params = $data->get_params();
		$nonce = $headers['x_wp_nonce'][0];

		if(!wp_verify_nonce( $nonce, 'wp_rest')){
			return new WP_REST_Response('Message not sent', 422);
		}

		$post_id = wp_insert_post([

			'post_type'=>'contact_form',
			'post_title'=>'contact_enquiry',
			'post_status'=>'publish'
		]);

		if($post_id){
			return new WP_REST_Response('Thank You', 200);
		}
	}

}

new contactForm;