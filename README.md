# Auto Import Posts

A custom plugin to fetch and create posts from an Mockaroo API response.

## Configuration
Before using the plugin, please set the api key:

- Open wp-config.php file
- Add the following line: `define('API_KEY', 'api-key');`

## Usage
Use the [aip] shortcode to display imported posts.

Shortcode Attributes:
- title – Section title (default: "Articles")
- count – Number of posts to display (default: -1 for all)
- sort – Sorting method: date (default), title, rating
- ids – Comma-separated list of specific post IDs to include

Example:
[aip title="Articles" count="5" sort="rating"]

## Changelog

### 1.0
Initial release.

### 1.1 (planned)
Add admin page for generating shortcodes based on available post IDs and categories.

### 1.2 (planned)
Add custom metabox for editing rating and site link.
