<?php

namespace webignition\HtmlDocumentLinkUrlFinder;

class Configuration {

    /**
     * @var bool
     */
    private $ignoreFragmentInUrlComparison = false;


    /**
     * @return Configuration
     */
    public function enableIgnoreFragmentInUrlComparison() {
        $this->ignoreFragmentInUrlComparison = true;
        return $this;
    }


    /**
     * @return Configuration
     */
    public function disableIgnoreFragmentInUrlComparison() {
        $this->ignoreFragmentInUrlComparison = false;
        return $this;
    }


    /**
     * @return bool
     */
    public function ignoreFragmentInUrlComparison() {
        return $this->ignoreFragmentInUrlComparison;
    }

}