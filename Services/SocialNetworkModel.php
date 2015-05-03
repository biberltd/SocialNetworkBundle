<?php
/**
 * SocialNetworkBundle Class
 *
 * This class acts as a database proxy model for SocialNetworkBundle functionalities.
 *
 * @vendor      BiberLtd
 * @package		Core\Bundles\SocialNetworkBundle
 * @subpackage	Services
 * @name	    SocialNetworkBundle
 *
 * @author		Can Berkol
 *
 * @copyright   Biber Ltd. (www.biberltd.com)
 *
 * @version     1.0.3
 * @date        03.05.2015
 *
 */
namespace BiberLtd\Bundle\SocialNetworkBundle\Services;

/** Extends CoreModel */
use BiberLtd\Bundle\CoreBundle\CoreModel;

/** Entities to be used */
use BiberLtd\Bundle\CoreBundle\Responses\ModelResponse;
use BiberLtd\Bundle\SocialNetworkBundle\Entity as BundleEntity;
use BiberLtd\Bundle\SiteManagementBundle\Entity as SMEntity;

/** Helper Models */
use BiberLtd\Bundle\SiteManagementBundle\Services as SMService;

/** Core Service*/
use BiberLtd\Bundle\CoreBundle\Services as CoreServices;
use BiberLtd\Bundle\CoreBundle\Exceptions as CoreExceptions;

