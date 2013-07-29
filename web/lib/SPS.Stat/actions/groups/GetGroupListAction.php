<?php
    /**
     * Get Group List Action
     * 
     * @package Stat
     * @subpackage
     */
class GetGroupListAction extends BaseGetAction {

    /**
     * Constructor
     */
    public function __construct() {
        $this->options = array(
              BaseFactory::WithoutDisabled => false
            , BaseFactory::WithLists     => false
        );

        parent::$factory = new GroupFactory();
        $this->connectionName = 'tst';
        $this->search = array(
            'type'      =>  GroupsUtility::Group_Global,
            'source'    =>  Group::STAT_GROUP
        );
    }

    public function getSearch()
    {
        $search = array(
            'type'      =>  GroupsUtility::Group_Global,
            'source'    =>  Group::STAT_GROUP
        );
        $old_search = parent::getSearch();
        if ( $old_search ) {
            $search = array_merge($search, $old_search);
        }

        return $search;
    }


    /**
     * Set Foreign Lists
     */
    protected function setForeignLists() {
    }
}