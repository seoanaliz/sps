<?php
    Package::Load( 'SPS.Common' );

    /**
     * InitEditorsTool Action
     * @package    SPS
     * @subpackage Common
     * @author     Shuler
     */
    class InitEditorsTool {

        /**
         * Entry Point
         */
        public function Execute() {
            Logger::LogLevel( ELOG_DEBUG );

            $map = array();
            $targetFeeds = TargetFeedFactory::Get();
            foreach ($targetFeeds as $targetFeed) {
                if (!empty($targetFeed->vkIds)) {
                    $vkIds = explode(',', $targetFeed->vkIds);
                    foreach ($vkIds as $vkId) {
                        if (empty($map[$vkId])) $map[$vkId] = array();
                        $map[$vkId][] = $targetFeed->targetFeedId;
                    }
                }
            }

            foreach ($map as $vkId => $targetFeedIds) {
                $object = EditorFactory::GetOne(
                    array('vkId' => $vkId)
                );
                if (empty($object)) {
                    $object = new Editor();
                    $object->vkId = $vkId;
                    $object->statusId = 1;
                    try {
                        if (!empty($object->vkId)) {
                            $profiles = VkAPI::GetInstance()->getProfiles(array('uids' => $object->vkId, 'fields' => 'photo'));
                            $profile = current($profiles);
                            $object->firstName = $profile['first_name'];
                            $object->lastName = $profile['last_name'];
                            $object->avatar = $profile['photo'];
                        }
                    } catch (Exception $Ex) {
                        Logger::Error($Ex);
                    }

                    $saveResult = EditorFactory::Add($object);
                } else {
                    try {
                        if (!empty($object->vkId)) {
                            $profiles = VkAPI::GetInstance()->getProfiles(array('uids' => $object->vkId, 'fields' => 'photo'));
                            $profile = current($profiles);
                            $object->firstName = $profile['first_name'];
                            $object->lastName = $profile['last_name'];
                            $object->avatar = $profile['photo'];
                        }
                    } catch (Exception $Ex) {
                        Logger::Error($Ex);
                    }
                    $saveResult = EditorFactory::UpdateByMask($object, array('targetFeedIds', 'firstName', 'lastName', 'avatar'), array('editorId' => $object->editorId));
                }

                if ($saveResult) {
                    $userFeeds = array();
                    foreach ($targetFeedIds as $targetFeedId) {
                        $UserFeed = new UserFeed();
                        $UserFeed->vkId = $object->vkId;
                        $UserFeed->targetFeedId = $targetFeedId;
                        $UserFeed->role = UserFeed::ROLE_EDITOR;
                        $userFeeds[] = $UserFeed;
                    }
                    UserFeedFactory::AddRange($userFeeds);
                }

                sleep(1);
            }
        }
    }
?>