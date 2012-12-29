<?php

/* Examples can be found on: http://www.flickr.com/explore/interesting/7days
 * usage
	array (
		'linkcolor' => '', // link-color above the background
		'src' => '', // image-source
		'link' => '', // back-reference
		'title' => '', // link-title
		'author' => '', // author
		'copyright' => '' // copyrightright-notice
	),
 * Source of the Link looks like this:
 * <a href="http://www.flickr.com/photos/58621196@N05/7078838757/" 
 * title="on the verge von brdonovan bei Flickr">
 * <img src="http://farm8.staticflickr.com/7106/7078838757_cc616aed30_b.jpg" width="1024" height="683" alt="on the verge">
 * </a>
 * */

$loginpics = array(
	array (
		'linkcolor' => '#eee', // link-color above the background
		'src' => 'http://farm8.staticflickr.com/7106/7078838757_cc616aed30_b.jpg', // image-source
		'link' => 'http://www.flickr.com/photos/58621196@N05/7078838757', // back-reference
		'title' => 'on the verge', // link-title
		'author' => 'brdonovan', // author
		'copyright' => 'CC-BY-SA' // copyrightright-notice
	),
	array (
		'linkcolor' => '#fff', // link-color above the background
		'src' => 'http://farm6.staticflickr.com/5463/7076454553_0c6b592dca_b.jpg', // image-source
		'link' => 'http://www.flickr.com/photos/nasahqphoto/7076454553', // back-reference
		'title' => 'Discovery Ready For Mate-Demate Device', // link-title
		'author' => 'nasa hq photo', // author
		'copyright' => 'CC-BY-NC' // copyrightright-notice
	),
	array (
		'linkcolor' => '#fff', // link-color above the background
		'src' => 'http://farm5.staticflickr.com/4025/4638522131_86dd2109d7_b.jpg', // image-source
		'link' => 'http://www.flickr.com/photos/22746515@N02/4638522131', // back-reference
		'title' => 'Passage to shopping paradise', // link-title
		'author' => 'Bert Kaufmann', // author
		'copyright' => 'CC-BY' // copyrightright-notice
	),
	array (
		'linkcolor' => '#000', // link-color above the background
		'src' => 'http://farm3.staticflickr.com/2613/4027150477_e7fbfc33c6_b.jpg', // image-source
		'link' => 'http://www.flickr.com/photos/hoseal/4027150477', // back-reference
		'title' => 'Industrial Ayala', // link-title
		'author' => 'hoseal', // author
		'copyright' => 'CC-BY' // copyrightright-notice
	),
	array (
		'linkcolor' => '#333', // link-color above the background
		'src' => 'http://farm5.staticflickr.com/4100/4745435921_817bb433b8_b.jpg', // image-source
		'link' => 'http://www.flickr.com/photos/petersandbach/4745435921', // back-reference
		'title' => 'Industrial Beauty', // link-title
		'author' => 'petersandbach', // author
		'copyright' => 'CC-BY' // copyrightright-notice
	),
	array (
		'linkcolor' => '#fff', // link-color above the background
		'src' => 'http://farm6.staticflickr.com/5180/5492207075_4e0e7a0ee6_b.jpg', // image-source
		'link' => 'http://www.flickr.com/photos/ingythewingy/5492207075', // back-reference
		'title' => 'Vitriol Works', // link-title
		'author' => 'Ingy The Wingy',
		'copyright' => 'CC-BY-ND' // copyrightright-notice
	),
	array (
		'linkcolor' => '#fff', // link-color above the background
		'src' => 'http://farm1.staticflickr.com/25/58379433_9ea61a0aeb_b.jpg', // image-source
		'link' => 'http://www.flickr.com/photos/mabahamo/58379433', // back-reference
		'title' => 'All work and no play makes Manuel a dull boy', // link-title
		'author' => 'mabahamo', // author
		'copyright' => 'CC-BY-ND' // copyrightright-notice
	),
	array(
		'linkcolor' => '#fff',
		'src' => 'http://farm3.staticflickr.com/2734/4036588376_2213e0b369_z.jpg?zz=1',
		'link' => 'http://www.flickr.com/photos/prudencebrown/4036588376',
		'title' => 'Work saves us from three great evils:  boredom, vice and need',
		'author' => 'Holly Ford Brown',
		'copyright' => 'CC-BY-ND'
	)
	
);

// get pic of the day
$bg = $loginpics[(date('z') % count($loginpics))];
echo json_encode($bg);

?>
