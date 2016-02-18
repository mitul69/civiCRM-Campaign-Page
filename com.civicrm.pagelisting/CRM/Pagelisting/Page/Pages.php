<?php

require_once 'CRM/Pagelisting/DAO/Page.php';  

class CRM_Pagelisting_Page_Pages extends CRM_Core_Page {
    
    var $_contactId = null;
    public function __construct() {
        parent::__construct();
        $this->_contactId = CRM_Utils_Request::retrieve('id', 'Positive', $this);

        $session = CRM_Core_Session::singleton();
        $userID = $session->get('userID');

        if (!$this->_contactId) {
          $this->_contactId = $userID;
        }
    }
  function getBAOName() {
        return 'CRM_Pagelisting_BAO_PAGE';
  }

  public function run() {
    CRM_Utils_System::setTitle(ts('Pages'));                      
    
    $pcpInfo = CRM_Pagelisting_BAO_PAGE::getPcpDashboardInfoWithFullDetail($this->_contactId);
    $this->assign('pcpInfo', $pcpInfo);

   
    parent::run();
  }
}
