<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD.'bos_insert_social_meta/config'.EXT;

$plugin_info = array(
	'pi_name'			=> INSERT_SOCIAL_META_NAME,
	'pi_version'		=> INSERT_SOCIAL_META_VERSION,
	'pi_author'			=> INSERT_SOCIAL_META_AUTHOR,
	'pi_author_url'		=> INSERT_SOCIAL_META_URL,
	'pi_description'	=> INSERT_SOCIAL_META_DESCRIPTION,
	'pi_usage'			=> Bos_insert_social_meta::usage()
);

class Bos_insert_social_meta
{
	var $return_data;

	function __construct()
	{
		// get global instance
		$this->EE =& get_instance();

		$output = '';
		$agent = $_SERVER['HTTP_USER_AGENT'];
		$url = full_url();
		$site = $this->EE->config->item('site_label');

		// do we have variables set?
		$title = $this->EE->TMPL->fetch_param('title') ? $this->EE->TMPL->fetch_param('title') : $site;
		$description = $this->EE->TMPL->fetch_param('description') ? $this->EE->TMPL->fetch_param('description') : '';
		$site = $this->EE->TMPL->fetch_param('site') ? $this->EE->TMPL->fetch_param('site') : $site;
		$url = $this->EE->TMPL->fetch_param('url') ? $this->EE->TMPL->fetch_param('url') : $url;
		$admins = $this->EE->TMPL->fetch_param('admins') ? $this->EE->TMPL->fetch_param('admins') : '';
		$image = $this->EE->TMPL->fetch_param('image') ? $this->EE->TMPL->fetch_param('image') : '';
		$width = $this->EE->TMPL->fetch_param('width') ? $this->EE->TMPL->fetch_param('width') : '';
		$twitter = $this->EE->TMPL->fetch_param('twitter') ? $this->EE->TMPL->fetch_param('twitter') : '';
		$viewable = $this->EE->TMPL->fetch_param('viewable') ? $this->EE->TMPL->fetch_param('viewable') : 'no';
		$seolite = $this->EE->TMPL->fetch_param('seolite') ? $this->EE->TMPL->fetch_param('seolite') : 'no';
		$entryid = $this->EE->TMPL->fetch_param('entryid') ? $this->EE->TMPL->fetch_param('entryid') : '0';
		$chars = $this->EE->TMPL->fetch_param('descchars') ? $this->EE->TMPL->fetch_param('descchars') : '300';

		if ($title == '') {
			exit;
		}
		$output = '';
		$facebook_meta = '';
		$twitter_meta = '';

		if ($seolite == 'yes')
		{
			$query = $this->EE->db->select('description')
	 					->from('exp_seolite_content')
						->where('entry_id', $entryid)
						->where('site_id', '1')
						->limit(1)
						->get();
			if (!empty($query->row('description')))
			{
				$description = $query->row('description');
			}
			else
			{
				$stripped_content = strip_tags($description);
				$description = (strlen($stripped_content) <= $chars ? $stripped_content : $this->_truncate_chars($stripped_content, $chars));
			}
		}
		//$description = strip_tags($description);

		//FACEBOOK META TAGS
		$facebook_meta .= ' <meta property="og:title" content="'. $title .'" />' . "\r\n";
		if ($description !='') { $facebook_meta .= ' <meta property="og:description" content="'. $description .'" />' . "\r\n"; }
		$facebook_meta .= ' <meta property="og:site_name" content="'. $site .'" />' . "\r\n";
		$facebook_meta .= ' <meta property="og:type" content="article" />' . "\r\n";
		$facebook_meta .= ' <meta property="og:url" content="'. $url .'" />' . "\r\n";
		if ($admins !='') { $facebook_meta .= ' <meta property="fb:admins" content="'. $admins .'" />' . "\r\n"; }
		if ($image !='') { $facebook_meta .= ' <meta property="og:image" content="'.$image.'" />' ."\r\n" . ' <meta property="og:image:type" content="image/jpeg" />' . "\r\n"; }
		if ($width !='') { $facebook_meta .= ' <meta property="og:image:width" content="'.$width.'" />' . "\r\n"; }

		//TWITTER META TAGS
		$twitter_meta .= ' <meta name="twitter:title" content="'. $title .'" />' . "\r\n";
		if ($description !='') { $twitter_meta .= ' <meta name="twitter:description" content="'. $description .'" />' . "\r\n"; }
		$twitter_meta .= ' <meta name="twitter:site" content="@'. $twitter .'" />' . "\r\n";
		$twitter_meta .= ' <meta name="twitter:creator" content="@'. $twitter .'" />' . "\r\n";
	  	$twitter_meta .= ' <meta name="twitter:card" content="summary_large_image" />' . "\r\n";
	  	if ($image !='') { $twitter_meta .= ' <meta name="twitter:image:src" content="'.$image.'" />' . "\r\n"; }

		if ($viewable == 'yes') {
			$output .= $facebook_meta;
			$output .= $twitter_meta;
		} else {
			if(stripos($agent, "facebookexternalhit") !== FALSE){ 
				$output .= $facebook_meta;
			}
			if(stripos($agent, "Twitterbot") !== FALSE){
				$output .= $twitter_meta;
			}
		}

		//send it back
		$this->return_data = $output;
	}

