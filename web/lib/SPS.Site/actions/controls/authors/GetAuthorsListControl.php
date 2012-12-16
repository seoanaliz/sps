<?php
Package::Load('SPS.Site/base');

/**
 * Возвращает список авторов для ленты
 * TODO наверное нкжно переделать
 * @package    SPS
 * @subpackage Site
 * @author     shuler
 */
class GetAuthorsListControl extends BaseControl
{

    /**
     * Entry Point
     */
    public function Execute() {
        $TargetFeedAccessUtility = new TargetFeedAccessUtility($this->vkId);
        $targetFeedId = Request::getInteger('targetFeedId');

        if (!$TargetFeedAccessUtility->canShowAuthorList($targetFeedId)) {
            return;
        }

        if (!empty($targetFeedId)) {
            $authors = AuthorFactory::Get(
                array()
                , array(
                    BaseFactory::WithoutPages => true
                , BaseFactory::OrderBy => ' "firstName", "lastName" '
                , BaseFactory::CustomSql => ' AND"targetFeedIds" @> \'{' . PgSqlConvert::ToInt($targetFeedId) . '}\''
                )
            );
        } else {
            $authors = array();
        }

        Response::setArray('authors', $authors);
    }
}

?>