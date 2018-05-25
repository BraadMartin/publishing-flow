# Publishing Flow #
**Contributors:** Braad  
**Donate link:** http://braadmartin.com/  
**Tags:** publishing, flow, required, fields, preview  
**Requires at least:** 4.5  
**Tested up to:** 4.6  
**Stable tag:** 1.1.3  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

Adds a Customizer-based publishing flow for ensuring posts are complete before publishing.

## Description ##

Have you ever wanted to ensure that certain fields always have a value before allowing a post to be published? Publishing Flow is for you.

Publishing Flow is a framework that allows you to define required and optional fields on posts, pages, and other CPTs. The fields can be primary fields on the post object (like title and content), meta fields (like a Featured Image), or a taxonomy term (like ensuring the post is in a category). Required and optional fields are highlighted on post edit screens and the Publish button gets replaced with a button that takes the user to a Customizer-based preview of the post with required and optional fields highlighted in place of the default Customizer controls.

By default only post title and content are required fields, but the plugin includes a number of filters that give you full control over which fields are defined as required and optional.

Example of defining required primary fields:

```
add_filter( 'publishing_flow_required_primary_fields', 'xxx_required_primary_fields', 10, 2 );
/**
 * Define required primary fields.
 *
 * @param   array   $fields     The array of primary fields.
 * @param   string  $post_type  The post type.
 *
 * @return  array               The updated array of primary fields.
 */
function xxx_required_primary_fields( $fields, $post_type ) {

	// Require an excerpt on posts and pages.
	if ( 'post' === $post_type || 'page' === $post_type ) {
		$fields['post_excerpt'] = array(
			'label'      => __( 'Post Excerpt', 'xxx' ),
			'has_value'  => __( 'The post has an excerpt', 'xxx' ),
			'no_value'   => __( 'The post is missing an excerpt', 'xxx' ),
			'show_value' => true,
		);
		// Other required primary fields can be defined here...
	}

	return $fields;
}
```

^You can see that there are 4 possible config values you can pass for each required field. The `label` key defines the field display label, the `has_value` and `no_value` keys define a bit of text to show if the field has or is missing a field value, and the `show_value` boolean will control whether the `has_value` label should show or the actual value of the field should show (useful for shorter fields like the excerpt, defaults to false).

Meta fields can be similarly defined as being required:

```
add_filter( 'publishing_flow_required_meta_fields', 'xxx_required_meta_fields', 10, 2 );
/**
 * Define required meta fields.
 *
 * @param   array   $fields     The array of meta fields.
 * @param   string  $post_type  The post type.
 *
 * @return  array               The updated array of meta fields.
 */
function xxx_required_meta_fields( $fields, $post_type ) {

	// Only on posts and pages.
	if ( 'post' === $post_type || 'page' === $post_type ) {
		$fields['_thumbnail_id'] = array(
			'label'     => __( 'Featured Image', 'xxx' ),
			'has_value' => __( 'The post has a featured image', 'xxx' ),
			'no_value'  => __( 'The post is missing a featured image', 'xxx' ),
		);
		// Other required meta fields can be defined here...
	}

	return $fields;
}
```

And taxonomies as well:

```
add_filter( 'publishing_flow_required_taxonomies', 'xxx_required_taxonomies', 10, 2 );
/**
 * Define required taxonomies.
 *
 * @param   array   $taxonomies  The array of taxonomies.
 * @param   string  $post_type   The post type.
 *
 * @return  array                The updated array of taxonomies.
 */
function xxx_required_taxonomies( $taxonomies, $post_type ) {

	// Only on posts and pages.
	if ( 'post' === $post_type || 'page' === $post_type ) {
		$taxonomies['category'] = array(
			'label'      => __( 'Category', 'xxx' ),
			'no_value'   => __( 'The post is missing a category', 'xxx' ),
			'show_value' => true,
		);
		// Other required taxonomies can be defined here...
	}

	return $taxonomies;
}
```

Sometimes you might have a group of meta fields where you want to require that at least one of them has a value. Publishing Flow supports this with nearly the same syntax as defining required/optional fields.

