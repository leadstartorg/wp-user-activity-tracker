<?php get_header(); ?>	
<?php
/*
	$user = wp_get_current_user();
	$author = (isset($_GET['author_name'])) ? get_user_by('slug', $author_name) : get_userdata(intval($author));
	$author_url = get_author_posts_url($author->ID);
	$author_id = get_the_author_meta('ID');
	$cover_image = get_the_author_meta('cover_image', $author_id);
	$cover_image_url = get_user_cover_image($author_id);
	$cover_position = get_the_author_meta('cover_position', $author_id) ?: '50% 50%';
	$current_user = wp_get_current_user();
	*/
?>
<?php
// Replace the existing code block with this permission-based version:
$user = wp_get_current_user();

// Function to safely get author meta
function safe_get_author_meta($meta_key, $author_id) {
	$author = get_userdata($author_id);
	if ($author && $author->has_cap('edit_posts')) {
		return get_the_author_meta($meta_key, $author_id);
	}
	return '';
}

// Function to get author data based on permissions
function get_author_data($author) {
	if (!$author) {
		return array('id' => 0, 'url' => '');
	}

	$author_id = $author->ID;

	// Check if the author has permission to edit posts
	if ($author->has_cap('edit_posts')) {
		$author_url = get_author_posts_url($author_id);
	} else {
		$author_url = '';
	//	error_log("Author found without edit_posts capability: " . $author_id . " - Roles: " . implode(', ', (array)$author->roles));
	}

	return array('id' => $author_id, 'url' => $author_url);
}

// Check if Co-Authors Plus is active and use its functions if available
if (function_exists('get_coauthors')) {
	$coauthors = get_coauthors();
	$author = !empty($coauthors) ? $coauthors[0] : null;

	if ($author) {
		if (isset($author->type) && $author->type === 'guest-author') {
			// Handle guest author
			$author_data = array('id' => $author->ID, 'url' => $author->link);
		} else {
			// Handle regular author
			$author_data = get_author_data($author);
		}
	} else {
		// Fallback if no author is found
		$author_data = array('id' => 0, 'url' => '');
	//	error_log("No author found for the current post.");
	}
} else {
	// Fallback to WordPress default author handling
	$author = (isset($_GET['author_name'])) ? get_user_by('slug', $author_name) : get_userdata(intval($author));
	$author_data = get_author_data($author);

	if ($author_data['id'] === 0) {
	//	error_log("No valid WordPress author found.");
	}
}

$author_id = $author_data['id'];
$author_url = $author_data['url'];

