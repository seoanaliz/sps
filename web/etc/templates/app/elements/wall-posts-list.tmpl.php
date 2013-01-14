<?
$articlesCount = empty($articlesCount) ? 0 : $articlesCount;
/** @var $articles Article[] */
/** @var $articleRecords ArticleRecord[] */
/** @var $authors Author[] */
/** @var $targetFeeds TargetFeed[] */
/** @var $userGroups array - группы пользователя */
/** @var $showControls bool */

$type = Request::getString('type');
$tabType = Request::getString('tabType');
$currentGroup = Request::getString('userGroupId');
$articlesCountText = (empty($articlesCount) ? 'нет' : $articlesCount) . ' ' . LocaleLoader::Translate('fe.common.records.declension' . TextHelper::GetDeclension($articlesCount));
?>

<? if ($showControls): ?>
<div class="groups" id="groups">
    <div class="tab-bar no-padding">
        <div class="tab<?=$currentGroup ? '' : ' selected'?>">Все записи</div>
        <? foreach ($userGroups as $userGroup) { ?>
        <div class="tab<?=$currentGroup == $userGroup->userGroupId ? ' selected' : ''?>"
             data-id="<?=$userGroup->userGroupId?>">
            <?=$userGroup->name?>
        </div>
        <? } ?>
    </div>
</div>


<div class="wall<?=$type == 'my' ? ' not-textarea' : ''?>" id="wall">

    <div class="title clear-fix">
        <div class="text"></div>
        <div class="dropdown" style="visibility: hidden;">мои записи</div>
    </div>
    <div class="tabs">
        <div class="tab-bar">
            <div class="tab all <?= (empty($tabType) || ($tabType == 'all')) ? 'selected' : '' ?>" data-type="all">Все
                записи
            </div>
            <div class="tab planned <?= (!empty($tabType) && ($tabType == 'queued')) ? 'selected' : '' ?>"
                 data-type="queued">Запланированные<span class="counter"></span></div>
            <div class="tab posted <?= (!empty($tabType) && ($tabType == 'sent')) ? 'selected' : '' ?>"
                 data-type="sent">Отправленные<span class="counter"></span></div>
        </div>
    </div>

    <div class="new-post">
        <div class="textarea-wrap">
            <textarea placeholder="Есть чем поделиться?" rows="2"></textarea>

            <div class="add-photo"></div>
        </div>
        <div class="attachments">
            <div class="photos clear-fix"></div>
        </div>
        <div class="actions">
            <button class="button send">Отправить</button>
            <span class="text">Ctrl+Enter</span>
            <span class="file-uploader">Attach</span>
        </div>
    </div>


    <div class="list">
         <? endif; ?>
        <? if (!empty($articles)) {
            foreach ($articles as $article) {
                $articleRecord = !empty($articleRecords[$article->articleId]) ? $articleRecords[$article->articleId] : new ArticleRecord();
                $author = !empty($authors[$article->authorId]) ? $authors[$article->authorId] : null;
                $targetFeed = !empty($targetFeeds[$article->targetFeedId]) ? $targetFeeds[$article->targetFeedId] : null;

                if (empty($articleRecord) || empty($author) || empty($targetFeed)) continue;
                ?>{increal:tmpl://app/elements/wall-post.tmpl.php}<?
            }
        } ?>

        <? if ($hasMore) { ?>
            <div id="wall-show-more" class="show-more">Еще</div>
        <? } ?>

        <? if ($showControls): ?>
    </div>

    <script type="text/javascript">
        $('#wall > .title .text').text('{$articlesCountText}');
    </script>
    <script type="text/javascript">
        function setCounter(selector, value) {
            var counter = $(selector);
            if (!counter.data('counter')) {
                counter.counter({prefix:'+'});
            }
            counter.counter('setCounter', value);
        }

            <? if (isset($__authorCounter)) { ?>
            <? if (isset($__authorCounter['total'])) { ?>
            setCounter('.menu .item.selected .counter', '{$__authorCounter[total]}');
                <? } ?>
            <? if (isset($__authorCounter['newQueued'])) { ?>
            setCounter('.tabs .tab.planned .counter', '{$__authorCounter[newQueued]}');
                <? } ?>
            <? if (isset($__authorCounter['newSent'])) { ?>
            setCounter('.tabs .tab.posted .counter', '{$__authorCounter[newSent]}');
                <? } ?>
            <? } ?>
    </script>
</div>
 <? endif; ?>