	//THE FOLLOWING FUNCTION COMES FROM EEHIVE HACKSAW by Brett DeWoody
	//http://www.ee-hive.com/add-ons/hacksaw
	//modified a bit to simplify it
	//I couldn't get Stash embeds to process the hacksaw code first before running the description field
	//So it was just easier to run the function directly

	function _truncate_chars($content, $limit) 
	{
		// Removing the below to see how it effect UTF-8. 
		$content = preg_replace('/\s+?(\S+)?$/', '', substr($content, 0, ($limit+1)));
		return $content;
	}

	//based on: http://snipplr.com/view.php?codeview&id=2734
	function full_url()
	{
	    $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
	    $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
	    $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
	    $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
	    return $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI'];
	}

	static function usage()
	{
	ob_start();
	?>
The BOS Insert Social Meta will insert the necessary Facebook Open Graph meta-data and
Twitter Cards meta-data only when the link is coming from those social sites,
saving your pageload for normal users.

-------------------------------------------------------------------------
Default Usage:
-------------------------------------------------------------------------
{exp:bos_insert_social_meta title="{title}" description="" image="" admins="123456789,987654321"}

- title is Required.

Default Options that can be changed:
- site: Default to the Site Name in your config file.
- url: Defaults to the current page URL.
- viewable: yes|no - if you want to see the meta tags in your source code, set to YES. Defaults to NO and the meta tags will only be seen by the social bots.

Options that default to blank
- description: Custom description field
- descchars: You can pass in a larger string and it will truncate to this number of characters. Defaults to 300.
- admins: Facebook admin ID numbers
- twitter: Twitter handle (leave off @ symbol)
- image: image to share
- width: width of image

SEO Lite Support
- seolite: yes|no - if you are using the SEO Lite Plugin and want to pull the custom description that can be set per entry, set this to yes
- entryid: you need to pass in the entry_id to lookup the entry in the SEO Lite table. 

-------------------------------------------------------------------------
All Parameaters Example:
-------------------------------------------------------------------------
{exp:bos_insert_social_meta site="{site_name}" title="{title}" description="{body}" descchars="300" image="{image}" width="300" admins="123456789,987654321" twitter="ellislab" seolite="yes" entryid="{entry_id}" viewable="yes"}

-------------------------------------------------------------------------
Test/Debug:
-------------------------------------------------------------------------
Facebook Test/Debug
Visit: https://developers.facebook.com/tools/debug/

Twitter Test/Debug
Visit: https://cards-dev.twitter.com/validator

	<?php
	$buffer = ob_get_contents();

	ob_end_clean();

	return $buffer;

  }
}

/* End of file pi.bos_insert_social_meta.php */
/* Location: ./system/expressionengine/third_party/bos_insert_social_meta/pi.bos_insert_social_meta.php */