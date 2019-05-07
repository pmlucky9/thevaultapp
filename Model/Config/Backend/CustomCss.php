<?php

  
namespace TheVaultApp\Magento2\Model\Config\Backend;

use \Magento\Config\Model\Config\Backend\File;
 
class CustomCss extends File
{

    const UPLOAD_DIR = 'thevaultapp';

    /**
     * Return path to directory for upload file
     *
     * @return string
     * @throw \Magento\Framework\Exception\LocalizedException
     */
    protected function _getUploadDir()
    {
        return $this->_mediaDirectory->getAbsolutePath($this->_appendScopeInfo(self::UPLOAD_DIR));
    }

    /**
     * Getter for allowed extensions of uploaded files.
     *
     * @return string[]
     */    
    protected function _getAllowedExtensions() {
        return ['css'];
    }

    /**
     * Makes a decision about whether to add info about the scope.
     *
     * @return boolean
     */
    protected function _addWhetherScopeInfo() {
        return true;
    }

    protected function getTmpFileName() {
        $tmpName = null;
        $value = $this->getValue();

        $tmpName = (is_array($value) && isset($value['tmp_name'])) ? $value['tmp_name'] : null;

        return $tmpName;
    }

    public function beforeSave() {
        $value = $this->getValue();
        $deleteFlag = is_array($value) && !empty($value['delete']);
        $fileTmpName = $this->getTmpFileName();

        if ($this->getOldValue() && ($fileTmpName || $deleteFlag)) {
            $this->_mediaDirectory->delete(self::UPLOAD_DIR . '/' . $this->getOldValue());
        }

        return parent::beforeSave();
    }

}