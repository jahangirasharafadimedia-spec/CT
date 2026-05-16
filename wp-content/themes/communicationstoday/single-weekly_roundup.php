<?php
// if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php')) {

//     /** Loads the WordPress Environment and Template */
//     require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');


// }
$id     = get_the_ID();
$post_id = (isset($_GET['post']) && $_GET['post']) ? absint($_GET['post']) : $id;
$fields  = get_field_objects($post_id);

if (! $fields) {
	echo 'Unable to view. Please try again.';
	return;
}
function getWeekdaya($date)
{
	return date('l', strtotime($date));
}
function limit_text($text, $limit)
{
	$limit = 10;
	if (str_word_count($text, 0) > $limit) {
		$words = str_word_count($text, 2);
		$pos   = array_keys($words);
		$text  = substr($text, 0, $pos[$limit]) . '...';
	}
	return $text;
}

$utm_campaign = date('F') . (int) ceil((new Datetime())->format('d') / 7);

$headers = array('Content-Type: text/html; charset=UTF-8');
$subject_prepared = "Activate subscription for Communications Today";
$ab = '';
if (! empty($_SERVER['HTTP_HOST']) && ! empty($_SERVER['REQUEST_URI'])) {
	$ab = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}
// echo file_get_contents('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
$body = '';
//$body .=file_get_contents($ab);
//wp_mail('ravipratapsingh96@gmail.com', $subject_prepared, $body, $headers);

?>
<!DOCTYPE html>
<html>

<head>
	<title></title>
	<style type="text/css">
		.button {
			border: 2px solid #1F93AC;
			padding: 5px;
			font-family: 'Roboto Condensed', sans-serif;
			color: #000;
			font-weight: 500;
			margin-right: 2px;
			font-size: 15px;
			text-decoration: none;
			margin-bottom: 10px !important;
		}

		a,
		td,
		p {
			font-size: 10px;
		}
	</style>
	<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500&family=Roboto&display=swap" rel="stylesheet">
</head>

