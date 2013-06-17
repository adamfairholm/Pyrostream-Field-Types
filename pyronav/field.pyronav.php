<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * PyroStreams PyroNav field type
 *
 * @package   PyroStreams navigation Field Type
 * @author    Tim Reynolds
 * @copyright Copyright (c) 2011
 * @link      timreynolds.me
 */

class Field_pyronav
{
	/**
	 * Required variables
	 */
	public $field_type_name = 'PyroNav';
	public $field_type_slug = 'pyronav';
	public $db_col_type     = 'int';
	
	/**
	 * Custom Parameters 
	 */
	public $custom_parameters	= array('navigation_group');
	
	public function __construct()
	{
		$this->CI = get_instance();
	}
	
	/**
	 * Output form input
	 * Used when adding entry to stream
	 * 
	 * @param  array
	 * @param  array
	 * @return string
	 */
	public function form_output($data)
	{
		$options = array();
		$nav     = array();

		$this->CI->load->model('navigation/navigation_m');

		if ( ! $data['custom']['navigation_group']) {
			return null;
		}

		$nav = $this->CI->navigation_m->get_link_tree($data['custom']['navigation_group']);

		foreach ($nav as $item) {
			$options[$item['id']] = $item['title'];
		}

		return form_dropdown($data['form_slug'], $options);
	}
	
	/**
	* Output from the form in the view file.
	* 
	* @param  array
	* @param  array
	* @return string
	*/
	public function pre_output($input, $data)
	{
		$attributes = array();
		$nav_item = '';

		$this->CI->load->model('navigation/navigation_m');
		$nav_item = $this->CI->navigation_m->get_link($input);

		if ( ! empty($nav_item))
		{
			// Create the url for the link type
			$nav_item   = $this->make_url($nav_item);
			$attributes = $this->generate_nav_attributes($nav_item);

			return anchor($nav_item->url, $nav_item->title, $attributes);
		
		} else {
			return anchor(site_url('admin/navigation'),'This link no longer exists');
		}
	}
	
	/**
	* Process before outputting for the plugin
	*
	* This creates an array of data to be merged with the
	* tag array so relationship data can be called with
	* a {field.column} syntax
	*
	* @param  string
	* @param  string
	* @param  array
	* @return array
	*/
	public function pre_output_plugin($input, $params)
	{
		if ( ! $input) {
			return array();
		}

		$nav_data   = array();
		$attributes = array();
		$nav_item   = '';

		$this->CI->load->model('navigation/navigation_m');
		$nav_item   = $this->CI->navigation_m->get_link($input);

		if ( ! empty($nav_item))
		{
			// Create the url for the link type
			$nav_item   = $this->make_url($nav_item);
			//$attributes = $this->generate_nav_attributes($nav_item);

			$nav_data['id']                  = $nav_item->id;
			$nav_data['title']               = $nav_item->title;
			$nav_data['parent']              = $nav_item->parent;
			$nav_data['has_kids']            = $nav_item->has_kids;
			$nav_data['link_type']           = $nav_item->link_type;
			$nav_data['page_id']             = $nav_item->page_id;
			$nav_data['module_name']         = $nav_item->module_name;
			$nav_data['url']                 = $nav_item->url;
			$nav_data['uri']                 = $nav_item->uri;
			$nav_data['navigation_group_id'] = $nav_item->navigation_group_id;
			$nav_data['position']            = $nav_item->position;
			$nav_data['target']              = $nav_item->target;
			$nav_data['class']               = $nav_item->class;
		}

		return $nav_data;
	}
	
	/**
	* Param Allowed Types
	*
	* @param  string
	* @return string
	*/
	public function param_navigation_group($value = '')
	{ 
	  $options          = array();
	  $navigation_group = array();
	  
	  $this->CI->load->model('navigation/navigation_m');
	  $navigation_group = $this->CI->navigation_m->get_groups();
	  
	  $options[0]       = "None";
		foreach ($navigation_group as $item)
		{
			$options[$item->id] = $item->title;
		}

		return form_dropdown('navigation_group',$options,$value);
	} 
	    
	/**
	* Determine the url from the link_type 
	*
	* @param  object
	* @return array
	*/
	private function make_url($nav_item)
	{
		switch ($nav_item->link_type) {
			case 'uri':
			  $nav_item->url = site_url($nav_item->url);
			break;
			case 'module':
			  $nav_item->url = site_url($nav_item->module_name);
			break;
			case 'page':
			  $this->CI->load->model('pages/page_m', false);
			  $page = $this->CI->page_m->get($nav_item->page_id);
			  $nav_item->url = $page ? site_url($nav_item->uri) : '';
			break;
		}
	  
	  return $nav_item;
	}
	
	/**
	* Process navigation attributes 
	*
	* @param  object
	* @return array
	*/
	private function generate_nav_attributes( $nav_item )
	{
	  $attributes           = array();
	  $attributes['target'] = $nav_item->target;
		$attributes['class']  = $nav_item->class;
		
	  return $attributes;
	}

}