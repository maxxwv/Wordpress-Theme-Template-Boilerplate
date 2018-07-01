<?php
namespace AW\ThemeName;
/**
 *	Base functions file
 *	Timber documentation:
 *	http://timber.github.io/timber/
 *	https://github.com/timber/timber/wiki/Timber-docs
 */
class Functions{
/**
 *	@var	string		The theme directory. Note that Timber doesn't
 *				necessitate your creating a child theme because you're
 *				including the library and calling everything via
 *				dependency injection. This could potentially allow us
 *				to create a main theme, then spin off child themes from
 *				that, which could be useful in the future.
 */
	private	$theme_dir;
/**
 *	@var	string		Template file extension. Everyone's going to have to
 *				update thier IDE configuration files to read and color
 *				.twig files correctly unless we want to use .html
 *				as an extension, which is possible.
 */
	private	$file_ext;
/**
 *	Constructor method.
 */
	public function __construct(){
		$this->theme_dir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR;
		$this->file_ext = apply_filters('aw_change_file_ext', '.twig');
	}

/**
 *	Clean up the WordPress <head> section.
 *	While a lot of this is great for a lot of sites, it doesn't do much for us here
 *	other than weigh down the page load.
 *	@return		void
 */
	private function clean_up_head(){
		\remove_action('wp_head','wlwmanifest_link');
		\remove_action('wp_head','feed_links', 2);
		\remove_action('wp_head','rsd_link');
		\remove_action('wp_head','wp_generator');
		\remove_action('wp_head','start_post_rel_link');
		\remove_action('wp_head','index_rel_link');
		\remove_action('wp_head','adjacent_posts_rel_link_wp_head');
		\remove_action('wp_head','print_emoji_detection_script', 7);
		\remove_action('admin_print_scripts','print_emoji_detection_script');
		\remove_action('wp_print_styles','print_emoji_styles');
		\remove_action('admin_print_style','print_emoji_styles');
	}

/**
 *	Set up the entire theme.
 *	@return	void
 */
	public function set_up_theme(){
		$this->clean_up_head();
		$this->add_theme_support()();
		$this->register_menus();
		$this->create_post_types();
		$this->register_widgets();
		$this->register_sidebars();
		$this->register_taxonomy();
	}

/**
 *	Base WP add theme support functionality, this gets called every theme regardless.
 *	@return	void
 */
	private function add_theme_support(){
		\add_theme_support('post-formats');
		\add_theme_support('post-thumbnails');
		\add_theme_support('menus');
		\add_theme_support('custom-header', array('flex-height' => true, 'flex-width' => true));
		\add_theme_support('custom-logo', array('flex-height' => true, 'flex-width' => true));
		\add_theme_support('html5', array(
			'search-form',
			'gallery',
			'caption'
		));
		\add_post_type_support('page', 'excerpt');
	}

/**
 *	Enable SVG file uploads.
 *	Used for service page featured image.
 *	@param		array		$mimes		Default allowed mime types
 *	@return		array
 */
	public function enable_svg_upload(array $mimes){
		$mimes['svg'] = 'image/svg+xml';
		return $mimes;
	}

/**
 *	Queue site assets - JS, CSS, fonts - as necessary
 *	@return	void
 */
	public function queue_resources(){
		$this->queue_javascript();
		$this->queue_fonts();
		$this->queue_css();
	}
	
/**
 *	Queue necessary javascript
 *	@return	void
 */
	private function queue_javascript(){
		\wp_enqueue_script('polyfill', '//cdnjs.cloudflare.com/ajax/libs/js-polyfills/0.1.41/polyfill.js', array(), null, true);
		\wp_enqueue_script('site', \get_stylesheet_directory_uri().'/js/siteScript.min.js');
	}
/**
 *	Queue any externally hosted (Google or TypeKit) fonts
 *	@return		void
 */
	private function queue_fonts(){
		
	}
/**
 *	Queue the site stylesheet
 *	Note that the base style.css that's necessary for every WP theme is hard-coded
 *	into the base_html_head.twig file.
 *	@return	void
 */
	private function queue_css(){
		
	}

/**
 *	Set up the navigation menu areas to be used throughout the site
 *	@return	void
 */
	private function register_menus(){
		\register_nav_menus(array(
			'site_main' => __('Main Menu', 'aw'),
			'site_footer' => __('Footer Menu', 'aw')
		));
	}

/**
 *	Register system-wide widgets.
 *	@return		void
 */
	public function register_widgets(){
	}

/**
 *	Registers the sidebars.
 *	@return		void
 */
	public function register_sidebars(){
	}

/**
 *	Register any custom taxonomies.
 *	@return		void
 */
	private function register_taxonomy(){
	}

/**
 *	Create the custom post types we're using for the site.
 *	@return		void
 */
	private function create_post_types(){
	}

/**
 *	This is the main entry into data/presentation separation via Twig.
 *	The 'timber/context' hook in the functions.php file kicks this thing
 *	off. We need to return (obviously) the post content and menus, however,
 *	we can do all sorts of fun stuff here. Instead of relying on the php
 *	files in the main theme directory to decide which template to render
 *	and spreading that logic across all those files, thereby making things
 *	just as bad as they would be without Twig, we can figure out the
 *	template to render and pass it as an array index. So, the only file
 *	we should need with any actual php content in it is index.php, and
 *	that only needs 3 lines of code. This allows us to work with WP
 *	using a prototype-style pattern instead of php template tags and
 *	logic spread throughout the theme files. Please see the readme
 *	for instructions on creating discrete custom page/post templates.
 *	@param	array	$context		Base Timber object in an associative array
 *	@return	array
 */
	public function add_to_context(array $context){
		if(defined('DOING_AJAX') && DOING_AJAX){
			return $context;
		}
		$context['menus'] = $this->get_registered_menus();
		$context['template'] = $this->get_render_template($context['posts']);
		$context['widgets'] = $this->get_widgets();
		$context['pagination'] = $this->add_pagination();
		$context = $this->get_additional_page_data($context);
		$context['site']->header_image = $this->get_header_image();
		$context['site']->logo_image = $this->get_logo_image();
		return $context;
	}
/**
 *	Gather and return necessary widgets.
 *	@return		array		Array of widgets for use in the templates
 */
	private function get_widgets(){
//		\Timber::get_widgets('widget_name');
	}
/**
 *	Gather and return the header image.
 *	Possibly need to refactor this so that we inject the header image into the site
 *	sub-object of the Twig object
 *	@return		string
 */
	private function get_header_image(){
		if(empty($this->header_image)){
			return new \Timber\Image(\get_custom_header()->url);
		}
	}
/**
 *	Gather and return the theme logo customization
 *	@return		\Timber\Image
 */
	private function get_logo_image(){
		if(empty($this->logo_image)){
			$logo = \wp_get_attachment_image_src(\get_theme_mod('custom_logo'), 'full');
			return new \Timber\Image($logo[0]);
		}
	}
/**
 *	Figure out if there's any additional data to gather, then gather it.
 *	For instance, if the template name is 'secondary-navigation', we'd know that
 *	that page needs a secondary navigation. So this method calls $this->add_secondary_navigation_additional_data()
 *	and returns the result in the proper place in the context array.
 *	@param	array	$context		Timber context array
 *	@return	array
 */
	private function get_additional_page_data(array $context){
		$fn = lcwords(str_replace(array('-',' '), '_', $context['template']));
		$fn = "get".str_replace(array(' ','.twig','.html','.php'), '', $fn)."_additional_data";
		if(method_exists($this, $fn)){
			$context = $this->$fn($context);
		}
		return $context;
	}

/**
 *	Gether home page specific data here for rendering in the template.
 *	@param		array		$context		Context to be passed to Twig
 *	@return		array
 */
	private function get_home_additional_data(array $context){
		return $context;
	}

/**
 *	Get the pagination for the current page of the site, if applicable.
 *	@return	array
 */
	private function add_pagination(){
		$pg = \Timber::get_pagination();
		return $pg;
	}

/**
 *	Figure out which template we're supposed to be rendering for the page in
 *	question. As far as I can tell, when a page is using the 'Default Template'
 *	(or no template is selected on the edit page screen), the returned value of
 *	\get_page_template_slug() will be empty.
 *	We check to make sure there's a post to deal with - if not, it's a non-existent
 *	page that's being requested so we can safely return the 404 template. If there
 *	is a post but the \get_page_template_slug() return value is empty, we assign
 *	the page the base index.twig template. Otherwise, we make sure the physical
 *	view file that renders the assigned template exists in the views/ directory
 *	and return that value if it does. If the view file doesn't exist, we're going
 *	to return the 404 template again. I think this is safer than trying to return
 *	the base index view template, but I could be argued out of that opinion if
 *	necessary.
 *
 *	@param	array	$post		Single-value indexed array of post (or empty if
 *				the routing request should result in a 404 page)
 *	@return	string			View template file name
 */
	private function get_render_template(\ArrayObject $post){
		$post = isset($post[0]) ? $post[0] : null;
		$tpl = !empty($post) ? \get_page_template_slug($post->ID) : null;
		if(empty($tpl)){
			$tpl = 'index';
		}
		if(\is_404()){
			$tpl = '404';
		}
		if(\is_front_page()){
			$tpl = 'home';
		}
		if(\is_home() || \is_archive() || \is_search()){
			$tpl = 'archive';
		}
		if(\is_single()){
			$tpl = "index";
		}
		$tpl = str_replace(array('.','/',' ','php','"',"'"), '', $tpl);
		if(!file_exists($this->theme_dir.'views/'.$tpl.$this->file_ext) || !ctype_alnum(str_replace(array('-','_'), '', $tpl))){
			return '404'.$this->file_ext;
		}
		return \esc_attr($tpl).$this->file_ext;
	}

/**
 *	Converts the WordPress menus into TimberMenu objects.
 *	@return	array
 */
	private function get_registered_menus(){
		$menus = \get_registered_nav_menus();
		$ret = array();
		foreach($menus as $m=>$name){
			$ret[$m] = new \Timber\Menu($m);
		}
		return $ret;
	}

/**
 *	I'm a little disappointed that Timber disables Twig's
 *	auto-escaping functionality, but given WP's fairly inconsistent
 *	escaping at compile time rather than output, it's understandable.
 *	Attempting to circumvent the issue by using \wp_kses_post() - it's
 *	a give and take. There could be issues with attempting to iframe
 *	youtube or vimeo videos, for instance.
 *	@see		\wp_kses_post()
 *	@see		https://github.com/timber/timber/issues/1557
 *	@param		string		$content		Object content
 *	@return		string
 */
	public function auto_escape($content){
		return \wp_make_content_images_responsive(\wp_kses_post($content));
	}
}