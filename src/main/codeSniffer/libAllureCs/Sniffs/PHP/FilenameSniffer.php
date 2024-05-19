<?php

class FilenameSniffer implements PHP_CodeSniffer_Sniff {
    public function register()
    {
        return array(
            T_OPEN_TAG
        );
    }

    public function process(PHP_CodeSniffer_File $file, $stackPtr) {
        $phpCsFile->addError('test', $stackPtr, 'Found', array('test'));
    }
}
