<?php
Package::Load('SPS.Site\base');

/**
 * ClearArticleTextControl Action
 * @package    SPS
 * @subpackage Site
 * @author     Shuler
 */
class ClearArticleTextControl extends BaseControl
{

    /**
     * Entry Point
     */
    public function Execute()
    {
        $id = Request::getInteger('id');

        if (empty($id)) {
            return;
        }

        $object = ArticleFactory::GetById($id);
        if (empty($object)) {
            return;
        }

        $SourceAccessUtility =new SourceAccessUtility($this->vkId);

        //check access
        if (!$SourceAccessUtility->hasAccessToSourceFeed($object->sourceFeedId)) {
            return;
        }

        if ($id) {
            $o = new ArticleRecord();
            $o->content = '';
            ArticleRecordFactory::UpdateByMask($o, array('content'), array('articleId' => $id));
        }
    }
}

?>