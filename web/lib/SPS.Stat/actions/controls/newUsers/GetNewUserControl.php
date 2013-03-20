<?php
    Package::Load( 'SPS.Stat' );

    /**
     * getNewUserControl Action
     * @package    SPS
     * @subpackage Stat
     */

    class getNewUserControl extends BaseControl {

        /**
         * Entry Point
         */
        public function Execute() {
//            error_reporting( 0 );

            $status = Request::GetInteger('status');
            $offset = Request::GetInteger('offset');
            $limit  = Request::GetInteger('limit');
            if( !$status )
                $status = 1;
            $search = array('statusId' => $status);
            if( $limit ) {
                $search[BaseFactoryPrepare::Page] = floor( $offset/$limit );
                $search[BaseFactoryPrepare::PageSize] = $limit;
            }
            $newUsers = NewUserRequestFactory::Get( $search );
            $response = array();
            foreach( $newUsers as $newUser ) {
                $newUser = (array)$newUser;
                $newUser['createdAt'] = $newUser['createdAt']->format('U');
                $newUser['publicIds'] = implode(',', $newUser['publicIds']);
                $response[$newUser['newUserRequestId']] = $newUser;
            }
            die( ObjectHelper::ToJSON( $response ));
        }
    }
?>