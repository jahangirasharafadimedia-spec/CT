
<?php 
$id = get_the_ID();
function getWeekday($date) {
	return date('l', strtotime($date));
}
function limit_text($text, $limit) {
	$limit=15;
	if (str_word_count($text, 0) > $limit) {
		$words = str_word_count($text, 2);
		$pos   = array_keys($words);
		$text  = substr($text, 0, $pos[$limit]) . '...';
	}
	return $text;
}
?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
	<style type="text/css">
		.button{
			border:2px solid #1F93AC;
			padding:5px;
			font-family: 'Roboto Condensed', sans-serif;
			color: #000;
			font-weight: 500;
			margin-right: 2px;
			font-size: 15px;
			text-decoration: none;
			margin-bottom: 10px !important;
		}
		a,td,p{
			font-size: 10px;
		}


		.ii a,.ii p{
           font-size: 10px;
		}



@media (max-width: 575.98px) {
.ii a,.ii p{
           font-size: 10px;
		}


 }

	</style>
	<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500&family=Roboto&display=swap" rel="stylesheet">
</head>


<body>
	<table width="600" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="white">
		<!-- START HEADER/BANNER -->

		<tbody>
			<?php
			if ( have_rows( 'flexible_sections', $id ) ):

				while ( have_rows( 'flexible_sections', $id ) ) : the_row();

					get_template_part( 'newsletter-parts/content', get_row_layout() );

				endwhile;

			endif;
			?>

		</tbody>
	</table>
</body>
</html>