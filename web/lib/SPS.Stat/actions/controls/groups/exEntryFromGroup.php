<?php
    Package::Load( 'SPS.Stat' );

    /**
     * addPrice Action
     * @package    SPS
     * @subpackage Stat
     */

    //subtract public from group
    class exEntryFromGroup {

        /**
         * Entry Point
         */

        public function Execute() {
            error_reporting( 0 );

            $userId   = Request::getInteger ( 'userId'  );
            $groupId  = Request::getInteger ( 'groupId' );
            $entry_id = Request::getInteger ( 'publId'  );
            if ( !$entry_id )
                $entry_id = Request::getInteger ( 'entryId'  );
            $general  = Request::getInteger ( 'general' );
            $type     = Request::getString ( 'type' );

            $type_array = array( 'Stat', 'Mes', 'stat', 'mes');
            if ( !$type || !in_array( $type, $type_array, 1 ) )
            $type = 'Stat';
            $m_class    = $type . 'Groups';
            $general = $general ? $general : 0;

            if (!$groupId || !$userId || !$entry_id) {

                die(ERR_MISSING_PARAMS);
            }

            //todo не уверен, нужна ли проверка на "главность"
            if (    !$general
                    ||
                    ( $general && StatUsers::is_Sadmin( $userId ) )
                    ) {

                $m_class::extricate_entry( $groupId, $entry_id);

                echo  ObjectHelper::ToJSON(array('response' => true));

            } else {
                echo  ObjectHelper::ToJSON(array('response' => false));
                die();
            }
        }
    }
?>