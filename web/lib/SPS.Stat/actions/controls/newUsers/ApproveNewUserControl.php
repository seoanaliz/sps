<?php
    Package::Load( 'SPS.Stat' );

    /**
     * addNewUserControl Action
     * @package    SPS
     * @subpackage Stat
     */

    class ApproveNewUserControl extends BaseControl {

        /**
         * Entry Point
         */
        public function Execute() {
//            error_reporting( 0 );
            $result = array(
                'success' => false
            );
            $act        = strtolower( Request::getString('act'));
            $request_id = Request::getInteger( 'newUserReqId' );

            $request = NewUserRequestFactory::GetById( $request_id );
            if ( $act === 'approve') {
                $request->statusId = StatusUtility::Queued;
            } elseif( $act === 'reject') {
                $request->statusId = StatusUtility::Finished;
            } else {
                die( ObjectHelper::ToJSON($result));
            }

            $result['success'] = NewUserRequestFactory::Update( $request );
            die( ObjectHelper::ToJSON($result));
        }
    }
?>