class SocialNetworkModel extends CoreModel {
    /**
     * @name            __construct()
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.3
     *
     * @param           object          $kernel
     * @param           string          $db_connection  Database connection key as set in app/config.yml
     * @param           string          $orm            ORM that is used.
     */
    public function __construct($kernel, $db_connection = 'default', $orm = 'doctrine'){
        parent::__construct($kernel, $db_connection, $orm);

        $this->entity = array(
            's'         => array('name' => 'SiteManagementBundle:Site', 'alias' => 's'),
            'sn'        => array('name' => 'SocialNetworkBundle:SocialNetwork', 'alias' => 'sn'),
        );
    }
    /**
     * @name            __destruct()
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     */
    public function __destruct(){
        foreach($this as $property => $value) {
            $this->$property = null;
        }
    }
    /**
     * @name 			deleteSocialNetwork()
     *
     * @since			1.0.0
     * @version         1.0.3
     * @author          Can Berkol
     *
     * @use             $this->deleteSocialNetworks()
     *
     * @param           mixed           $network
     *
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function deleteSocialNetwork($network){
        return $this->deleteSocialNetworks(array($network));
    }
    /**
     * @name 			deleteSocialNetworks()
     *
     * @since			1.0.0
     * @version         1.0.3
     * @author          Can Berkol
	 *
     * @use             $this->createException()
     *
     * @param           array           $collection
     *
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
	public function deleteSocialNetworks($collection) {
		$timeStamp = time();
		if (!is_array($collection)) {
			return $this->createException('InvalidParameterValueException', 'Invalid parameter value. Parameter must be an array collection', 'E:S:001');
		}
		$countDeleted = 0;
		foreach($collection as $entry){
				if($entry instanceof BundleEntity\SocialNetwork){
				$this->em->remove($entry);
				$countDeleted++;
			}
			else{
				$response = $this->getFile($entry);
				if(!$response->error->exists){
					$entry = $response->result->set;
					$this->em->remove($entry);
					$countDeleted++;
				}
			}
		}
		if($countDeleted < 0){
			return new ModelResponse(null, 0, 0, null, true, 'E:E:001', 'Unable to delete all or some of the selected entries.', $timeStamp, time());
		}
		$this->em->flush();

		return new ModelResponse(null, 0, 0, null, false, 'S:D:001', 'Selected entries have been successfully removed from database.', $timeStamp, time());
	}

    /**
     * @name 			doesSocialNetworkExist()
     *
     * @since			1.0.0
     * @version         1.0.3
     * @author          Can Berkol
     *
     * @use             $this->getSocialNetwork()
     *
     * @param           mixed           $network
     * @param           bool            $bypass         If set to true does not return response but only the result.
     *
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
	public function doesSocialNetworkExist($network, $bypass = false) {
		$timeStamp = time();
		$exist = false;

		$response = $this->getSocialNetwork($network);

		if ($response->error->exists) {
			if($bypass){
				return $exist;
			}
			$response->result->set = false;
			return $response;
		}

		$exist = true;

		if ($bypass) {
			return $exist;
		}
		return new ModelResponse(true, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
	}
    /**
     * @name 			getSocialNetwork()
     *
     * @since			1.0.0
     * @version         1.0.3
     * @author          Can Berkol
     *
     * @use             $this->createException()
     * @use             $this->listSocialNetworks()
     *
     * @param           mixed           $network
     *
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
	public function getSocialNetwork($network) {
		$timeStamp = time();
		if($network instanceof BundleEntity\SocialNetwork){
			return new ModelResponse($network, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
		}
		$result = null;
		switch($network){
			case is_numeric($network):
				$result = $this->em->getRepository($this->entity['f']['name'])->findOneBy(array('id' => $network));
				break;
			case is_string($network):
				$result = $this->em->getRepository($this->entity['f']['name'])->findOneBy(array('code' => $network));
				break;
		}
		if(is_null($result)){
			return new ModelResponse($result, 0, 0, null, true, 'E:D:002', 'Unable to find request entry in database.', $timeStamp, time());
		}

		return new ModelResponse($result, 1, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
	}
    /**
     * @name 			insertSocialNetwork()
     *
     * @since			1.0.0
     * @version         1.0.3
	 *
     * @author          Can Berkol
     *
     * @use             $this->insertSocialNetworks()
     *
     * @param           mixed           $network
     *
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function insertSocialNetwork($network){
        return $this->insertSocialNetworks(array($network));
    }
    /**
     * @name 			insertSocialNetworks()
     *
     * @since			1.0.0
     * @version         1.0.3
     * @author          Can Berkol

     * @use             $this->createException()
     *
     * @param           array           $collection
     *
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
	public function insertSocialNetworks($collection) {
		$timeStamp = time();
		if (!is_array($collection)) {
			return $this->createException('InvalidParameterValueException', 'Invalid parameter value. Parameter must be an array collection', 'E:S:001');
		}
		$countInserts = 0;
		$insertedItems = array();
		foreach($collection as $data){
			if($data instanceof BundleEntity\SocialNetwork){
				$entity = $data;
				$this->em->persist($entity);
				$insertedItems[] = $entity;
				$countInserts++;
			}
			else if(is_object($data)){
				$entity = new BundleEntity\SocialNetwork();
				foreach($data as $column => $value){
					$set = 'set'.$this->translateColumnName($column);
					switch($column){
						case 'site':
							$sModel = $this->kernel->getContainer()->get('sitemanagement.model');
							$response = $sModel->getSite($value);
							if(!$response->error->exists){
								$entity->$set($response->result->set);
							}
							unset($response, $sModel);
							break;
						default:
							$entity->$set($value);
							break;
					}
				}
				$this->em->persist($entity);
				$insertedItems[] = $entity;
				$countInserts++;
			}
		}
		if($countInserts > 0){
			$this->em->flush();
			return new ModelResponse($insertedItems, $countInserts, 0, null, false, 'S:D:003', 'Selected entries have been successfully inserted into database.', $timeStamp, time());
		}
		return new ModelResponse(null, 0, 0, null, true, 'E:D:003', 'One or more entities cannot be inserted into database.', $timeStamp, time());
	}
    /**
     * @name 			listAllSocialNetworks()
     *
     * @since			1.0.1
     * @version         1.0.3
     * @author          Can Berkol
     *
     * @use             $this->listSocialNetworks()
     *
     * @param           array           $sortOrder
     * @param           array           $limit
     *
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function listAllSocialNetworks($sortOrder = null, $limit = null){
        return $this->listSocialNetworks(null, $sortOrder, $limit);
    }
    /**
     * @name 			listFacebookAccounts()
     *
     * @since			1.0.0
     * @version         1.0.3
     * @author          Can Berkol
     *
     * @use             $this->listSocialNetworks()
     *
     * @param           array           $sortOrder
	 * @param           array           $limit
	 * @param           array           $site
     *
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function listFacebookAccounts($sortOrder = null, $limit = null, $site = null){
		$timeStamp = time();
        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => array('column' => $this->entity['sn']['alias'].'.url', 'comparison' => 'like', 'value' => 'facebook.com'),
                )
            )
        );
		if(!is_null($site)){
			$sModel = $this->kernel->getContainer()->get('sitemanagement.model');
			$response = $sModel->getSite($site);
			if($response->error->exist){
				return $this->createException('EntityDoesNotExist', 'Site with id / url_key '.$site.' does not exist in database.', 'E:D:002');
			}
			$site = $response->result->set;
			$filter[] = array(
				'glue' => 'and',
				'condition' => array(
					array(
						'glue' => 'and',
						'condition' => array('column' => $this->entity['sn']['alias'].'.site', 'comparison' => 'like', 'value' => $site->getId()),
					)
				)
			);
		}
        $response = $this->listSocialNetworks($filter, $sortOrder, $limit);
		$response->stats->execution->start = $timeStamp;
		$response->stats->execution->end = time();

		return $response;
    }

	/**
	 * @name 			listGiggemAccounts()
	 *
	 * @since			1.0.0
	 * @version         1.0.3
	 * @author          Can Berkol
	 *
	 * @use             $this->listSocialNetworks()
	 *
	 * @param           array           $sortOrder
	 * @param           array           $limit
	 * @param           array           $site
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function listGiggemAccounts($sortOrder = null, $limit = null, $site = null){
		$timeStamp = time();
		$filter[] = array(
			'glue' => 'and',
			'condition' => array(
				array(
					'glue' => 'and',
					'condition' => array('column' => $this->entity['sn']['alias'].'.url', 'comparison' => 'like', 'value' => 'giggem.com'),
				)
			)
		);
		if(!is_null($site)){
			$sModel = $this->kernel->getContainer()->get('sitemanagement.model');
			$response = $sModel->getSite($site);
			if($response->error->exist){
				return $this->createException('EntityDoesNotExist', 'Site with id / url_key '.$site.' does not exist in database.', 'E:D:002');
			}
			$site = $response->result->set;
			$filter[] = array(
				'glue' => 'and',
				'condition' => array(
					array(
						'glue' => 'and',
						'condition' => array('column' => $this->entity['sn']['alias'].'.site', 'comparison' => 'like', 'value' => $site->getId()),
					)
				)
			);
		}
		$response = $this->listSocialNetworks($filter, $sortOrder, $limit);
		$response->stats->execution->start = $timeStamp;
		$response->stats->execution->end = time();

		return $response;
	}
	/**
	 * @name 			listGplusAccounts()
	 *
	 * @since			1.0.0
	 * @version         1.0.3
	 * @author          Can Berkol
	 *
	 * @use             $this->listSocialNetworks()
	 *
	 * @param           array           $sortOrder
	 * @param           array           $limit
	 * @param           array           $site
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function listGplusAccounts($sortOrder = null, $limit = null, $site = null){
		$timeStamp = time();
		$filter[] = array(
			'glue' => 'and',
			'condition' => array(
				array(
					'glue' => 'and',
					'condition' => array('column' => $this->entity['sn']['alias'].'.url', 'comparison' => 'like', 'value' => 'google.com'),
				)
			)
		);
		if(!is_null($site)){
			$sModel = $this->kernel->getContainer()->get('sitemanagement.model');
			$response = $sModel->getSite($site);
			if($response->error->exist){
				return $this->createException('EntityDoesNotExist', 'Site with id / url_key '.$site.' does not exist in database.', 'E:D:002');
			}
			$site = $response->result->set;
			$filter[] = array(
				'glue' => 'and',
				'condition' => array(
					array(
						'glue' => 'and',
						'condition' => array('column' => $this->entity['sn']['alias'].'.site', 'comparison' => 'like', 'value' => $site->getId()),
					)
				)
			);
		}
		$response = $this->listSocialNetworks($filter, $sortOrder, $limit);
		$response->stats->execution->start = $timeStamp;
		$response->stats->execution->end = time();

		return $response;
	}
	/**
	 * @name 			listInstagramAccounts()
	 *
	 * @since			1.0.0
	 * @version         1.0.3
	 * @author          Can Berkol
	 *
	 * @use             $this->listSocialNetworks()
	 *
	 * @param           array           $sortOrder
	 * @param           array           $limit
	 * @param           array           $site
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function listInstagramAccounts($sortOrder = null, $limit = null, $site = null){
		$timeStamp = time();
		$filter[] = array(
			'glue' => 'and',
			'condition' => array(
				array(
					'glue' => 'and',
					'condition' => array('column' => $this->entity['sn']['alias'].'.url', 'comparison' => 'like', 'value' => 'instagram.com'),
				)
			)
		);
		if(!is_null($site)){
			$sModel = $this->kernel->getContainer()->get('sitemanagement.model');
			$response = $sModel->getSite($site);
			if($response->error->exist){
				return $this->createException('EntityDoesNotExist', 'Site with id / url_key '.$site.' does not exist in database.', 'E:D:002');
			}
			$site = $response->result->set;
			$filter[] = array(
				'glue' => 'and',
				'condition' => array(
					array(
						'glue' => 'and',
						'condition' => array('column' => $this->entity['sn']['alias'].'.site', 'comparison' => 'like', 'value' => $site->getId()),
					)
				)
			);
		}
		$response = $this->listSocialNetworks($filter, $sortOrder, $limit);
		$response->stats->execution->start = $timeStamp;
		$response->stats->execution->end = time();

		return $response;
	}

	/**
	 * @name 			listLinkedinAccounts()
	 *
	 * @since			1.0.0
	 * @version         1.0.3
	 * @author          Can Berkol
	 *
	 * @use             $this->listSocialNetworks()
	 *
	 * @param           array           $sortOrder
	 * @param           array           $limit
	 * @param           array           $site
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function listLinkedinAccounts($sortOrder = null, $limit = null, $site = null){
		$timeStamp = time();
		$filter[] = array(
			'glue' => 'and',
			'condition' => array(
				array(
					'glue' => 'and',
					'condition' => array('column' => $this->entity['sn']['alias'].'.url', 'comparison' => 'like', 'value' => 'linkedin.com'),
				)
			)
		);
		if(!is_null($site)){
			$sModel = $this->kernel->getContainer()->get('sitemanagement.model');
			$response = $sModel->getSite($site);
			if($response->error->exist){
				return $this->createException('EntityDoesNotExist', 'Site with id / url_key '.$site.' does not exist in database.', 'E:D:002');
			}
			$site = $response->result->set;
			$filter[] = array(
				'glue' => 'and',
				'condition' => array(
					array(
						'glue' => 'and',
						'condition' => array('column' => $this->entity['sn']['alias'].'.site', 'comparison' => 'like', 'value' => $site->getId()),
					)
				)
			);
		}
		$response = $this->listSocialNetworks($filter, $sortOrder, $limit);
		$response->stats->execution->start = $timeStamp;
		$response->stats->execution->end = time();

		return $response;
	}
	/**
	 * @name 			listPinterestAccounts()
	 *
	 * @since			1.0.0
	 * @version         1.0.3
	 * @author          Can Berkol
	 *
	 * @use             $this->listSocialNetworks()
	 *
	 * @param           array           $sortOrder
	 * @param           array           $limit
	 * @param           array           $site
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function listPinterestAccounts($sortOrder = null, $limit = null, $site = null){
		$timeStamp = time();
		$filter[] = array(
			'glue' => 'and',
			'condition' => array(
				array(
					'glue' => 'and',
					'condition' => array('column' => $this->entity['sn']['alias'].'.url', 'comparison' => 'like', 'value' => 'pinterest.com'),
				)
			)
		);
		if(!is_null($site)){
			$sModel = $this->kernel->getContainer()->get('sitemanagement.model');
			$response = $sModel->getSite($site);
			if($response->error->exist){
				return $this->createException('EntityDoesNotExist', 'Site with id / url_key '.$site.' does not exist in database.', 'E:D:002');
			}
			$site = $response->result->set;
			$filter[] = array(
				'glue' => 'and',
				'condition' => array(
					array(
						'glue' => 'and',
						'condition' => array('column' => $this->entity['sn']['alias'].'.site', 'comparison' => 'like', 'value' => $site->getId()),
					)
				)
			);
		}
		$response = $this->listSocialNetworks($filter, $sortOrder, $limit);
		$response->stats->execution->start = $timeStamp;
		$response->stats->execution->end = time();

		return $response;
	}
	/**
	 * @name            listSocialNetworks()
	 *
	 * @since           1.0.0
	 * @version         1.0.3
	 *
	 * @author          Can Berkol
	 * @author          Said Imamoglu
	 *
	 * @use             $this->createException()
	 *
	 * @param           array   $filter
	 * @param			array	$sortOrder
	 * @param			array	$limit
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 *
	 */
	public function listSocialNetworks($filter = null, $sortOrder = null, $limit = null) {
		$timeStamp = time();
		if (!is_array($sortOrder) && !is_null($sortOrder)) {
			return $this->createException('InvalidSortOrderException', '$sortOrder must be an array with key => value pairs where value can only be "asc" or "desc".', 'E:S:002');
		}

		$oStr = $wStr = $gStr = $fStr = '';

		$qStr = 'SELECT '.$this->entity['sn']['alias']
			.' FROM '.$this->entity['sn']['name'].' '.$this->entity['sn']['alias'];

		if (!is_null($sortOrder)) {
			foreach ($sortOrder as $column => $direction) {
				switch ($column) {
					case 'id':
					case 'name':
					case 'url_key':
					case 'code':
						$column = $this->entity['sn']['alias'].'.'.$column;
						break;
				}
				$oStr .= ' '.$column.' '.strtoupper($direction).', ';
			}
			$oStr = rtrim($oStr, ', ');
			$oStr = ' ORDER BY '.$oStr.' ';
		}
		if (!is_null($filter)) {
			$fStr = $this->prepareWhere($filter);
			$wStr = ' WHERE '.$fStr;
		}

		$qStr .= $wStr.$gStr.$oStr;

		$q = $this->em->createQuery($qStr);
		$q = $this->addLimit($q, $limit);
		$result = $q->getResult();

		$totalRows = count($result);
		if ($totalRows < 1) {
			return new ModelResponse(null, 0, 0, null, true, 'E:D:002', 'No entries found in database that matches to your criterion.', $timeStamp, time());
		}
		return new ModelResponse($result, $totalRows, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
	}

	/**
	 * @name 			listSocialNetworksOfSite()
	 *
	 * @since			1.0.0
	 * @version         1.0.3
	 *
	 * @author          Can Berkol
	 *
	 * @use             $this->listSocialNetworks()
	 *
	 * @param           mixed           $site
	 * @param           array           $sortOrder
	 * @param           array           $limit
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function listSocialNetworksOfSite($site, $sortOrder = null, $limit = null){
		$timeStamp = time();
		$sModel = $this->kernel->getContainer()->get('sitemanagement.model');
		$response = $sModel->getSite($site);
		if($response->error->exist){
			return $this->createException('EntityDoesNotExist', 'Site with id / url_key '.$site.' does not exist in database.', 'E:D:002');
		}
		$site = $response->result->set;
		$filter[] = array(
			'glue' => 'and',
			'condition' => array(
				array(
					'glue' => 'and',
					'condition' => array('column' => $this->entity['sn']['alias'].'.site', 'comparison' => '=', 'value' => $site->getId()),
				)
			)
		);

		$response = $this->listSocialNetworks($filter, $sortOrder, $limit);

		$response->stats->execution->start = $timeStamp;
		$response->stats->execution->end = time();

		return $response;
	}
	/**
	 * @name 			listTwitterAccounts()
	 *
	 * @since			1.0.0
	 * @version         1.0.3
	 * @author          Can Berkol
	 *
	 * @use             $this->listSocialNetworks()
	 *
	 * @param           array           $sortOrder
	 * @param           array           $limit
	 * @param           array           $site
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function listTwitterAccounts($sortOrder = null, $limit = null, $site = null){
		$timeStamp = time();
		$filter[] = array(
			'glue' => 'and',
			'condition' => array(
				array(
					'glue' => 'and',
					'condition' => array('column' => $this->entity['sn']['alias'].'.url', 'comparison' => 'like', 'value' => 'twitter.com'),
				)
			)
		);
		if(!is_null($site)){
			$sModel = $this->kernel->getContainer()->get('sitemanagement.model');
			$response = $sModel->getSite($site);
			if($response->error->exist){
				return $this->createException('EntityDoesNotExist', 'Site with id / url_key '.$site.' does not exist in database.', 'E:D:002');
			}
			$site = $response->result->set;
			$filter[] = array(
				'glue' => 'and',
				'condition' => array(
					array(
						'glue' => 'and',
						'condition' => array('column' => $this->entity['sn']['alias'].'.site', 'comparison' => 'like', 'value' => $site->getId()),
					)
				)
			);
		}
		$response = $this->listSocialNetworks($filter, $sortOrder, $limit);
		$response->stats->execution->start = $timeStamp;
		$response->stats->execution->end = time();

		return $response;
	}
	/**
	 * @name 			listYoutubeAccounts()
	 *
	 * @since			1.0.0
	 * @version         1.0.3
	 * @author          Can Berkol
 	 *
	 * @use             $this->listSocialNetworks()
	 *
	 * @param           array           $sortOrder
	 * @param           array           $limit
	 * @param           array           $site
	 *
	 * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
	 */
	public function listYoutubeAccounts($sortOrder = null, $limit = null, $site = null){
		$timeStamp = time();
		$filter[] = array(
			'glue' => 'and',
			'condition' => array(
				array(
					'glue' => 'and',
					'condition' => array('column' => $this->entity['sn']['alias'].'.url', 'comparison' => 'like', 'value' => 'youtube.com'),
				)
			)
		);
		if(!is_null($site)){
			$sModel = $this->kernel->getContainer()->get('sitemanagement.model');
			$response = $sModel->getSite($site);
			if($response->error->exist){
				return $this->createException('EntityDoesNotExist', 'Site with id / url_key '.$site.' does not exist in database.', 'E:D:002');
			}
			$site = $response->result->set;
			$filter[] = array(
				'glue' => 'and',
				'condition' => array(
					array(
						'glue' => 'and',
						'condition' => array('column' => $this->entity['sn']['alias'].'.site', 'comparison' => 'like', 'value' => $site->getId()),
					)
				)
			);
		}
		$response = $this->listSocialNetworks($filter, $sortOrder, $limit);
		$response->stats->execution->start = $timeStamp;
		$response->stats->execution->end = time();

		return $response;
	}

    /**
     * @name 			updateSocialNetwork()
     *
     * @since			1.0.0
     * @version         1.0.3
     * @author          Can Berkol
     *
     * @use             $this->updateSocialNetworks()
     *
     * @param           mixed           $network
     *
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
    public function updateSocialNetwork($network){
        return $this->updateSocialNetworks(array($network));
    }
    /**
     * @name 			updateSocialNetworks()
     *
     * @since			1.0.0
     * @version         1.0.3
     * @author          Can Berkol

     * @use             $this->createException()
     *
     * @param           array           $collection
     *
     * @return          BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
     */
	public function updateSocialNetworks($collection){
		$timeStamp = time();
		/** Parameter must be an array */
		if (!is_array($collection)) {
			return $this->createException('InvalidParameterValueException', 'Invalid parameter value. Parameter must be an array collection', 'E:S:001');
		}
		$countUpdates = 0;
		$updatedItems = array();
		foreach($collection as $data){
			if($data instanceof BundleEntity\SocialNetwork){
				$entity = $data;
				$this->em->persist($entity);
				$updatedItems[] = $entity;
				$countUpdates++;
			}
			else if(is_object($data)){
				if(!property_exists($data, 'id') || !is_numeric($data->id)){
					return $this->createException('InvalidParameterException', 'Parameter must be an object with the "id" parameter and id parameter must have an integer value.', 'E:S:003');
				}
				$response = $this->getSocialNetwork($data->id);
				if($response->error->exist){
					return $this->createException('EntityDoesNotExist', 'File upload folder with id '.$data->id, 'E:D:002');
				}
				$oldEntity = $response->result->set;
				foreach($data as $column => $value){
					$set = 'set'.$this->translateColumnName($column);
					switch($column){
						case 'site':
							$sModel = $this->kernel->getContainer()->get('sitemanagement.model');
							$response = $sModel->getSite($value);
							if(!$response->error->exist){
								$oldEntity->$set($response->result->set);
							}
							else{
								new CoreExceptions\EntityDoesNotExistException($this->kernel, $value);
							}
							unset($response, $sModel);
							break;
						case 'id':
							break;
						default:
							$oldEntity->$set($value);
							break;
					}
					if($oldEntity->isModified()){
						$this->em->persist($oldEntity);
						$countUpdates++;
						$updatedItems[] = $oldEntity;
					}
				}
			}
		}
		if($countUpdates > 0){
			$this->em->flush();
			return new ModelResponse($updatedItems, $countUpdates, 0, null, false, 'S:D:004', 'Selected entries have been successfully updated within database.', $timeStamp, time());
		}
		return new ModelResponse(null, 0, 0, null, true, 'E:D:004', 'One or more entities cannot be updated within database.', $timeStamp, time());
	}
}

/**
 * Change Log
 * **************************************
 * v1.0.3                      03.05.2015
 * Can Berkol
 * **************************************
 * CR :: Made compatible with CoreBundle v3.3.
 *
 * **************************************
 * v1.0.2                      Can Berkol
 * **************************************
 * B getSocialNetwork() return $this->resetResponse(); => return $this->response;
 *
 * **************************************
 * v1.0.1                      Can Berkol
 * 23.11.2013
 * **************************************
 * A listAllSocialNetworks()
 *
 * **************************************
 * v1.0.0                      Can Berkol
 * 23.11.2013
 * **************************************
 * A deleteSocialNetwork()
 * A deleteSocialNetworks()
 * A deleteSocialNetworksOfSite()
 * A doesSocialNetworkExist()
 * A getSocialNetwork()
 * A insertSocialNetwork()
 * A insertSocialNetworks()
 * A listFacebookAccounts()
 * A listGiggemAccounts()
 * A listInstagramAccounts()
 * A listLinkedinAccounts()
 * A listTwitterAccounts()
 * A listSocialNetwork()
 * A listSocialNetworksOfSite()
 * A updateSocialNetwork()
 * A updateSocialNetworks()
 */

