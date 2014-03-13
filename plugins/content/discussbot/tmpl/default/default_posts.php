<?php
/**
 * @var JFusionDiscussBotHelper $this
 */
?>
<?php if(!empty($this->output['posts'])) : ?>
	<?php foreach ($this->output['posts'] as $p) : ?>
		<div class="jfusionPostBody" id="post<?php echo $p->postid; ?>">
			<?php if(!empty($p->avatar_src)) : ?>
				<div class="jfusionUserAvatar">
					<img style="max-height: <?php echo $p->avatar_height; ?>px; max-width: <?php echo $p->avatar_width; ?>px;" src="<?php echo $p->avatar_src; ?>">
				</div>
			<?php endif; ?>

			<span class="jfusionPostTitle">
		        <a href="<?php echo $p->subject_url; ?>"><?php echo $p->subject; ?></a>
			</span>

			<?php if(!empty($p->username)) : ?>
				-- <span class="jfusionPostUser">
				<?php if(!empty($p->username_url)) : ?>
					<a href="<?php echo $p->username_url; ?>"><?php echo $p->username; ?></a>

				<?php elseif($p->guest) : ?>
					<?php echo $p->username . ' (' . JText::_('GUEST') . ')';  ?>
				<?php else: ?>
					<?php echo $p->username; ?>
				<?php endif; ?>
				</span>
			<?php endif; ?>

			<?php if(!empty($p->date)) : ?>
				<div class='jfusionPostDate'><?php echo $p->date; ?></div>
			<?php endif; ?>

			<div style="<?php if(!empty($p->avatar_src)) echo 'padding-left:' . ($p->avatar_width+10) . 'px;'; ?>" class="jfusionPostText"><?php echo $p->text; ?></div>
			<div class="jfusionToolbar jfusionclearfix"><span><?php echo $p->toolbar; ?></span></div>
			<div id="originalText<?php echo $p->postid;?>" style="display:none;"><?php echo $p->original_text; ?></div>
		</div>
	<?php endforeach; ?>
<?php else: ?>
	<div class="jfusionNoPostMsg"><?php echo $this->params->get('no_posts_msg')?></div>
<?php endif; ?>