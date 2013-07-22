<?php
/**
 * get new posts from observed accounts
 * */
class WallChecker
{

    const PAUSE = 0.3;
    public function Execute()
    {

        set_time_limit(55);
        $instAccounts = InstObservedAccountFactory::Get(array('status' => StatusUtility::Enabled));
        echo 'аккаунтов для проверки: ',  count( $instAccounts), '<br>';

        $params = array('count' => 2);
        foreach( $instAccounts as $acc ) {
            $method = 'users/' . $acc->id . '/media/recent/';
            $last_base_post = InstObservedPostFactory::GetOne(
                array( 'author_id' => $acc->id ),
                array(
                    BaseFactory::CustomSql => 'ORDER BY created_at DESC LIMIT 1')
            );
            $id_for_check = ( empty( $last_base_post )) ? 0 : $last_base_post->id;
            try {
                $posts = InstagramHelper::api_request( $method, $params );
            } catch ( Exception $e ) {
                continue;
            }
            print_r(count($posts));
            foreach( $posts as $post ) {
                if ( strpos( $post->id, $id_for_check)) {
                    //todo errorlog
                    continue;
                }

                $post_id                = explode('_',$post->id);
                $reference_id           = $this->get_reference_from_text($post->caption->text);

                if ( !$reference_id || count($post_id) != 2) {
                    //todo errorlog
                    continue;
                }
                $ref_subscribers_count  = $this->get_ref_user_subs( $reference_id );

                $a = new InstObservedPost();
                $a->id              = $post_id[0];
                $a->reference_id    = $reference_id;
                $a->comments        = $post->comments->count;
                $a->likes           = $post->likes->count;
                $a->posted_at       = DateTimeWrapper::Now();
                $a->updated_at      = DateTimeWrapper::Now();
                $a->ref_start_subs  = $ref_subscribers_count;
                $a->status = StatusUtility::Enabled;
                $a->author_id       = $post->user->id;

                InstObservedPostFactory::Add($a);
                sleep( self::PAUSE );
            }
        }

    }

    public function get_reference_from_text( $text ) {
        if( preg_match( '/@(.+)\s?/', $text, $matches)) {
            try {
                $user = InstagramHelper::api_request( 'users/search', array('q' => $matches[1]));
                if( isset ($user[0]->id )) {
                    return $user[0]->id;
                }
            } catch (Exception $e ) {
                //todo errorlog
            }
        }
        return false;
    }

    public function get_ref_user_subs( $ref_id) {
        try {
            $user = InstagramHelper::api_request( 'users/' . $ref_id, array());
            if( isset ($user->id )) {
                return $user->counts->followed_by;
            }
        } catch (Exception $e ) {
            //todo errorlog
        }

        return false;
    }
}