$cover_image = safe_get_author_meta('cover_image', $author_id);
$cover_image_url = '';
if (function_exists('get_user_cover_image')) {
	$author = get_userdata($author_id);
	if ($author && $author->has_cap('edit_posts')) {
		$cover_image_url = get_user_cover_image($author_id);
	}
}
$cover_position = safe_get_author_meta('cover_position', $author_id) ?: '50% 50%';
$current_user = wp_get_current_user();
?>
<div id="userProfile">
	<div class="container-fluid">
		<div class="row">
			<div id="secondary" class="col-xl-2 d-none d-xl-flex widget-area">
				<div class="sidebar" role="complementary">
					<span class="logo">S</span>
					<a class="logo-expand" href="#">Profile</a>
					<div class="side-wrapper">
						<div class="side-title">MENU</div>
						<div class="side-menu">
							<div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
								<a class="sidebar-link nav-link active" id="v-pills-posts-tab" data-bs-toggle="pill" data-bs-target="#v-pills-posts" type="button" role="tab" aria-controls="v-pills-posts" aria-selected="true"><i class="icon-WS5sYY01"></i>Posts</a>
								<a class="sidebar-link nav-link" id="v-pills-likes-tab" data-bs-toggle="pill" data-bs-target="#v-pills-likes" type="button" role="tab" aria-controls="v-pills-likes" aria-selected="false"><em class="fa fa-heart"></em> Likes</a>
								<a class="sidebar-link nav-link" id="v-pills-bookmarks-tab" data-bs-toggle="pill" data-bs-target="#v-pills-bookmarks" type="button" role="tab" aria-controls="v-pills-bookmarks" aria-selected="false"><i class="icon-UNcV6P01"></i> Bookmarks</a>
								<a class="sidebar-link nav-link" id="v-pills-comments-tab" data-bs-toggle="pill" data-bs-target="#v-pills-comments" type="button" role="tab" aria-controls="v-pills-comments" aria-selected="false"><i class="icon-kdCd2p01"></i> Comments</a>
							</div>
						</div>
					</div>
				<div class="side-wrapper">
					<div class="side-title">GET INVOLVED</div>
						<div class="side-menu">
							<a class="sidebar-link" href="/contribute" target="_blank">
							<i class="icon-home-noun-code-1953831"></i>
							Contribute
							</a>
							<a class="sidebar-link" href="/affiliates" target="_blank">
							<em class="fa fa-users"></em>
							Become an Affiliate
							</a>
							<a class="sidebar-link" href="/donate" target="_blank">
							<i class="icon-home-noun-code-4436295"></i>
							Donate
							</a>
							<a class="sidebar-link" href="https://support.leadstart.org" target="_blank">
							<em class="fa fa-question-circle"></em>
							Help
							</a>
						</div><!-- #side-menu -->
					</div><!-- #side-title -->
				</div><!-- #side-wrapper -->
			</div><!-- #secondary -->
			<div id="primary" class="content-area col-lg-12 col-xl-10 wrapper">
				<main id="main" class="site-main" role="main">
					<header class="row">
						<div class="page-header col-12">
							<div class="author-cover-container">
								<div class="profile-cover author-cover-image bg-image mb-4 py-sm-3 container-fluid">
									<div class="row g-0">
										<div class="col-12">
											<div class="d-flex bg-white p-3">
											<div class="position-relative col-md-2 me-1">
												<a href="<?php echo esc_url($author_url); ?>">
													<?php echo get_avatar($author->ID, 200, '', esc_attr($author->display_name), array('class' => 'rounded-2 object-cover rounded-circle')); ?>
												</a>
												<div class="position-absolute bottom-0 end-0 me-n1 mb-1 rounded-circle border border-4 border-white bg-success d-none d-md-block" title="User is online">
													<?php echo user_availability_link($author_id); ?>
												</div>
											</div>
											<div class="d-flex flex-column px-3">
												<div class="d-flex align-items-center mb-1">
													<a href="my-account" target="_blank" class="text-decoration-none">
														<h2 class="fs-5 fw-semibold mb-0"><?php echo esc_html($author->display_name); ?></h2>
													</a>
													<svg class="ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#ff3258" viewBox="0 0 24 24">
													<path d="M23,12L20.56,9.22L20.9,5.54L17.29,4.72L15.4,1.54L12,3L8.6,1.54L6.71,4.72L3.1,5.53L3.44,9.21L1,12L3.44,14.78L3.1,18.47L6.71,19.29L8.6,22.47L12,21L15.4,22.46L17.29,19.28L20.9,18.46L20.56,14.78L23,12M10,17L6,13L7.41,11.59L10,14.17L16.59,7.58L18,9L10,17Z"></path>
													</svg>
												</div>
												<div class="d-flex flex-wrap gap-2 mb-2">
													<div class="d-flex align-items-center">
														<svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="gray" viewBox="0 0 24 24">
															<path d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M7.07,18.28C7.5,17.38 10.12,16.5 12,16.5C13.88,16.5 16.5,17.38 16.93,18.28C15.57,19.36 13.86,20 12,20C10.14,20 8.43,19.36 7.07,18.28M18.36,16.83C16.93,15.09 13.46,14.5 12,14.5C10.54,14.5 7.07,15.09 5.64,16.83C4.62,15.5 4,13.82 4,12C4,7.59 7.59,4 12,4C16.41,4 20,7.59 20,12C20,13.82 19.38,15.5 18.36,16.83M12,6C10.06,6 8.5,7.56 8.5,9.5C8.5,11.44 10.06,13 12,13C13.94,13 15.5,11.44 15.5,9.5C15.5,7.56 13.94,6 12,6M12,11A1.5,1.5 0 0,1 10.5,9.5A1.5,1.5 0 0,1 12,8A1.5,1.5 0 0,1 13.5,9.5A1.5,1.5 0 0,1 12,11Z"></path>
														</svg>
														<span class="text-muted small">
															<?php
																$author_id = $author->ID;
																$user_info = get_userdata($author_id);
																
																if (!empty($user_info->roles)) {
																	$author_role = $user_info->roles[0]; // Assuming the author has one role
																	echo '' . $author_role;
																} else {
																	echo 'Subscriber';
																}
															?>
														</span>
													</div>
													<div class="d-flex align-items-center">
													<svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="gray" viewBox="0 0 24 24">
														<path d="M12,6.5A2.5,2.5 0 0,1 14.5,9A2.5,2.5 0 0,1 12,11.5A2.5,2.5 0 0,1 9.5,9A2.5,2.5 0 0,1 12,6.5M12,2A7,7 0 0,1 19,9C19,14.25 12,22 12,22C12,22 5,14.25 5,9A7,7 0 0,1 12,2M12,4A5,5 0 0,0 7,9C7,10 7,12 12,18.71C17,12 17,10 17,9A5,5 0 0,0 12,4Z"></path>
													</svg>
													<span class="text-muted small">Istanbul</span>
													</div>
													<div class="d-flex align-items-center">
														<svg class="me-2" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="gray" viewBox="0 0 24 24">
															<path d="M12,15C12.81,15 13.5,14.7 14.11,14.11C14.7,13.5 15,12.81 15,12C15,11.19 14.7,10.5 14.11,9.89C13.5,9.3 12.81,9 12,9C11.19,9 10.5,9.3 9.89,9.89C9.3,10.5 9,11.19 9,12C9,12.81 9.3,13.5 9.89,14.11C10.5,14.7 11.19,15 12,15M12,2C14.75,2 17.1,3 19.05,4.95C21,6.9 22,9.25 22,12V13.45C22,14.45 21.65,15.321,16C20.3,16.67 19.5,17 18.5,17C17.3,17 16.31,16.5 15.56,15.5C14.56,16.5 13.38,17 12,17C10.63,17 9.45,16.5 8.46,15.54C7.5,14.55 7,13.38 7,12C7,10.63 7.5,9.45 8.46,8.46C9.45,7.5 10.63,7 12,7C13.38,7 14.55,7.5 15.54,8.46C16.5,9.45 17,10.63 17,12V13.45C17,13.86 17.16,14.22 17.46,14.53C17.76,14.84 18.11,15 18.5,15C18.92,15 19.27,14.84 19.57,14.53C19.87,14.22 20,13.86 20,13.45V12C20,9.81 19.23,7.93 17.65,6.35C16.07,4.77 14.19,4 12,4C9.81,4 7.93,4.77 6.35,6.35C4.77,7.93 4,9.81 4,12C4,14.19 4.77,16.07 6.35,17.65C7.93,19.23 9.81,20 12,20H17V22H12C9.25,22 6.9,21 4.95,19.05C3,17.1 2,14.75 2,12C2,9.25 3,6.9 4.95,4.95C6.9,3 9.25,2 12,2Z"></path>
														</svg>
														<span class="text-muted small">who@am.i</span>
													</div>
												</div>
												<div class="d-flex mt-2 gap-3">
													<a href="#" class="d-flex flex-column align-items-center justify-content-center border border-dashed border-secondary-subtle rounded-3 p-3 text-decoration-none transition-all hover:border-secondary">
														<div class="d-flex align-items-center mb-2">
															<svg class="me-2 text-secondary-emphasis" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
															<path d="M12,23A1,1 0 0,1 11,22V19H7A2,2 0 0,1 5,17V7A2,2 0 0,1 7,5H21A2,2 0 0,1 23,7V17A2,2 0 0,1 21,19H16.9L13.2,22.71C13,22.89 12.76,23 12.5,23H12M13,17V20.08L16.08,17H21V7H7V17H13M3,15H1V3A2,2 0 0,1 3,1H19V3H3V15M9,9H19V11H9V9M9,13H17V15H9V13Z"></path>
															</svg>
															<span class="fw-bold text-body">4.6K</span>
														</div>
														<span class="text-muted small">Followers</span>
													</a>
													<a href="#" class="d-flex flex-column align-items-center justify-content-center border border-dashed border-secondary-subtle rounded-3 p-3 text-decoration-none transition-all hover:border-secondary">
														<div class="d-flex align-items-center mb-2">
															<svg class="me-2 text-secondary-emphasis" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
															<path d="M2.5 19.6L3.8 20.2V11.2L1.4 17C1 18.1 1.5 19.2 2.5 19.6M15.2 4.8L20.2 16.8L12.9 19.8L7.9 7.9V7.8L15.2 4.8M15.3 2.8C15 2.8 14.8 2.8 14.5 2.9L7.1 6C6.4 6.3 5.9 7 5.9 7.8C5.9 8 5.9 8.3 6 8.6L11 20.5C11.3 21.3 12.0 21.7 12.8 21.7C13.1 21.7 13.3 21.7 13.6 21.6L21 18.5C22 18.1 22.5 16.9 22.1 15.9L17.1 4C16.8 3.2 16 2.8 15.3 2.8M10.5 9.9C9.9 9.9 9.5 9.5 9.5 8.9S9.9 7.9 10.5 7.9C11.1 7.9 11.5 8.4 11.5 8.9S11.1 9.9 10.5 9.9M5.9 19.8C5.9 20.9 6.8 21.8 7.9 21.8H9.3L5.9 13.5V19.8Z"></path>
															</svg>
															<span class="fw-bold text-body">45</span>
														</div>
														<span class="text-muted small">Submissions</span>
													</a>
													<a href="#" class="d-flex flex-column align-items-center justify-content-center border border-dashed border-secondary-subtle rounded-3 p-3 text-decoration-none transition-all hover:border-secondary">
														<div class="d-flex align-items-center mb-2">
															<svg class="me-2 text-secondary-emphasis" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
															<path d="M5.68,19.74C7.16,20.95 9,21.75 11,21.95V19.93C9.54,19.75 8.21,19.17 7.1,18.31M13,19.93V21.95C15,21.75 16.84,20.95 18.32,19.74L16.89,18.31C15.79,19.17 14.4.46,19.75 13,19.93M18.31,16.9L19.74,18.33C20.95,16.85 21.75,15 21.95,13H19.93C19.75,14.46 19.17,15.79 18.31,16.9M15,12A3,3 0 0,0 12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12M4.07,13H2.05C2.25,15 3.05,16.84 4.26,18.32L5.69,16.89C4.83,15.79 4.25,14.46 4.07,13M5.69,7.1L4.26,5.68C3.05,7.16 2.25,9 2.05,11H4.07C4.25,9.54 4.83,8.21 5.69,7.1M19.93,11H21.95C21.75,9 20.95,7.16 19.74,5.68L18.31,7.1C19.17,8.21 19.75,9.54 19.93,11M18.32,4.26C16.84,3.05 15,2.25 13,2.05V4.07C14.46,4.25 15.79,4.83 16.9,5.69M11,4.07V2.05C9,2.25 7.16,3.05 5.68,4.26L7.1,5.69C8.21,4.83 9.54,4.25 11,4.07Z"></path>
															</svg>
															<span class="fw-bold text-body">120K</span>
														</div>
														<span class="text-muted small">Downloads</span>
													</a>
												</div>
											</div>
											<div class="d-flex flex-column ms-auto">
											<div class="d-flex gap-2">
												<button class="btn btn-primary btn-sm d-flex align-items-center pe-3">
												<a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
													<path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z"></path>
												</svg>
												Follow</a>
												</button>
												<button class="btn btn-outline-secondary btn-sm">
												<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
													<path d="M12,16A2,2 0 0,1 14,18A2,2 0 0,1 12,20A2,2 0 0,1 10,18A2,2 0 0,1 12,16M12,10A2,2 0 0,1 14,12A2,2 0 0,1 12,14A2,2 0 0,1 10,12A2,2 0 0,1 12,10M12,4A2,2 0 0,1 14,6A2,2 0 0,1 12,8A2,2 0 0,1 10,6A2,2 0 0,1 12,4Z"></path>
												</svg>
												</button>
											</div>
											</div>
										</div>
									</div>
								</div><!-- author-cover-image -->
							</div><!-- author-cover-container -->
							<div class="profile-info px-2">
								<div class="profile-block">
									<h3 class="bio px-5">Bio</h3>
									<p class="text-muted px-5"><?php echo wp_kses_post(mb_strimwidth($author->user_description, 0, 300, '...')); ?></p>
									<div class="profile block pt-4 p-0">
										<ul class="profile-options horizontal-list nav nav-pills" id="v-pills-tab" role="tablist">
											<li>
												<a class="comments p-2" id="v-pills-comments-tab" data-bs-toggle="pill" data-bs-target="#v-pills-comments" type="button" role="tab" aria-controls="v-pills-comments">
													<p><em class="fa fa-comment icon"></em></p>
													<p>
														<?php 
															$comments_count = get_comments(array(
															'user_id' => $author->ID,
															'status'  => 'approve',  // Ensures only approved comments are counted
															'count'   => true
														));

														echo esc_html($comments_count);
														echo '<br /><small class="text-center">Comments</small>';
														?>
													</p>
												</a>
											</li>
											<li>
												<a class="bookmarks p-2" id="v-pills-bookmarks-tab" data-bs-toggle="pill" data-bs-target="#v-pills-bookmarks" type="button" role="tab" aria-controls="v-pills-bookmarks">
													<p><em class="fa fa-bookmark icon"></em></p>
													<p>
													<?php
														// Retrieve bookmarks
														$book_types = get_post_types(array('public' => true));
														$bookmark_args = array(
															'post_type' => $book_types,
															'meta_query' => array(
																array(
																	'key' => '_user_bookmarked',
																	'value' => $author->ID,
																	'compare' => 'LIKE'
																)
															)
														);
														$bookmark_query = new WP_Query($bookmark_args);
														echo esc_html($bookmark_query->found_posts);
														wp_reset_postdata();
														echo '<br /><small class="text-center">Bookmarks</small>'; 
													?>
													</p>
												</a>
											</li>
											<li>
												<a class="likes p-2" id="v-pills-likes-tab" data-bs-toggle="pill" data-bs-target="#v-pills-likes" type="button" role="tab" aria-controls="v-pills-likes">
													<p><em class="fa fa-heart icon"></em></p>
													<p>
													<?php
														// Retrieve likes
														$like_types = get_post_types(array('public' => true));
														$like_args = array(
															'post_type' => $like_types,
															'meta_query' => array(
																array(
																	'key' => '_user_liked',
																	'value' => $author->ID,
																	'compare' => 'LIKE'
																)
															)
														);
														$like_query = new WP_Query($like_args);
														echo esc_html($like_query->found_posts);
														wp_reset_postdata();
														echo '<br /><small class="text-center">Likes</small>'; 
													?>
													</p>
												</a>
											</li>
										</ul>
									</div><!-- profile-block inner -->
								</div><!-- profile-block 1 -->
								<div class="profile-block d-md-flex d-none d-md-flex">
									<h3 class="px-5"><em class="fa fa-history" style="color: #ff3258; font-size: 1rem; padding-right: 10px; margin-top: px;"></em> Recent Activity</h3>
									<?php echo do_shortcode('[uat_combined_data user_id="'.$author_id.'"]'); ?>
									<div class="list-group list-group-flush hidden">
										<a href="#" class="list-group-item list-group-item-action active d-inline-flex ps-5 pe-4" aria-current="true">
											<div class="w-20 justify-content-center">
												<img alt="Jessica" src="https://secure.gravatar.com/avatar/a9bb6dd33bbaefd644fcbe5744bfb8bd?s=150&amp;d=retro&amp;r=g" srcset="https://secure.gravatar.com/avatar/a9bb6dd33bbaefd644fcbe5744bfb8bd?s=300&amp;d=retro&amp;r=g 2x" class="avatar avatar-150 photo rounded-circle" height="150" width="150" decoding="async">
												</div>
												<div class="w-80"><small class="" style="white-space: nowrap;text-align:right">3 days ago</small><h5 class="my-1" style="color: white;">List group item heading</h5><div class="d-flex w-100 justify-content-between">
												
												
												</div>
												<p class="mb-1">Some placeholder content in a paragraph.</p>
												<small>And some small print.</small>
											</div>
										</a>
										<a href="#" class="list-group-item list-group-item-action disabled px-5">
											<div class="d-flex w-100 justify-content-between">
											<h5 class="mb-1">List group item heading</h5>
											<small class="text-body-secondary">3 days ago</small>
											</div>
											<p class="mb-1">Some placeholder content in a paragraph.</p>
											<small class="text-body-secondary">And some muted small print.</small>
										</a>
										<a href="#" class="list-group-item list-group-item-action px-5">
											<div class="d-flex w-100 justify-content-between">
											<h5 class="mb-1">List group item heading</h5>
											<p class="badge rounded-pill" style="margin-block-end: 3em;">3 days ago</p>
											</div>
											<p class="mb-1">Some placeholder content in a paragraph.</p>
											<small class="text-body-secondary">And some muted small print.</small>
										</a>
									</div><!-- list-group -->
									<div class="weather hidden">
										<div class="weather block clear p-0">
											<?php echo do_shortcode('[display_weather]'); ?>
										</div>
									</div>
								</div><!-- profile-block 2 -->
							</div><!-- profile-info -->
						</div><!-- page-header -->
					</header><!-- row -->
					<section class="row">
						<div class="main-container col-12">
							<div class="tab-content" id="v-pills-tabContent">
								<div class="tab-pane fade show active" id="v-pills-posts" role="tabpanel" aria-labelledby="v-pills-posts-tab">
									<div class="small-header d-none">Recent Posts</div>
									<?php 
									global $post_query;				

									$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
									$post_args = array(	  	
										'post_type' => 'post',
										'orderby' => 'date',
										'order' => 'desc',
										'posts_per_page'=> 8,
										'post_status' => 'publish',
										'paged' => $paged,
									);
									
									$post_query = new WP_Query( $post_args );
									if ($post_query->have_posts()) : ?>
									<div class="scroll-content">
									<div class="row pb-5 px-2">
									<?php
										while ($post_query->have_posts()) : $post_query->the_post(); 

											get_template_part( 'template-parts/content', 'archive' );

										endwhile;

											wp_reset_postdata();

										else:

											echo 'No blog posts were found';

										endif; 
									?>
									</div><!-- row -->
									</div><!-- scroll-content -->
								</div><!-- tab-pane -->
								<div class="tab-pane fade" id="v-pills-likes" role="tabpanel" aria-labelledby="v-pills-likes-tab">
									<div class="small-header d-none">Recently Liked</div>
									<?php 
									global $like_query;				

									$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
									$types = get_post_types( array( 'public' => true ) );
									$like_args = array(	  	
										'post_type' => $types,
										'orderby' => 'date',
										'order' => 'desc',
										'posts_per_page'=> 8,
										'post_status' => 'publish',
										'paged' => $paged,
										'meta_query' => array (
											array (
											'key' => '_user_liked',
											'value' => $author->ID,
											'compare' => 'LIKE'
											)
										));
									
									$like_query = new WP_Query( $like_args );
									if ($like_query->have_posts()) : ?>
									<div class="text-start">
										<!-- <h2 class="home-title">
											<?php echo esc_html__( 'Recently Liked Posts', 'leadstart' ); ?>
										</h2> -->
									</div>
									<div class="scroll-content">
									<div class="row pb-5 px-2">
									<?php
										while ($like_query->have_posts()) : $like_query->the_post(); 

											get_template_part( 'template-parts/content', 'archive' );

										endwhile;

											wp_reset_postdata();

										else:

											echo 'No liked posts were found';

										endif; 
									?>
									</div><!-- row -->
									</div><!-- scroll-content -->
								</div><!-- tab-pane -->
								<div class="tab-pane fade" id="v-pills-bookmarks" role="tabpanel" aria-labelledby="v-pills-bookmarks-tab">
									<div class="small-header d-none">Recently Bookmarked</div>
									<?php 
									global $bookmark_query;				
									
									$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
									$types = get_post_types( array( 'public' => true ) );
									$bookmark_args = array(	  	
										'post_type' => $types,
										'orderby' => 'date',
										'order' => 'desc',
										'posts_per_page'=> 8,
										'post_status' => 'publish',
										'paged' => $paged,
										'meta_query' => array (
											array (
											'key' => '_user_bookmarked',
											'value' => $author->ID,
											'compare' => 'LIKE'
											)
										));
									

									$bookmark_query = new WP_Query( $bookmark_args );
									$current_user = get_current_user_id();
									if($current_user == $author->ID) {
										if ($bookmark_query->have_posts()) : ?>
											<div class="text-start">
												<!-- <h2 class="home-title">
													<?php echo esc_html__( 'Recently Bookmarked Posts', 'leadstart' ); ?>
												</h2> -->
											</div>
											<div class="scroll-content">
											<div class="row pb-5 px-2">
											<?php
												while ($bookmark_query->have_posts()) : $bookmark_query->the_post(); 

													get_template_part( 'template-parts/content', 'archive' );

												endwhile;

													wp_reset_postdata();

												else:

													echo 'No bookmarked posts were found';

												endif; 
											?>
											</div><!-- row -->
											</div><!-- scroll-content -->
										<?php } else {
										echo '<p class="text-center pt-5 m-3">Only saved posts are visible to the user.</p>';
									} ?>
								</div><!-- tab-pane -->
								<div class="tab-pane fade" id="v-pills-comments" role="tabpanel" aria-labelledby="v-pills-comments-tab">
    								<div class="small-header d-none">Recent Comments</div>
    								<div class="text-start d-none">
        								<h2 class="home-title"><?php echo esc_html__('Comments', 'leadstart'); ?></h2>
    								</div>
								</div><!-- tab-pane -->			
    							<div class="scroll-content">
									<div class="comments px-2">
										<div class="row d-flex justify-content-center">
											<div class="col-12">
												<?php
												$args = array(
													'status' => 'approve',
													'order' => 'DESC',
													'user_id' => $author->ID
												);
												$comments = get_comments($args);
												if ($comments) {
													foreach ($comments as $comment) :
														$gravatar_url = get_avatar_url($comment->comment_author_email, array('size' => 30));
														$is_profile_owner = (get_current_user_id() == $author->ID); // Check if the logged-in user is the profile owner 
												?>
												<div class="card p-4 mt-2 mb-3">
													<div class="d-flex justify-content-between align-items-center">
														<div class="user d-flex flex-row align-items-center">
															<img src="<?php echo esc_url($gravatar_url); ?>" width="30" class="user-img rounded-circle mr-2">
															<span>
																<small class="font-weight-bold text-primary"><?php echo esc_html($comment->comment_author); ?></small> 
															</span>
														</div>
														<small><?php echo human_time_diff(get_comment_time('U'), current_time('timestamp')) . ' ago'; ?></small>
													</div>
													<p class="py-4 pb-0 mb-2"><?php echo esc_html($comment->comment_content); ?></p>
													<div class="action d-flex justify-content-between mt-2 align-items-center">
														<?php if ($is_profile_owner || get_current_user_id() == $comment->user_id) : ?>
															<div class="reply pe-4">
																<small><a href="<?php echo esc_url(get_delete_comment_link($comment->comment_ID)); ?>" class="remove-comment"></a>Remove</a></small>
																<span></span>
																<small><a href="#reply" class="reply-comment" data-comment-id="<?php echo $comment->comment_ID; ?>">Reply</a></small>
															</div>
														<?php endif; ?>
														<div class="icons align-items-center">
															<em class="fa fa-check-circle check-icon text-primary"></em>
														</div>
													</div>
												</div>
												<?php
													endforeach;
												} else {
													echo '<p class="text-center pt-5 m-3">Comments are managed by <a href="https://disqus.com/leadstartorg" target="_blank">Disqus</a>.</p>';
												}
												?>
											</div>
										</div>
									</div>
    							</div>
							</div><!-- /tab-content -->
						</div><!-- /main-container -->	
					</section>
				</main><!-- #main -->
			</div> <!-- #primary -->
		</div> <!-- #row -->
	</div> <!-- #container-fluid -->
</div><!-- #userProfile -->
<?php get_footer(); ?>

