<?php
    Package::Load( 'SPS.Stat' );
    Package::Load( 'SPS.Site' );
    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */
    class selectSAdmin extends wrapper {


        public function Execute() {

            $adminId    = Request::getInteger( 'adminId' );
            $publId     = Request::getInteger( 'publId' );
            $groupName  = Request::getString(  'groupName' );
            $userId     = Request::getInteger( 'userId' );
            if (!$adminId || !$publId || !$groupName || !$userId) {
                echo ObjectHelper::ToJSON(array('response' => false));
                die();
            }
            $sql = sprintf('UPDATE
                                publ_rels_names
                            SET
                                selected_admin=%4$d
                            WHERE
                                publ_id=%1$d AND group_name=\'%2$s\' AND user_id=%3$d' ,
                               $publId,         $groupName,         $userId
                         ,$adminId);
            $this->db_wrap('query', $sql);
            echo ObjectHelper::ToJSON(array('response' => true));
        }
    }

?>