```
add_filter( 'publishing_flow_required_meta_field_groups', 'xxx_required_meta_field_groups', 10, 2 );
/**
 * Define required meta field groups.
 *
 * @param   array   $groups     The array of meta field groups.
 * @param   string  $post_type  The post type.
 *
 * @return  array               The updated array of meta field groups.
 */
function xxx_required_meta_field_groups( $groups, $post_type ) {

	// Only on posts and pages.
	if ( 'post' === $post_type || 'page' === $post_type ) {
		$groups['content_type'] = array(
			'label'      => __( 'Content Type', 'xxx' ),
			'meta_keys'  => array(
				'_meta_checkbox_navigate' => __( 'Navigate', 'xxx' ),
				'_meta_checkbox_learn'    => __( 'Learn', 'xxx' ),
				'_meta_checkbox_decide'   => __( 'Decide', 'xxx' ),
			),
			'no_value'   => __( 'The post is missing a content type', 'xxx' ),
			'show_value' => true,
		);
		// Other required meta key groups can be defined here...
	}

	return $groups;
}
```

All of these filters for defining required fields have counterparts that allow you to define optional fields, which will show among the list of required fields in a separate section but will not block publishing. The optional filters take an identical definition to the required fields but use different filter names:

```
publishing_flow_optional_primary_fields
publishing_flow_optional_meta_fields
publishing_flow_optional_meta_field_groups
publishing_flow_optional_taxonomies
```

When working locally you might wish to bypass the requirement checks in order to more easily publish test posts. This plugin supports defining a "development domain" that, when detected, will bypass the requirement checks, but otherwise show the same UI:

```
add_filter( 'publishing_flow_dev_domain', 'xxx_dev_domain' );
/**
 * Specify a dev domain to bypass the requirements check.
 *
 * @param   string  $domain  The default dev domain.
 *
 * @return  string           Our dev domain.
 */
function xxx_dev_domain( $domain ) {

	$domain = 'local.wordpress.dev';

	return $domain;
}
```

There are many more hooks available throughout this plugin to customize all the things. Here is a quick list:

```
publishing_flow_customizer_url
publishing_flow_data_array
publishing_flow_js_templates
publishing_flow_publish_success_template
publishing_flow_schedule_success_template
publishing_flow_publish_fail_template
publishing_flow_required_primary_fields
publishing_flow_optional_primary_fields
publishing_flow_required_meta_fields
publishing_flow_optional_meta_fields
publishing_flow_required_meta_field_groups
publishing_flow_optional_meta_field_groups
publishing_flow_required_taxonomies
publishing_flow_optional_taxonomies
```

## Installation ##

### Manual Installation ###

1. Upload the entire `/publishing-flow` directory to the `/wp-content/plugins/` directory.
1. Activate Publishing Flow through the 'Plugins' menu in WordPress.

### Better Installation ###

1. Go to Plugins > Add New in your WordPress admin and search for Publishing Flow.
1. Click Install.

## Frequently Asked Questions ##

### Can I specify a different default device than mobile? ###

Yes! The array of config data that gets passed into the Customizer has a filter on it when it is generated, and this filter can be used to change any of the config options. Here's an example of using this filter to change the default preview device to tablet:

```
add_filter( 'publishing_flow_data_array', 'xxx_data_array' );
/**
 * Change the default preview device to tablet.
 *
 * @param   array  $data  The default data array.
 *
 * @return  array         The updated data array.
 */
function xxx_data_array( $data ) {

	$data['defaultDevice'] = 'tablet';

	return $data;
}
```

## Changelog ##

### 1.1.3 ###
* Update user capability check to work correctly for custom post types.

### 1.1.2 ###
* Update styling of required fields toggle in the Publish box on Post edit screens to improve discoverability

### 1.1.1 ###
* Fix for compatibility issue with Edit Flow version 0.8.1 and older

### 1.1.0 ###
* Bugfix for incorrect publish date displaying in the preview
* Bugfix for sometimes incorrect publish date persisting after publishing
* Bugfix for posts with a publish date in the past showing as publishing "immediately"

### 1.0 ###
* Initial release.

## Upgrade Notice ##

### 1.1.3 ###
* Update user capability check to work correctly for custom post types.

### 1.1.2 ###
* Update styling of required fields toggle in the Publish box on Post edit screens to improve discoverability

### 1.1.1 ###
* Fix for compatibility issue with Edit Flow version 0.8.1 and older

### 1.1.0 ###
* Bugfix for incorrect publish date displaying in the preview
* Bugfix for sometimes incorrect publish date persisting after publishing
* Bugfix for posts with a publish date in the past showing as publishing "immediately"

### 1.0 ###
* Initial release.
