<?php
/**
 * User: x100up
 * Date: 16.12.12 13:50
 * In Code We Trust
 */

Package::Load('SPS.Site/base');

/**
 * @static ArticleGroup[] Get(array|null $searchArray=null, array|null $options=null, array|null $connectionName=null)
 * @static ArticleGroup GetById(array|null $searchArray=null, array|null $options=null, array|null $connectionName=null)
 * @static ArticleGroup GetOne(array|null $searchArray=null, array|null $options=null, array|null $connectionName=null)
 */
class ArticleGroupFactory extends BaseModelFactory {
    public static $mapping = array(
        'class' => 'ArticleGroup',
        'table' => 'articleGroup',
        //'view' => 'userFeed',
        'fields' => array(
            'articleGroupId' => array(
                'name' => 'articleGroupId',
                'type' => TYPE_INTEGER
            ),
            'targetFeedId' => array(
                'name' => 'targetFeedId',
                'type' => TYPE_INTEGER
            ),
            'name' => array(
                'name' => 'name',
                'type' => TYPE_STRING
            ),
        )
    );

}
