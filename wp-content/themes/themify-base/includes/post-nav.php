<?php
/**
 * Post Navigation.
 */

$in_same_cat = true; ?>
<!-- post-nav -->
<div class="post-nav clearfix">
	<?php previous_post_link( '<span class="prev">%link</span>', '<span class="arrow icon-left"></span> %title' ) ?>
	<?php next_post_link( '<span class="next">%link</span>', '%title <span class="arrow icon-right"></span>' ) ?>
</div>
<!-- /post-nav -->
