<div id="b-location-list" class="b-container <?php echo ( $categories ) ? 'have-categories' : '' ?> <?php echo ( $search ) ? 'have-search' : '' ?> <?php echo ( $locations ) ? 'have-location' : '' ?>">
	<div class="b-preloader"></div>
	<?php if ( $categories ): ?>
		<select name="b-categories" id="b-categories">
			<option>- Select Categories -</option>
			<?php foreach ( $categories as $category ): ?>
				<option value="<?php echo $category->slug ?>"><?php echo $category->name ?></option>
			<?php endforeach ?>
		</select>
	<?php endif ?>

	<?php if ( $search && $locations ): ?>
		<input type="text" name="b-search" id="b-search">
	<?php endif ?>

	<div id="b-location">
		<ul>
			<li>Loading Locations..</li>
		</ul>
	</div>
</div>

<?php wp_reset_postdata() ?>