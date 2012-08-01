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
                    $object->targetFeedIds = $targetFeedIds;
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

                    EditorFactory::Add($object);
                } else {
                    $object->targetFeedIds = $targetFeedIds;
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
                    EditorFactory::UpdateByMask($object, array('targetFeedIds', 'firstName', 'lastName', 'avatar'), array('editorId' => $object->editorId));
                }
                sleep(1);
            }
        }
    }
?>