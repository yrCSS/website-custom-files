<?php

# From https://piszek.com/2022/08/15/wordpress-github-markdown/#
wp_embed_register_handler(
	'github_readme_md',
	'&https?:\/\/github\.com\/([a-zA-Z-_0-9/]+)\/([a-zA-Z]+)\.md&i',
	__NAMESPACE__ . '\artpi_github_markdown_handler'
);

function artpi_github_markdown_handler( $matches, $attr, $url, $rawattr ) {
	$url = str_replace(
		[ 'github.com', '/blob' ],
		[ 'raw.githubusercontent.com', '' ],
		$matches[0]
	);
	$transient_key = 'gh_' . md5( $url );
	$content = get_transient( $transient_key );
	if ( ! $content ) {
		$request = wp_remote_get( $url );
		if ( is_wp_error( $request ) ) {
			return false;
		}
		$content = wp_remote_retrieve_body( $request );
		if( ! $content ) {
			return false;
		}
		require_once __DIR__ . '/Parsedown.php'; // You will need to download Parsedown https://github.com/erusev/parsedown
		$md_parser = new \Parsedown();
		$content = $md_parser->text( $content );
		if( ! $content ) {
			return false;
		}
		$content = "<div class='github_readme_md'>$content</div>";
		set_transient( $transient_key, $content, 3600 );
	}
	return apply_filters( 'embed_github_readme_md', $content, $matches, $attr, $url, $rawattr );
}
