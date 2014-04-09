<?php
/**
 * @var JFusionDiscussBotHelper $this
 */
if($this->params->get('show_posts')) : ?>
    <div class="jfusionPostHeader"><?php echo $this->params->get('post_header'); ?></div>

    <div class="jfusionPostArea">
        <?php require(DISCUSSION_TEMPLATE_PATH . 'default_posts.php'); ?>
    </div>

    <div class="pagination jfusionPostPagination">
        <?php echo $this->output['post_pagination']; ?>
    </div>
<?php endif; ?>

<div id="jfusionMessageArea<?php echo $this->article->id; ?>"></div>
<div class="jfusionQuickReply" id="jfusionQuickReply">
	<?php if ($this->output['reply_form']) : ?>
	    <div class="jfusionQuickReplyHeader"><?php echo $this->params->get('quick_reply_header'); ?></div>

		<?php echo $this->output['reply_form']; ?>

	<?php elseif (!empty($this->output['reply_form_error'])) : ?>
		<?php echo $this->output['reply_form_error']; ?>
	<?php endif; ?>
</div>