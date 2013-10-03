<?php
/**
 * setCpp      Action
 * @package    SPS
 * @subpackage Stat
 * @author     kulikov
 * @task       #18899
 */
class setCpp {
    //на проде - 435
    private $cheapGroupId = 435;

    public function Execute() {
        $result = array('success' => false);
        $userVkId = AuthVkontakte::IsAuth();
        if ($userVkId) {
            $intId = Request::getInteger('intId');
            $cppString = trim(Request::getString('cpp'));
            $cpp = (int) $cppString;
            // если появятся дробные значения, is_numeric нужно будет заменить
            if (is_numeric($cppString) && $cpp >= 0 && $cpp <= 99999) {
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
                                $this->checkIfCheap($intId, $cppString);
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

    public function checkIfCheap($vkId, $price) {
        $vkPublic = VkPublicFactory::GetOne(array( 'vk_id' => $vkId));
        if ( isset( $vkPublic->viewers_week )) {
            if ( $price && (
                ($vkPublic->viewers_week <= 10000 && $price <= 50)  ||
                ($vkPublic->viewers_week <= 50000 && $vkPublic->viewers_week > 10000&& $price <= 200)  ||
                ($vkPublic->viewers_week <= 100000 && $vkPublic->viewers_week > 50000 && $price <= 400)  ||
                ($vkPublic->viewers_week <= 200000 && $vkPublic->viewers_week > 100000 && $price <= 800)  ||
                ($vkPublic->viewers_week <= 500000 && $vkPublic->viewers_week > 200000 && $price <= 1500)  ||
                ($vkPublic->viewers_week <= 1000000 && $vkPublic->viewers_week > 500000 && $price <= 3000)  ||
                ($vkPublic->viewers_week <= 1500000 && $vkPublic->viewers_week > 1000000 && $price <= 4500)  ||
                ($vkPublic->viewers_week <= 2000000 && $vkPublic->viewers_week > 1500000 && $price <= 6000)  ||
                ($vkPublic->viewers_week <= 3000000 && $vkPublic->viewers_week > 2000000 && $price <= 9000) )) {


                $ge = new GroupEntry(
                    $this->cheapGroupId,
                    $vkPublic->vk_public_id,
                    Group::STAT_GROUP,
                    AuthVkontakte::IsAuth()
                );

                GroupEntryFactory::Add($ge);
            } else {
                GroupEntryFactory::DeleteByMask( array(
                    'groupId'   =>  $this->cheapGroupId,
                    'entryId'   =>  $vkPublic->vk_public_id,
                    'sourceType'=>  Group::STAT_GROUP,
                ));
            }
        }
    }

}
?>