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
            if (!is_numeric($cppString) || $cpp < 0 || $cpp > 2147483646) {
                $result['validation'] = '-';
            } else if ($intId) {
               // проверим, что это действительно фид пользователя
                $userFeed = UserFeedFactory::GetOne(array(
                    'vkId' => $userVkId,
                    'targetFeedId' => $intId
                    // проверить роль?
                ));
                /////////////////////////////////////////////////////
                //             ХАРДКОД, НЕКРАСИВО              //
                /////////////////////////////////////////////////////
                if (TRUE || $userFeed) {
                    $vkPublic = VkPublicFactory::GetOne(array('vk_public_id' => $intId)); // можно ли сделать, не получая?
                    $vkPublic->cpp = $cpp;
                    $time = time();
                    $vkPublic->cppChange = "1;$userVkId;$time"; 
                    $updateResult = VkPublicFactory::Update($vkPublic, array(BaseFactory::WithReturningKeys => true));
                    if ($updateResult) {
                        $result['success'] = true;
                        $result['cpp'] = $vkPublic->cpp;
                    }
                }
            }
        }
        echo ObjectHelper::ToJSON($result);
    }
}
?>