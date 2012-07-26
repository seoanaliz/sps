<?
    /** @var $article Article */
    /** @var $articleRecord ArticleRecord */

    if (!empty($article)) {
?>

<div class="post" data-id="{$article->articleId}">
    <div class="delete"></div>
    <div class="photo">
        <a target="_blank" href="http://vk.com/public">
            <img src="http://vk.cc/Q3gWv" alt="" />
        </a>
    </div>
    <div class="content">
        <div class="title">
            <a target="_blank" href="http://vk.com/public">Travels</a>
        </div>
        <div class="text"><?= nl2br(HtmlHelper::RenderToForm($articleRecord->content)) ?></div>
        <div class="sign clear-fix">
            <div class="user-info">
                <span class="photo">
                    <a target="_blank" href="http://vk.com/id">
                        <img src="http://vk.cc/Q2PuP" alt="" />
                    </a>
                </span>
                <span class="name">
                    <a target="_blank" href="http://vk.com/id">Artyom Kohver</a>
                </span>
                <span class="date">{$article->createdAt->defaultFormat()}</span>
            </div>
            <? if ($likes) { ?>
            <div class="likes">
                <span class="icon hart"></span>
                <span class="counter">1720</span>
            </div>
            <? } ?>
        </div>
        {increal:tmpl://app/elements/wall-comments.tmpl.php}
    </div>
</div>
<? } ?>