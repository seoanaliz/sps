<?php
    Package::Load( 'SPS.Stat' );

    /**
     * addNewUserControl Action
     * @package    SPS
     * @subpackage Stat
     */

    class addNewUserControl extends BaseControl {

        /**
         * Entry Point
         */
        public function Execute() {
//            error_reporting( 0 );
            $result = array(
                'success' => false
            );

            if ( !$this->vkId ) {
                $result['message'] = 'no vk id';
                die( ObjectHelper::ToJSON($result));
            }

            $email      = Request::GetString('email');
            $public_ids = Request::GetString('publicIds');
            $public_ids_arr = explode( ',', $public_ids );
            if ( !$email || !is_numeric($public_ids_arr[0]) || !$public_ids_arr[0] ) {
                $result['message'] = 'Wrong data';
                die( ObjectHelper::ToJSON($result));
            }
//            if ( !filter_var($email, FILTER_VALIDATE_EMAIL)) {
//                $result['message'] = 'Wrong email';
//                die( ObjectHelper::ToJSON($result));
//            }
            $newUser = NewUserRequestFactory::GetOne( array('vkId' => $this->vkId));
            if( $newUser ) {
                $old_publics = is_array( $newUser->publicIds ) ? $newUser->publicIds: array();
                $newUser->publicIds = array_unique( array_merge( $old_publics, $public_ids_arr));
                $newUser->email = $email;
                $result['success'] = NewUserRequestFactory::Update($newUser);
            } else {
                $newUser = new NewUserRequest();
                $newUser->createdAt = DateTimeWrapper::Now();
                $newUser->email = $email;
                $newUser->publicIds = $public_ids_arr;
                $newUser->vkId = $this->vkId;
                $newUser->statusId = StatusUtility::Enabled;
                $result['success'] = NewUserRequestFactory::Add($newUser, array(BaseFactory::WithReturningKeys => true));
            }
            if( $result['success']) {
                $result['newUserReqId'] = $newUser->newUserRequestId;
            }
            die( ObjectHelper::ToJSON($result));
        }
    }
?>