<body>
	<table width="600" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="white">
		<!-- START HEADER/BANNER -->

		<tbody>


			<?php if ($fields['top_banner']['value']) { ?>
				<tr>
					<td align="center">
						<table class="col-600" width="660" border="0" align="center" cellpadding="0" cellspacing="0" style="">
							<tbody>
								<tr>
									<td style="width: 660px;">
										<a href="<?php echo $fields['top_banner_link']['value']; ?>" target="_blank">
											<img src="<?php echo $fields['top_banner']['value']; ?>" style="width: 660px;">
										</a>
									</td>

								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			<?php } ?>

			<!-- END HEADER/BANNER -->

			<tr>
				<td align="center">
					<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0" style="">
						<tbody>
							<tr height="8"></tr>
							<tr>
								<td width="440">
									<a href="https://www.communicationstoday.co.in/">
										<img src="https://www.communicationstoday.co.in/wp-content/uploads/2023/06/CT-DN-11.png" width="70%">
									</a>
								</td>
								<td align="right" style="" width="160">
									<span style="font-size:15px;"><strong style="font-size:15px;font-family: 'Roboto Condensed', sans-serif;color:black; "><?php echo $fields['date']['value']; ?></strong></span><br>
									<span style="font-size:13px;font-family: 'Roboto Condensed', sans-serif; "><?php echo getWeekdaya($fields['date']['value']); ?></span>
								</td>
							</tr>
							<tr height="10"></tr>
						</tbody>
					</table>
				</td>
			</tr>




			<!-- START 3 BOX SHOWCASE -->
			<?php if ($fields['banner_1']['value']) { ?>
				<tr>
					<td align="center">
						<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0" style="">
							<tbody>
								<tr>
									<td height=""></td>
								</tr>
								<tr>
									<td align="center">
										<a href="<?php echo $fields['banner_1_link']['value']; ?>" target="_blank">
											<img src="<?php echo $fields['banner_1']['value']; ?>" style="width:100%">
										</a>
									</td>
								</tr>
								<tr>
									<td height="20"></td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			<?php } ?>


			<?php if ($fields['type']['value'] == 'perspective') { ?>
				<tr>
					<td align="center">
						<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0" style="">
							<tbody>

								<tr>
									<td align="center">
										<img src="https://www.medicalbuyer.co.in/wp-content/uploads/2023/05/MB-DN-13.png" width="100%">
									</td>
								</tr>
								<tr>
									<td height="20"></td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr>
					<td align="center">
						<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0" style="">
							<tbody>
								<tr>
									<td valign="top">
										<table width="287" border="0" align="left" cellpadding="0" cellspacing="0" class="col2" style="">
											<tbody>
												<tr>
													<td align="center">
														<table class="insider" width="287" border="0" align="center" cellpadding="0" cellspacing="0">


															<tbody>
																<tr valign="top">
																	<td><?php  ?>
																		<a href="<?php echo esc_url(get_permalink($fields['perspective_post']['value']->ID)); ?>?utm_source=newsletter&utm_medium=email&utm_campaign=<?php echo $utm_campaign; ?>">
																			<img src="<?php echo $fields['perspective_post_image']['value']; ?>" style="width:300px;" height="150"> </a>
																	</td>
																</tr>
																<tr align="left">
																	<td>
																		<a href="<?php echo esc_url(get_permalink($fields['perspective_post']['value']->ID)); ?>?utm_source=newsletter&utm_medium=email&utm_campaign=<?php echo $utm_campaign; ?>" style="text-decoration:none;font-family: 'Roboto Condensed', sans-serif; font-size:15px; color:#2a3b4c;line-height:20px; font-weight: bold;"><?php echo $fields['perspective_post']['value']->post_title; ?>
																		</a>
																	</td>
																</tr>


																<tr>
																	<td style="font-family: 'Roboto Condensed', sans-serif; font-size:15px; color:#444; line-height:24px; font-weight: 300;"><?php echo limit_text($fields['perspective_post']['value']->post_content, 17); ?>
																	</td>
																</tr>
																<tr>
																	<td>
																		<a href="<?php echo esc_url(get_permalink($fields['perspective_post']['value']->ID)); ?>?utm_source=newsletter&utm_medium=email&utm_campaign=<?php echo $utm_campaign; ?>" class="readmore" style="text-decoration:none;font-family: 'Roboto Condensed', sans-serif; font-size:14px; color:#0066cc; line-height:24px; font-weight:bold;"><i>Read more</i></a>
																	</td>
																</tr>


															</tbody>
														</table>
													</td>
												</tr>
											</tbody>
										</table>
									</td>


									<td valign="top" style="padding: 0;">
										<table class="col2" width="260" border="0" align="right" cellpadding="0" cellspacing="0">
											<tbody>
												<tr>
													<td align="center" style="line-height:0px;">
														<a href="<?php echo $fields['perspective_banner_link']['value']; ?>">
															<img style="display:block; line-height:0px; font-size:0px; border:0px;" class="images_style" src="<?php echo $fields['perspective_banner']['value']; ?>" width="280" height="280">
														</a>
													</td>
												</tr>
											</tbody>
										</table>
									</td>

								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			<?php } ?>

			<?php if ($fields['type']['value'] == 'exclusive') { ?>
				<tr>
					<td align="center">
						<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0" style="">
							<tbody>

								<tr>
									<td align="center">
										<img src="https://www.medicalbuyer.co.in/wp-content/uploads/2023/05/MB-DN-13.png" width="100%">
									</td>
								</tr>
								<tr>
									<td height="20"></td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr>
					<td align="center">
						<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0" style="">
							<tbody>
								<tr>
									<td valign="top">
										<table width="287" border="0" align="left" cellpadding="0" cellspacing="0" class="col2" style="">
											<tbody>
												<tr>
													<td align="center">
														<table class="insider" width="287" border="0" align="center" cellpadding="0" cellspacing="0">


															<tbody>
																<tr valign="top">
																	<td><?php  ?>
																		<a href="<?php echo esc_url(get_permalink($fields['exclusive']['value']->ID)); ?>?utm_source=newsletter&utm_medium=email&utm_campaign=<?php echo $utm_campaign; ?>">
																			<img src="<?php echo communicationstoday_newsletter_post_thumb_url($fields['exclusive']['value']->ID); ?>" style="width:300px;" height="150"> </a>
																	</td>
																</tr>
																<tr align="left">
																	<td>
																		<a href="<?php echo esc_url(get_permalink($fields['exclusive']['value']->ID)); ?>?utm_source=newsletter&utm_medium=email&utm_campaign=<?php echo $utm_campaign; ?>" style="text-decoration:none;font-family: 'Roboto Condensed', sans-serif; font-size:15px; color:#2a3b4c;line-height:20px; font-weight: bold;"><?php echo $fields['exclusive']['value']->post_title; ?>
																		</a>
																	</td>
																</tr>


																<tr>
																	<td style="font-family: 'Roboto Condensed', sans-serif; font-size:15px; color:#444; line-height:24px; font-weight: 300;"><?php echo limit_text($fields['exclusive']['value']->post_content, 17); ?>
																	</td>
																</tr>
																<tr>
																	<td>
																		<a href="<?php echo esc_url(get_permalink($fields['exclusive']['value']->ID)); ?>?utm_source=newsletter&utm_medium=email&utm_campaign=<?php echo $utm_campaign; ?>" class="readmore" style="text-decoration:none;font-family: 'Roboto Condensed', sans-serif; font-size:14px; color:#0066cc; line-height:24px; font-weight:bold;"><i>Read more</i></a>
																	</td>
																</tr>


															</tbody>
														</table>
													</td>
												</tr>
											</tbody>
										</table>
									</td>


									<td valign="top" style="padding: 0;">
										<table class="col2" width="260" border="0" align="right" cellpadding="0" cellspacing="0">
											<tbody>
												<tr>
													<td align="center" style="line-height:0px;">
														<a href="<?php echo $fields['exclusive_banner_link']['value']; ?>">
															<img style="display:block; line-height:0px; font-size:0px; border:0px;" class="images_style" src="<?php echo $fields['exclusive_banner']['value']; ?>" width="280" height="280">
														</a>
													</td>
												</tr>
											</tbody>
										</table>
									</td>

								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			<?php } ?>

			<?php if ($fields['type']['value'] == 'headlineoftheday') { ?>
				<tr>
					<td align="center">
						<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0" style="">
							<tbody>

								<tr>
									<td align="center">
										<img src="https://www.medicalbuyer.co.in/wp-content/uploads/2021/06/12314235.jpg" width="100%">
									</td>
								</tr>
								<tr>
									<td height="20"></td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr>
					<td align="center">
						<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0" style="">
							<tbody>
								<tr>
									<td valign="top">
										<table width="287" border="0" align="left" cellpadding="0" cellspacing="0" class="col2" style="">
											<tbody>
												<tr>
													<td align="center">
														<table class="insider" width="287" border="0" align="center" cellpadding="0" cellspacing="0">


															<tbody>
																<tr valign="top">
																	<td><?php  ?>
																		<a href="<?php echo esc_url(get_permalink($fields['headlines_post']['value']->ID)); ?>?utm_source=newsletter&utm_medium=email&utm_campaign=<?php echo $utm_campaign; ?>">
																			<img src="<?php echo communicationstoday_newsletter_post_thumb_url($fields['headlines_post']['value']->ID); ?>" style="width:300px;" height="150"> </a>
																	</td>
																</tr>
																<tr align="left">
																	<td>
																		<a href="<?php echo esc_url(get_permalink($fields['headlines_post']['value']->ID)); ?>?utm_source=newsletter&utm_medium=email&utm_campaign=<?php echo $utm_campaign; ?>" style="text-decoration:none;font-family: 'Roboto Condensed', sans-serif; font-size:15px; color:#2a3b4c;line-height:20px; font-weight: bold;"><?php echo $fields['headlines_post']['value']->post_title; ?>
																		</a>
																	</td>
																</tr>


																<tr>
																	<td style="font-family: 'Roboto Condensed', sans-serif; font-size:15px; color:#444; line-height:24px; font-weight: 300;"><?php echo limit_text($fields['headlines_post']['value']->post_content, 17); ?>
																	</td>
																</tr>
																<tr>
																	<td>
																		<a href="<?php echo esc_url(get_permalink($fields['headlines_post']['value']->ID)); ?>?utm_source=newsletter&utm_medium=email&utm_campaign=<?php echo $utm_campaign; ?>" class="readmore" style="text-decoration:none;font-family: 'Roboto Condensed', sans-serif; font-size:14px; color:#0066cc; line-height:24px; font-weight:bold;"><i>Read more</i></a>
																	</td>
																</tr>


															</tbody>
														</table>
													</td>
												</tr>
											</tbody>
										</table>
									</td>


									<td valign="top" style="padding: 0;">
										<table class="col2" width="260" border="0" align="right" cellpadding="0" cellspacing="0">
											<tbody>
												<tr>
													<td align="center" style="line-height:0px;">
														<a href="<?php echo $fields['headlines_banner_link']['value']; ?>">
															<img style="display:block; line-height:0px; font-size:0px; border:0px;" class="images_style" src="<?php echo $fields['headlines_banner']['value']; ?>" width="280" height="280">
														</a>
													</td>
												</tr>
											</tbody>
										</table>
									</td>

								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			<?php } ?>


			<?php if ($fields['type']['value'] == 'exclusive_video') { ?>
				<tr>
					<td align="center">
						<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0" style="">
							<tbody>

								<tr>
									<td align="center">
										<img src="https://www.medicalbuyer.co.in/wp-content/uploads/2021/06/12314235.jpg" width="100%">
									</td>
								</tr>
								<tr>
									<td height="20"></td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr>
					<td align="center">
						<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0" style="">
							<tbody>
								<tr>
									<td valign="top">
										<table width="287" border="0" align="left" cellpadding="0" cellspacing="0" class="col2" style="">
											<tbody>
												<tr>
													<td align="center">
														<table class="insider" width="287" border="0" align="center" cellpadding="0" cellspacing="0">


															<tbody>
																<tr valign="top">
																	<td><?php  ?>
																		<a href="<?php echo $fields['video_link']['value']; ?>?utm_source=newsletter&utm_medium=email&utm_campaign=<?php echo $utm_campaign; ?>">
																			<img src="<?php echo $fields['exclusive_video_image']['value']; ?>" style="width:300px;" height="240"> </a>
																	</td>
																</tr>
																<tr align="left">
																	<td>
																		<a href="<?php echo $fields['video_link']['value']; ?>?utm_source=newsletter&utm_medium=email&utm_campaign=<?php echo $utm_campaign; ?>" style="text-decoration:none;font-family: 'Roboto Condensed', sans-serif; font-size:15px; color:#2a3b4c;line-height:20px; font-weight: bold;"><?php echo $fields['video_title']['value']; ?>
																		</a>
																	</td>
																</tr>
															</tbody>
														</table>
													</td>
												</tr>
											</tbody>
										</table>
									</td>


									<td valign="top" style="padding: 0;">
										<table class="col2" width="260" border="0" align="right" cellpadding="0" cellspacing="0">
											<tbody>
												<tr>
													<td align="center" style="line-height:0px;">
														<a href="<?php echo $fields['exclusive_feature_link_']['value']; ?>">
															<img style="display:block; line-height:0px; font-size:0px; border:0px;" class="images_style" src="<?php echo $fields['exclusive_feature_banner']['value']; ?>" width="280" height="280">
														</a>
													</td>
												</tr>
											</tbody>
										</table>
									</td>

								</tr>
							</tbody>
						</table>
					</td>
				</tr>

			<?php } ?>



			<?php if ($fields['type']['value'] == 'exclusive_feature') { ?>
				<tr>
					<td align="center">
						<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0" style="">
							<tbody>

								<tr>
									<td align="center">
										<img src="https://www.medicalbuyer.co.in/wp-content/uploads/2021/06/12314235.jpg" width="100%">
									</td>
								</tr>
								<tr>
									<td height="20"></td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr>
					<td align="center">
						<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0" style="">
							<tbody>
								<tr>
									<td valign="top">
										<table width="287" border="0" align="left" cellpadding="0" cellspacing="0" class="col2" style="">
											<tbody>
												<tr>
													<td align="center">
														<table class="insider" width="287" border="0" align="center" cellpadding="0" cellspacing="0">


															<tbody>
																<tr valign="top">
																	<td><?php  ?>
																		<a href="<?php echo esc_url(get_permalink($fields['exclusive_feature_post']['value']->ID)); ?>?utm_source=newsletter&utm_medium=email&utm_campaign=<?php echo $utm_campaign; ?>">
																			<img src="<?php echo communicationstoday_newsletter_post_thumb_url($fields['exclusive_feature_post']['value']->ID); ?>" style="width:300px;" height="150"> </a>
																	</td>
																</tr>
																<tr align="left">
																	<td>
																		<a href="<?php echo esc_url(get_permalink($fields['exclusive_feature_post']['value']->ID)); ?>?utm_source=newsletter&utm_medium=email&utm_campaign=<?php echo $utm_campaign; ?>" style="text-decoration:none;font-family: 'Roboto Condensed', sans-serif; font-size:15px; color:#2a3b4c;line-height:20px; font-weight: bold;"><?php echo $fields['exclusive_feature_post']['value']->post_title; ?>
																		</a>
																	</td>
																</tr>


																<tr>
																	<td style="font-family: 'Roboto Condensed', sans-serif; font-size:15px; color:#444; line-height:24px; font-weight: 300;"><?php echo limit_text($fields['exclusive_feature_post']['value']->post_content, 17); ?>
																	</td>
																</tr>
																<tr>
																	<td>
																		<a href="<?php echo esc_url(get_permalink($fields['exclusive_feature_post']['value']->ID)); ?>?utm_source=newsletter&utm_medium=email&utm_campaign=<?php echo $utm_campaign; ?>" class="readmore" style="text-decoration:none;font-family: 'Roboto Condensed', sans-serif; font-size:14px; color:#0066cc; line-height:24px; font-weight:bold;"><i>Read more</i></a>
																	</td>
																</tr>


															</tbody>
														</table>
													</td>
												</tr>
											</tbody>
										</table>
									</td>


									<td valign="top" style="padding: 0;">
										<table class="col2" width="260" border="0" align="right" cellpadding="0" cellspacing="0">
											<tbody>
												<tr>
													<td align="center" style="line-height:0px;">
														<a href="<?php echo $fields['exclusive_feature_link_']['value']; ?>">
															<img style="display:block; line-height:0px; font-size:0px; border:0px;" class="images_style" src="<?php echo $fields['exclusive_feature_banner']['value']; ?>" width="280" height="280">
														</a>
													</td>
												</tr>
											</tbody>
										</table>
									</td>

								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			<?php } ?>

			<?php if ($fields['type']['value'] == 'editorial') { ?>
				<tr>
					<td align="center">
						<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0" style="">
							<tbody>

								<tr>
									<td align="center">
										<img src="https://www.medicalbuyer.co.in/wp-content/uploads/2021/06/12314235.jpg" width="100%">
									</td>
								</tr>
								<tr>
									<td height="20"></td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr>
					<td align="center">
						<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0" style="">
							<tbody>
								<tr>
									<td valign="top">
										<table width="287" border="0" align="left" cellpadding="0" cellspacing="0" class="col2" style="">
											<tbody>
												<tr>
													<td align="center">
														<table class="insider" width="287" border="0" align="center" cellpadding="0" cellspacing="0">


															<tbody>
																<tr valign="top">
																	<td><?php  ?>
																		<a href="<?php echo esc_url(get_permalink($fields['editorial_post_']['value']->ID)); ?>?utm_source=newsletter&utm_medium=email&utm_campaign=<?php echo $utm_campaign; ?>">
																			<img src="<?php echo $fields['editorial_post_image']['value']; ?>" style="width:150px;" height="150"> </a>
																	</td>
																</tr>
																<tr align="left">
																	<td>
																		<a href="<?php echo esc_url(get_permalink($fields['editorial_post_']['value']->ID)); ?>?utm_source=newsletter&utm_medium=email&utm_campaign=<?php echo $utm_campaign; ?>" style="text-decoration:none;font-family: 'Roboto Condensed', sans-serif; font-size:15px; color:#2a3b4c;line-height:20px; font-weight: bold;"><?php echo $fields['editorial_post_']['value']->post_title; ?>
																		</a>
																	</td>
																</tr>


																<tr>
																	<td style="font-family: 'Roboto Condensed', sans-serif; font-size:15px; color:#444; line-height:24px; font-weight: 300;"><?php echo limit_text($fields['editorial_post_']['value']->post_content, 17); ?>
																	</td>
																</tr>
																<tr>
																	<td>
																		<a href="<?php echo esc_url(get_permalink($fields['editorial_post_']['value']->ID)); ?>?utm_source=newsletter&utm_medium=email&utm_campaign=<?php echo $utm_campaign; ?>" class="readmore" style="text-decoration:none;font-family: 'Roboto Condensed', sans-serif; font-size:14px; color:#0066cc; line-height:24px; font-weight:bold;"><i>Read more</i></a>
																	</td>
																</tr>


															</tbody>
														</table>
													</td>
												</tr>
											</tbody>
										</table>
									</td>


									<td valign="top" style="padding: 0;">
										<table class="col2" width="260" border="0" align="right" cellpadding="0" cellspacing="0">
											<tbody>
												<tr>
													<td align="center" style="line-height:0px;">
														<a href="<?php echo $fields['editorial_feature_link']['value']; ?>">
															<img style="display:block; line-height:0px; font-size:0px; border:0px;" class="images_style" src="<?php echo $fields['editorial_feature_banner']['value']; ?>" width="280" height="280">
														</a>
													</td>
												</tr>
											</tbody>
										</table>
									</td>

								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			<?php } ?>


			<!-- Headlines-->
			<tr>
				<td align="center">
					<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0" style="">
						<tbody>
							<tr>
								<td height="5"></td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<!-- START 3 BOX SHOWCASE -->
			<?php if ($fields['banner_3']['value']) { ?>
				<tr>
					<td align="center">
						<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0" style="">
							<tbody>
								<tr>
									<td height="10"></td>
								</tr>
								<tr>
									<td align="center">
										<a href="<?php echo $fields['banner_3_link']['value']; ?>" target="_blank">
											<img src="<?php echo $fields['banner_3']['value']; ?>" style="width:100%">
										</a>
									</td>
								</tr>
								<tr>
									<td height="10"></td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			<?php } ?>



			<!-- Headlines with image -->
			<tr>
				<td height="8">

				</td>
			</tr>
			<?php

			foreach ($fields['news']['value'] as $data) {

			?>
				<tr>
					<?php if ($data['image']) { ?>
						<td align="center">
							<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0" style="">
								<tbody>
									<td align="right">
										<table class="col2" width="185" border="0" align="left" cellpadding="0" cellspacing="5">
											<tbody>
												<tr>
													<td align="center" style="line-height:0px;">
														<a href="<?php echo esc_url(get_permalink($data['all_news']->ID)); ?>?utm_source=newsletter&utm_medium=email&utm_campaign=<?php echo $utm_campaign; ?>">
															<img style="display:block; line-height:0px; font-size:0px; border:0px;" class="images_style" src="<?php echo communicationstoday_newsletter_post_thumb_url($data['all_news']->ID); ?>" width="180" height="120">
														</a>
													</td>
												</tr>
											</tbody>
										</table>
									</td>
									<td valign="top">
										<table width="390" border="0" align="right" cellpadding="0" cellspacing="5" class="col2" style="">
											<tbody>
												<tr>
													<td align="center">
														<table class="insider" style="width: 390px;" border="0" align="center" cellpadding="0" cellspacing="0">
															<tbody>
																<tr align="left">
																</tr>
																<tr align="left">
																	<td>
																		<a href="<?php echo esc_url(get_permalink($data['all_news']->ID)); ?>?utm_source=newsletter&utm_medium=email&utm_campaign=<?php echo $utm_campaign; ?>" style="text-decoration:none;font-family: 'Roboto Condensed', sans-serif; font-size:15px; color:#2a3b4c;line-height:20px; font-weight: bold;"><?php echo $data['all_news']->post_title;  ?>
																		</a>
																	</td>
																</tr>
																<tr>
																	<td style="font-family: 'Roboto Condensed', sans-serif; font-size:15px; color:#444;line-height:24px; font-weight: 300;"><?php echo limit_text($data['all_news']->post_content, 17); ?>
																	</td>
																</tr>
																<tr>
																	<td>
																		<a href="<?php echo esc_url(get_permalink($data['all_news']->ID)); ?>?utm_source=newsletter&utm_medium=email&utm_campaign=<?php echo $utm_campaign; ?>" class="readmore" style="text-decoration:none;font-family: 'Roboto Condensed', sans-serif; font-size:14px; color:#0066cc; line-height:24px; font-weight:bold;"><i>Read more</i></a>
																	</td>
																</tr>
															</tbody>
														</table>
													</td>
												</tr>
											</tbody>
										</table>
									</td>
								</tbody>
							</table>
				<tr>
					<td height="5"></td>
				</tr>
				</td>
				</tr>
				<!-- with image -->
			<?php } else { ?>
				<!-- without image -->
				<tr>
					<td align="left">
						<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="5" style="">
							<tbody>
								<tr>
									<td>
										<a href="<?php echo esc_url(get_permalink($data['all_news']->ID)); ?>?utm_source=newsletter&utm_medium=email&utm_campaign=<?php echo $utm_campaign; ?>" style="text-decoration:none;font-family: 'Roboto Condensed',sans-serif;width:600px; font-size:15px; color:#2a3b4c;line-height:20px; font-weight: bold;"><?php echo $data['all_news']->post_title;  ?>
										</a>
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<!-- without image -->
			<?php } ?>

		<?php } ?>

		<!-- Reports with image -->
		<?php if ($fields['banner_2']['value']) { ?>
			<tr>
				<td align="center">
					<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0" style="">
						<tbody>
							<tr>
								<td height="10"></td>
							</tr>
							<tr>
								<td align="center">
									<a href="<?php echo $fields['banner_2_link']['value']; ?>" target="_blank">
										<img src="<?php echo $fields['banner_2']['value']; ?>" width="100%">
									</a>
								</td>
							</tr>
							<tr>
								<td height="10"></td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>

		<?php } ?>

		<?php

		foreach ($fields['news_2']['value'] as $data) {

		?>
			<tr>
				<?php if ($data['image']) { ?>
					<td align="center">
						<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0" style="">
							<tbody>
								<td align="right">
									<table class="col2" width="185" border="0" align="left" cellpadding="0" cellspacing="5">
										<tbody>
											<tr>
												<td align="center" style="line-height:0px;">
													<a href="<?php echo esc_url(get_permalink($data['all_news']->ID)); ?>?utm_source=newsletter&utm_medium=email&utm_campaign=<?php echo $utm_campaign; ?>">
														<img style="display:block; line-height:0px; font-size:0px; border:0px;" class="images_style" src="<?php echo communicationstoday_newsletter_post_thumb_url($data['all_news']->ID); ?>" width="180" height="120">
													</a>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
								<td valign="top">
									<table width="390" border="0" align="right" cellpadding="0" cellspacing="5" class="col2" style="">
										<tbody>
											<tr>
												<td align="center">
													<table class="insider" style="width: 390px;" border="0" align="center" cellpadding="0" cellspacing="0">
														<tbody>
															<tr align="left">
															</tr>
															<tr align="left">
																<td>
																	<a href="<?php echo esc_url(get_permalink($data['all_news']->ID)); ?>?utm_source=newsletter&utm_medium=email&utm_campaign=<?php echo $utm_campaign; ?>" style="text-decoration:none;font-family: 'Roboto Condensed', sans-serif; font-size:15px; color:#2a3b4c;line-height:20px; font-weight: bold;"><?php echo $data['all_news']->post_title;  ?>
																	</a>
																</td>
															</tr>
															<tr>
																<td style="font-family: 'Roboto Condensed', sans-serif; font-size:15px; color:#444;line-height:24px; font-weight: 300;"><?php echo limit_text($data['all_news']->post_content, 17); ?>
																</td>
															</tr>
															<tr>
																<td>
																	<a href="<?php echo esc_url(get_permalink($data['all_news']->ID)); ?>?utm_source=newsletter&utm_medium=email&utm_campaign=<?php echo $utm_campaign; ?>" class="readmore" style="text-decoration:none;font-family: 'Roboto Condensed', sans-serif; font-size:14px; color:#0066cc; line-height:24px; font-weight:bold;"><i>Read more</i></a>
																</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tbody>
						</table>
			<tr>
				<td height="5"></td>
			</tr>
			</td>
			</tr>
			<!-- with image -->
		<?php } else { ?>
			<!-- without image -->
			<tr>
				<td align="left">
					<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="5" style="">
						<tbody>
							<tr>
								<td>
									<a href="<?php echo esc_url(get_permalink($data['all_news']->ID)); ?>?utm_source=newsletter&utm_medium=email&utm_campaign=<?php echo $utm_campaign; ?>" style="text-decoration:none;font-family: 'Roboto Condensed',sans-serif;width:600px; font-size:15px; color:#2a3b4c;line-height:20px; font-weight: bold;"><?php echo $data['all_news']->post_title;  ?>
									</a>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<!-- without image -->
		<?php } ?>

	<?php } ?>

	<!-- Reports with image -->
	<?php if ($fields['banner_4']['value']) { ?>
		<tr>
			<td align="center">
				<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0" style="">
					<tbody>
						<tr>
							<td height="10"></td>
						</tr>
						<tr>
							<td align="center">
								<a href="<?php echo $fields['banner_4_link']['value']; ?>" target="_blank">
									<img src="<?php echo $fields['banner_4']['value']; ?>" width="100%">
								</a>
							</td>
						</tr>
						<tr>
							<td height="10"></td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>

	<?php } ?>



	<tr>
		<td height="20"></td>
	</tr>


	<tr>
		<td align="center">
			<table class="col-600" width="600" border="0" align="center" cellpadding="0" cellspacing="0" style="">
				<tbody>
					<tr>
						<td height="40"></td>
					</tr>
					<tr>
						<td align="center">
							<!-- Image Map Generated by http://www.image-map.net/ -->
							<img src="https://www.adi-media.com/img/CT-LT-600x160-01.png" usemap="#image-map">


							<map name="image-map">
								<area target="_blank" alt="adi media" title="adi media" href="https://www.adi-media.com/" coords="3,60,96,124" shape="rect">
								<area target="_blank" alt="Home" title="Home" href="https://www.communicationstoday.co.in/" coords="29,130,85,149" shape="rect">
								<area target="_blank" alt="About us" title="About us" href="https://www.communicationstoday.co.in/aboutus/" coords="97,133,163,153" shape="rect">
								<area target="_blank" alt="Contact us" title="Contact us" href="https://www.communicationstoday.co.in/contact-us/" coords="171,131,240,151" shape="rect">
								<area target="_blank" alt="Term & Conditions" title="Term & Conditions" href="https://www.communicationstoday.co.in/terms-conditions/" coords="261,132,385,150" shape="rect">
								<area target="_blank" alt="Privacy" title="Privacy" href="https://www.communicationstoday.co.in/privacy-policy/" coords="394,129,485,151" shape="rect">
								<area target="_blank" alt="Disclaimer" title="Disclaimer" href="https://www.communicationstoday.co.in/disclaimer/" coords="492,130,562,152" shape="rect">
								<area target="_blank" alt="CT" title="CT" href="https://www.communicationstoday.co.in/" coords="366,16,583,40" shape="rect">
								<area target="_blank" alt="Linkedin" title="Linkedin" href="https://www.linkedin.com/company/communications-today/" coords="418,44,449,71" shape="rect">
								<area target="_blank" alt="Twitter" title="Twitter" href="https://twitter.com/comms_today" coords="462,44,492,74" shape="rect">
								<area target="_blank" alt="Youtube" title="Youtube" href="https://www.youtube.com/@communicationstoday8265" coords="502,43,533,70" shape="rect">
							</map>
						</td>
					</tr>
					<tr>
						<td height="20"></td>
					</tr>
				</tbody>
			</table>
		</td>
	</tr>


		</tbody>
	</table>
</body>

</html>