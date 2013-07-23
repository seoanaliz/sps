<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 23.07.13
 * Time: 13:51
 * To change this template use File | Settings | File Templates.
 */
class GetInstPostInfo
{
    public function Execute()
    {
        $response   = array( 'success' => false );
        $link       = Request::getString('post_shortlink');
        if( $link ) {

            $post = InstObservedPostFactory::GetOne( array( 'link' => $link ));
            if( $post ) {
                $response['success']    = true;
                $response['data']['likes']      = $post->likes;
                $response['data']['comments']   = $post->comments;

                $subs = '-';
                if( $post->ref_start_subs && $post->ref_end_subs ) {
                    $subs = $post->ref_end_subs - $post->ref_start_subs;
                }
                $response['data']['subscribers']= $subs;
            }
        }

        die( ObjectHelper::ToJSON($response));
    }
}
