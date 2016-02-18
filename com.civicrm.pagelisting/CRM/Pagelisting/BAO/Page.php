<?php
/**
 * @package Pagelisting
 */

require_once 'CRM/Pagelisting/DAO/Page.php';

class CRM_Pagelisting_BAO_Page extends CRM_Pagelisting_DAO_Page {

  /**
   * class constructor
   */
  function __construct() {
    parent::__construct();
  }                                                             

  /**
  *  Get Contacts pages
  *  Created By : Mitul Patel
  *  Date : Feb 18, 2016
  * 
  * @param integer $contactId
  * @return mixed
  */
  public static function getPcpDashboardInfoWithFullDetail($contactId){
    $links = CRM_PCP_BAO_PCP::pcpLinks();
    $query = "
                SELECT 
                    * 
                FROM 
                    civicrm_pcp pcp
                WHERE 
                    pcp.is_active = 1
                AND 
                    pcp.contact_id = %1
                ORDER BY 
                    page_type, page_id";

    $params = array(1 => array($contactId, 'Integer'));

    $pcpInfoDao = CRM_Core_DAO::executeQuery($query, $params);
    $pcpInfo = array();
    $hide = $mask = array_sum(array_keys($links['all']));
    $contactPCPPages = array();
    $event = CRM_Event_PseudoConstant::event(NULL, FALSE, "( is_template IS NULL OR is_template != 1 )");
    $contribute = CRM_Contribute_PseudoConstant::contributionPage();
    $pcpStatus = CRM_Contribute_PseudoConstant::pcpStatus();
    $approved = CRM_Utils_Array::key('Approved', $pcpStatus);

    while ($pcpInfoDao->fetch()) {
      $mask = $hide;
      if ($links) {
        $replace = array(
          'pcpId' => $pcpInfoDao->id,
          'pcpBlock' => $pcpInfoDao->pcp_block_id,
          'pageComponent' => $pcpInfoDao->page_type,
        );
      }

      $pcpLink = $links['all'];
      $class = '';

      if ($pcpInfoDao->status_id != $approved || $pcpInfoDao->is_active != 1) {
        $class = 'disabled';
        if (!$pcpInfoDao->tellfriend) {
          $mask -= CRM_Core_Action::DETACH;
        }
      }

      if ($pcpInfoDao->is_active == 1) {
        $mask -= CRM_Core_Action::ENABLE;
      }
      else {
        $mask -= CRM_Core_Action::DISABLE;
      }    
      
      $editLink = array(2 => $pcpLink[2]);
      $action = CRM_Core_Action::formLink($editLink, $mask, $replace, ts('more'),
        FALSE, 'pcp.dashboard.active', 'PCP', $pcpInfoDao->id);
      $component = $pcpInfoDao->page_type;
      $pageTitle = CRM_Utils_Array::value($pcpInfoDao->page_id, $$component);
      
      $contributInfo = CRM_Pagelisting_BAO_Page::getPCPContributions($pcpInfoDao->id);
      
      $pcpInfo[] = array(
        'pageTitle' => $pageTitle,
        'pcpId' => $pcpInfoDao->id,
        'pcpTitle' => $pcpInfoDao->title,
        'pcpStatus' => $pcpStatus[$pcpInfoDao->status_id],
        'raisAmount' =>  $contributInfo['rais_amount'],
        'goalAmount' =>  $pcpInfoDao->goal_amount,
        'totalContribution' =>  $contributInfo['total_no_contribution'],
        'action' => $action,
        'class' => $class,
      );
      $contactPCPPages[$pcpInfoDao->page_type][] = $pcpInfoDao->page_id;
    }
    return $pcpInfo;
 }
 
 
 /**
 *  Get total number of the count and amount by the pcpId
 *  Created By : Mitul Patel
 *  Date : Feb 18, 2016
 * 
 *  It return rais_amount, total_no_contribution
 *  
 * @param mixed $pcpId
 */
  public static function getPCPContributions($pcpId) {
     $query = "
            SELECT 
                count(*) AS total_no_contribution, 
                SUM(cc.total_amount) AS rais_amount
            FROM 
                civicrm_contribution cc
            LEFT JOIN 
                civicrm_contribution_soft cs 
            ON 
                cc.id = cs.contribution_id
            WHERE 
                    cs.pcp_id = {$pcpId}
                AND 
                    cs.pcp_display_in_roll = 1
                AND 
                    contribution_status_id = 1
                AND 
                    is_test = 0";
                    
    $dao = CRM_Core_DAO::executeQuery($query);
    $honor = array( "rais_amount" => 0 , "total_no_contribution" => 0);
    while ($dao->fetch()){
      $honor['total_no_contribution'] = $dao->total_no_contribution;
      $honor['rais_amount'] = $dao->rais_amount?$dao->rais_amount:0;
    }
    return $honor;
  }

}
