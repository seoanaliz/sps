<?php
    Package::Load( 'SPS.Common' );
    
    /**
     * MemcacheTest Action
     * @package SPS
     * @subpackage Common
     * @author eugeneshulepin
     */
    class MemcacheTest {
    
        /**
         * Entry Point
         */
        public function Execute() {
            Logger::LogLevel(ELOG_DEBUG);

            $tags = MemcacheHelper::Get(BaseFactoryPrepare::GetCacheTags(TargetFeedFactory::$mapping));
            Logger::VarDump($tags);
        }
    }
?>