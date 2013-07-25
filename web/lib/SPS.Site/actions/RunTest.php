<?php

/**
 * @author kulikov
 */
class RunTest {
    public function Execute() {
        $Reflection = new ReflectionClass(get_class());
        $methodNames = array_map(function ($RM) {return $RM->getName();}, $Reflection->getMethods());
        $testMethodNames = preg_grep('/^test/', $methodNames);
        foreach ($testMethodNames as $testMethodName) {
            $result = $this->{$testMethodName}();
            if ($result) {
                Logger::Warning($testMethodName . ' passed!');
            } else {
                Logger::Error($testMethodName . ' failed!');
            }
        }
    }

    public function testGetSimilarSlugs() {
        $StatGroups = new StatGroups();
        $slugs = $StatGroups->getSimilarSlugs('biznes');
        return (count($slugs) > 1);
    }

    public function testGettingLargestNumberFromTheEndOfSlug() {
        $StatGroups = new StatGroups();
        $sampleData = array ('slug', 'slugextra', 'slug-', 'slug1', 'slug2', 'slug5');
        $largestNumber = $StatGroups->getLargestSuffixNumber($sampleData, 'slug');
        return ($largestNumber === 5);
    }

    public function testGettingLargestNumberFromTheEndOfSlugOneElement() {
        $StatGroups = new StatGroups();
        $sampleData = array ('slug');
        $largestNumber = $StatGroups->getLargestSuffixNumber($sampleData, 'slug');
        return ($largestNumber === 0);
    }

    public function testGettingLargestNumberFromTheEndOfSlugNoElements() {
        $StatGroups = new StatGroups();
        $sampleData = array ();
        $largestNumber = $StatGroups->getLargestSuffixNumber($sampleData, 'slug');
        return ($largestNumber === false);
    }

    public function testGettingLargestNumberFromTheEndOfSlugNonMatch() {
        $StatGroups = new StatGroups();
        $sampleData = array ('slug-', 'slugextra');
        $largestNumber = $StatGroups->getLargestSuffixNumber($sampleData, 'slug');
        return ($largestNumber === false);
    }
}

?>
