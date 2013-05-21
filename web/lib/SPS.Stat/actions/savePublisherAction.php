<?php
class savePublisherAction
{

    /**
     * Entry Point
     */
    public function Execute()
    {
        $at = Request::getString( 'access_token');
        $user_id = AuthVkontakte::IsAuth();
        $result = array( 'response' => false );

        if( !$user_id ) {
            die(ObjectHelper::ToJSON( $result ));
        }
        $check = UserFeedFactory::Get( array( 'role' => UserFeed::ROLE_ADMINISTRATOR, 'vkId' => $user_id ));
        if( empty( $check) || !VkHelper::check_at( $at ))
            die(ObjectHelper::ToJSON( $result ));

        $publisher = PublisherFactory::GetOne( array( 'vk_id' => $user_id ));
        if( empty( $publisher )) {
            $user_info = StatUsers::get_vk_user_info( $user_id);
            $user_info = current( $user_info );
            $publisher = new Publisher();
            $publisher->name = $user_info['name'];
            $publisher->status = StatusUtility::Enabled;
            $publisher->vk_app = 2;
            $publisher->vk_id = $user_id;
            $publisher->vk_seckey = 2;
            $publisher->vk_token = $at;
            $publisher->statusId = StatusUtility::Enabled;
            $id = PublisherFactory::Add( $publisher, array(BaseFactory::WithReturningKeys) );
        } else {
            $id = $publisher->publisherId;
            $publisher->vk_token = $at;
            $publisher->vk_app   = 2;
            $result['response']  = PublisherFactory::Update( $publisher );
        }

        if ( !$id ) {
            die(ObjectHelper::ToJSON( $result ));
        }

        $feed_ids = ArrayHelper::GetObjectsFieldValues( $check, array( 'targetFeedId' ));
        $tfp = array();
        foreach( $feed_ids as $target_feed_id ) {
            $target_feed_publisher = new TargetFeedPublisher();
            $target_feed_publisher->publisherId  = $id;
            $target_feed_publisher->targetFeedId = $target_feed_id;
            $tfp[] = $target_feed_publisher;
        }
        if( !empty( $tfp )) {
            $result['response'] = TargetFeedPublisherFactory::AddRange( $tfp );
        }
        die(ObjectHelper::ToJSON( $result ));
    }
}
?>
