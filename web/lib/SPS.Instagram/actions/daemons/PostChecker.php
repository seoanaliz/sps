<?php
/**
 * get new posts from observed accounts
 * */
class PostChecker
{

    const PAUSE = 0.3;
    /** частота обновления данных о посте(значение) в зависимости от времени с его создания(ключ)
    * после - наблюдение прекращается
    * */
    public $update_rules = array(
        '1 hour'     =>   '4 minutes',
        '3 hours'    =>   '14 minutes',
        '1 day'      =>   '28 minutes',
    );

    public function Execute()
    {
        set_time_limit(180);

        $posts = $this->get_posts_for_update();
        foreach( $posts as $post ) {
            $end_subs = $this->get_ref_user_subs( $post->reference_id);
            if( $end_subs) {
                $post->ref_end_subs = $end_subs;
                $post->updated_at = DateTimeWrapper::Now();
            }  else {
                //todo errorlog
            }
        }

        InstObservedPostFactory::UpdateRange( $posts);

    }

    /** @return InstObservedPost[] */
    public function get_posts_for_update()
    {
        $posts = array();
        foreach( $this->update_rules as $interval => $frequency) {
           $posts += InstObservedPostFactory::Get(
               array(
                   'posted_atGE'  =>    DateTimeWrapper::Now()->modify('-' . $interval  ),
                   'updated_atLE' =>    DateTimeWrapper::Now()->modify('-' . $frequency ),
               )
           );
        }
        echo 'постов под обновление: ',  count( $posts), '<br>';
        return $posts;
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
