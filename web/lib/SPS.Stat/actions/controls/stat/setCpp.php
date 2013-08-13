<?php
/**
 * setCpp      Action
 * @package    SPS
 * @subpackage Stat
 * @author     kulikov
 * @task       #18899
 */
class setCpp {

    public function Execute() {
        $result = array('success' => false);
        $userVkId = AuthVkontakte::IsAuth();
        if ($userVkId) {
            $intId = Request::getInteger('intId');
            $cppString = trim(Request::getString('cpp'));
            $cpp = (int) $cppString;
            // если появятся дробные значения, is_numeric нужно будет заменить
            if (is_numeric($cppString) && $cpp > 0 && $cpp <= 99999) {
                if ($intId) {
                    // проверим, что это действительно фид пользователя
                    $targetFeed = TargetFeedFactory::GetOne( array( 'externalId' => $intId));
                    if( $targetFeed ) {
                        $accessUtility = new TargetFeedAccessUtility($userVkId);
                        if ($accessUtility->moreThenAuthor( $targetFeed->targetFeedId)) {
                            $vkPublic = new VkPublic();
                            $vkPublic->cpp = $cpp;
                            $time = time();
                            $vkPublic->cppChange = "1;$userVkId;$time";
                            $updateResult = VkPublicFactory::UpdateByMask(
                                $vkPublic,
                                array('cpp','cppChange'),
                                array('vk_id' => $intId)
                            );
                            if ($updateResult) {
                                $result['success'] = true;
                                $result['cpp'] = $vkPublic->cpp;
                            }
                        }
                    }
                }
            } else {
                $result['validation'] = 1; // Просто указываем, что не прошли валидацию
            }
        }
        echo ObjectHelper::ToJSON($result);
    }
}
?>