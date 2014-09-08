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
 * @version     1.0.2
 * @date        27.11.2013
 *
 * =============================================================================================================
 * !! INSTRUCTIONS ON IMPORTANT ASPECTS OF MODEL METHODS !!!
 *
 * Each model function must return a $response ARRAY.
 * The array must contain the following keys and corresponding values.
 *
 * $response = array(
 *              'result'    =>   An array that contains the following keys:
 *                               'set'         Actual result set returned from ORM or null
 *                               'total_rows'  0 or number of total rows
 *                               'last_insert_id' The id of the item that is added last (if insert action)
 *              'error'     =>   true if there is an error; false if there is none.
 *              'code'      =>   null or a semantic and short English string that defines the error concanated
 *                               with dots, prefixed with err and the initials of the name of model class.
 *                               EXAMPLE: err.amm.action.not.found success messages have a prefix called scc..
 *
 *                               NOTE: DO NOT FORGET TO ADD AN ENTRY FOR ERROR CODE IN BUNDLE'S
 *                               RESOURCES/TRANSLATIONS FOLDER FOR EACH LANGUAGE.
 * =============================================================================================================
 * TODOs:
 * Do not forget to implement SITE, ORDER, AND PAGINATION RELATED FUNCTIONALITY
 */
namespace BiberLtd\Bundle\SocialNetworkBundle\Services;

/** Extends CoreModel */
use BiberLtd\Core\CoreModel;

/** Entities to be used */
use BiberLtd\Bundle\SocialNetworkBundle\Entity as BundleEntity;
use BiberLtd\Bundle\SiteManagementBundle\Entity as SMEntity;

/** Helper Models */
use BiberLtd\Bundle\SiteManagementBundle\Services as SMService;

/** Core Service*/
use BiberLtd\Core\Services as CoreServices;
use BiberLtd\Core\Exceptions as CoreExceptions;

class SocialNetworkModel extends CoreModel {
    /**
     * @name            __construct()
     *                  Constructor.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @param           object          $kernel
     * @param           string          $db_connection  Database connection key as set in app/config.yml
     * @param           string          $orm            ORM that is used.
     */
    public function __construct($kernel, $db_connection = 'default', $orm = 'doctrine'){
        parent::__construct($kernel, $db_connection, $orm);

        /**
         * Register entity names for easy reference.
         */
        $this->entity = array(
            'social_network'        => array('name' => 'SocialNetworkBundle:SocialNetwork', 'alias' => 'sn'),
        );
    }
    /**
     * @name            __destruct()
     *                  Destructor.
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
     *  				Deletes an existing social network from database.
     *
     * @since			1.0.0
     * @version         1.0.0
     * @author          Can Berkol
     *
     * @use             $this->deleteSocialNetworks()
     *
     * @param           mixed           $data             a single value of 'entity', 'id', 'url_key'
     * @param           string          $by               'entity', 'id', 'url_key'
     *
     * @return          mixed           $response
     */
    public function deleteSocialNetwork($data, $by = 'entity'){
        return $this->deleteSocialNetworks(array($data), $by);
    }
    /**
     * @name 			deleteSocialNetworks()
     *  				Deletes provided social networks from database.
     *
     * @since			1.0.0
     * @version         1.0.0
     * @author          Can Berkol
     *
     * @use             $this->delete_entities()
     * @use             $this->createException()
     *
     * @param           array           $collection     Collection consists one of the following: 'entity', 'id', 'url_key', 's,te'
     * @param           string          $by             Accepts the following options: 'entity', 'id', 'url_key', 'site'
     *
     * @return          array           $response
     */
    public function deleteSocialNetworks($collection, $by = 'entity'){
        $this->resetResponse();
        $by_opts = array('entity', 'id', 'url_key', 'site');
        if(!in_array($by, $by_opts)){
            return $this->createException('InvalidParameterValueException', implode(',', $by_opts), 'err.invalid.parameter.value');
        }
        /** Parameter must be an array */
        if(!is_array($collection)){
            return $this->createException('InvalidParameterValueException', 'Array', 'err.invalid.parameter.collection');
        }
        /** If COLLECTION is ENTITYs then USE ENTITY MANAGER */
        if($by == 'entity'){
            $sub_response = $this->delete_entities($collection, 'BundleEntity\SocialNetwork');
            /**
             * If there are items that cannot be deleted in the collection then $sub_response['process']
             * will be equal to continue and we need to continue process; otherwise we can return response.
             */
            if($sub_response['process'] == 'stop'){
                $this->response = array(
	    'rowCount' => $this->response['rowCount'],
                    'result'     => array(
                        'set'           => $sub_response['entries']['valid'],
                        'total_rows'    => $sub_response['item_count'],
                        'last_insert_id'=> null,
                    ),
                    'error'      => false,
                    'code'       => 'scc.db.deleted.done',
                );

                return $this->response;
            }
            else{
                $collection = $sub_response['entries']['invalid'];
            }
        }
        /**
         * If COLLECTION is NOT Entitys OR MORE COMPLEX DELETION NEEDED
         * CREATE CUSTOM SQL / DQL
         *
         * If you need custom DELETE, you need to assign $q_str to well formed DQL string; otherwise use
         * $tih-s>prepare_delete.
         */
        $table = $this->entity['social_network']['name'].' '.$this->entity['social_network']['alias'];
        $q_str = $this->prepare_delete($table, $this->entity['social_network']['alias'].'.'.$by, $collection);

        $query = $this->em->createQuery($q_str);
        /**
         * 6. Run query
         */
        $query->getResult();
        /**
         * Prepare & Return Response
         */
        $collection_count = count($collection);
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result'     => array(
                'set'           => $collection,
                'total_rows'    => $collection_count,
                'last_insert_id'=> null,
            ),
            'error'      => false,
            'code'       => 'scc.db.deleted.done',
        );
        return $this->response;
    }
    /**
     * @name 			deleteSocialNetworksOfSite()
     *  				Deletes an existing site's all social networks from database.
     *
     * @since			1.0.0
     * @version         1.0.0
     * @author          Can Berkol
     *
     * @use             $this->deleteSocialNetworks()
     *
     * @param           mixed           $site                   SMEntity\Site or identification number of site entry
     *
     * @return          mixed           $response
     */
    public function deleteSocialNetworksOfSite($site){
        if($site instanceof SMEntity\Site){
            $site = $site->getId();
        }
        $data = array('site' => $site);
        return $this->deleteSocialNetworks(array($data), 'site');
    }
    /**
     * @name 			doesSocialNetworkExist()
     *  				Checks if entry exists in database.
     *
     * @since			1.0.0
     * @version         1.0.0
     * @author          Can Berkol
     *
     * @use             $this->getSocialNetwork()
     *
     * @param           mixed           $network        id, url_key
     * @param           string          $by             id, url_key
     *
     * @param           bool            $bypass         If set to true does not return response but only the result.
     *
     * @return          mixed           $response
     */
    public function doesSocialNetworkEcist($network, $by = 'id', $bypass = false){
        $this->resetResponse();
        $exist = false;

        $response = $this->getSocialNetwork($network, $by);

        if(!$response['error'] && $response['result']['total_rows'] > 0){
            $exist = true;
        }
        if($bypass){
            return $exist;
        }
        /**
         * Prepare & Return Response
         */
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result'     => array(
                'set'           => $exist,
                'total_rows'    => 1,
                'last_insert_id'=> null,
            ),
            'error'      => false,
            'code'       => 'scc.db.entry.exist',
        );
        return $this->response;
    }
    /**
     * @name 			getSocialNetwork()
     *  				Returns details of a social network.
     *
     * @since			1.0.0
     * @version         1.0.2
     * @author          Can Berkol
     *
     * @use             $this->createException()
     * @use             $this->listSocialNetworks()
     *
     * @param           mixed           $network            entity, id, url_key
     * @param           string          $by                 entity, id, url_key
     *
     * @return          mixed           $response
     */
    public function getSocialNetwork($network, $by = 'id'){
        $this->resetResponse();
        $by_opts = array('id', 'url_key', 'entity');
        if(!in_array($by, $by_opts)){
            return $this->createException('InvalidParameterValueException', implode(',', $by_opts), 'err.invalid.parameter.by');
        }
        if(!is_object($network) && !is_numeric($network) && !is_string($network)){
            return $this->createException('InvalidParameterException', 'SocialNetwork', 'err.invalid.parameter.social_network');
        }
        if(is_object($network)){
            if(!$network instanceof BundleEntity\SocialNetwork){
                return $this->createException('InvalidParameterException', 'SocialNetwork', 'err.invalid.parameter.social_network');
            }
            /**
             * Prepare & Return Response
             */
            $this->response = array(
	    'rowCount' => $this->response['rowCount'],
                'result'     => array(
                    'set'           => $network,
                    'total_rows'    => 1,
                    'last_insert_id'=> null,
                ),
                'error'      => false,
                'code'       => 'scc.db.entry.exist',
            );
            return $this->response;
        }
        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => array('column' => $this->entity['social_network']['alias'].'.'.$by, 'comparison' => '=', 'value' => $network),
                )
            )
        );

        $response = $this->listSocialNetworks($filter, null, array('start' => 0, 'count' => 1));
        if($response['error']){
            return $response;
        }
        $collection = $response['result']['set'];
        /**
         * Prepare & Return Response
         */
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result'     => array(
                'set'           => $collection[0],
                'total_rows'    => 1,
                'last_insert_id'=> null,
            ),
            'error'      => false,
            'code'       => 'scc.db.entry.exist',
        );
        return $this->response;
    }
    /**
     * @name 			insertSocialNetwork()
     *  				Inserts one social network into database.
     *
     * @since			1.0.0
     * @version         1.0.0
     * @author          Can Berkol
     *
     * @use             $this->insertSocialNetworks()
     *
     * @param           mixed           $social_network        Entity or post
     * @param           mixed           $by                    entity, or, post
     *
     * @return          array           $response
     */
    public function insertSocialNetwork($social_network){
        $this->resetResponse();
        return $this->insertSociakNetworks(array($social_network));
    }
    /**
     * @name 			insertSocialNetworks()
     *  				Inserts one or more social networks into database.
     *
     * @since			1.0.0
     * @version         1.0.0
     * @author          Can Berkol
     *
     * @use             $this->doesSocialNetworkExist()
     *
     * @use             $this->createException()
     *
     * @param           array           $collection        Collection of entities or post data.
     * @param           string          $by                entity, post
     *
     * @return          array           $response
     */
    public function insertSocialNetworks($collection, $by = 'post'){
        $this->resetResponse();

        /** Parameter must be an array */
        if(!is_array($collection)){
            return $this->createException('InvalidParameterException', 'Array', 'err.invalid.parameter.collection');
        }
        if($by == 'entity'){
            $sub_response = $this->insert_entities($collection, 'BundleEntity\SocialNetwork');
            /**
             * If there are items that cannot be deleted in the collection then $sub_Response['process']
             * will be equal to continue and we need to continue process; otherwise we can return response.
             */
            if($sub_response['process'] == 'stop'){
                $this->response = array(
	    'rowCount' => $this->response['rowCount'],
                    'result'     => array(
                        'set'           => $sub_response['entries']['valid'],
                        'total_rows'    => $sub_response['item_count'],
                        'last_insert_id'=> null,
                    ),
                    'error'      => false,
                    'code'       => 'scc.db.insert.done.',
                );

                return $this->response;
            }
            else{
                $collection = $sub_response['entries']['invalid'];
            }
        }
        /**
         * If by post
         */
        $to_insert = 0;
        foreach($collection as $item){
            $site = '';
            if(isset($item['site'])){
                $site = $item['site'];
                unset($item['site']);
            }
            $entity = new BundleEntity\SocialNetwork();
            foreach($item as $column => $value){
                $method = 'set_'.$column;
                if(method_exists($entity, $method)){
                    $entity->$method($value);
                }
            }
            /** HANDLE FOREIGN DATA :: SITE */
            if(!is_numeric($site)){
                $SMModel = new SMService\SiteManagementModel($this->kernel, $this->db_connection, $this->orm);
                $response = $SMModel->getSite($value, 'id');
                if($response['error']){
                    new CoreExceptions\InvalidSiteException($this->kernel, $value);
                    break;
                }
                $site = $response['result']['set'];
                $entity->$method($site);
                /** Free up some memory */
                unset($site, $response, $SMModel);
            }
            $this->insert_entities(array($entity), 'BundleEntity\SocialNetwork');

            $this->em->persist($entity);
            $to_insert++;
            /** Free some memory */
            unset($entity_localizations);
        }
        $this->em->flush();
        $code = 'scc.db.insert.done';
        /**
         * Prepare & Return Response
         */
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result'     => array(
                'set'           => $collection,
                'total_rows'    => $to_insert,
                'last_insert_id'=> $entity->getId(),
            ),
            'error'      => false,
            'code'       => $code,
        );
        return $this->response;
    }
    /**
     * @name 			listAllSocialNetworks()
     *  				Lists all social networks registered for a given site.
     *
     * @since			1.0.1
     * @version         1.0.1
     * @author          Can Berkol
     *
     * @use             $this->listSocialNetworks()
     *
     * @param           Array           $sortorder
     * @param           Array           $limit
     *
     * @return          mixed           $response
     */
    public function listAllSocialNetworks($sortorder = null, $limit = null){
        $this->resetResponse();
        return $this->listSocialNetworks(null, $sortorder, $limit);
    }
    /**
     * @name 			listFacebookAccounts()
     *  				Returns Facebook social network entries.
     *                  Runs a LIKE search in url field.
     *
     * @since			1.0.0
     * @version         1.0.0
     * @author          Can Berkol
     *
     * @use             $this->listSocialNetworks()
     *
     * @param           string          $by                 entity, id, url_key
     *
     * @return          mixed           $response
     */
    public function listFacebookAccounts($sortorder = null, $limit = null){
        $this->resetResponse();

        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => array('column' => $this->entity['social_network']['alias'].'.url', 'comparison' => 'like', 'value' => 'facebook.com'),
                )
            )
        );

        $response = $this->listSocialNetworks($filter, $sortorder, $limit);
        if($response['error']){
            return $response;
        }
        $collection = $response['result']['set'];
        /**
         * Prepare & Return Response
         */
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result'     => array(
                'set'           => $collection[0],
                'total_rows'    => 1,
                'last_insert_id'=> null,
            ),
            'error'      => false,
            'code'       => 'scc.db.entry.exist',
        );
        return $this->response;
    }
    /**
     * @name 			listGiggemAccounts()
     *  				Returns Giggem social network entries.
     *                  Runs a LIKE search in url field.
     *
     * @since			1.0.0
     * @version         1.0.0
     * @author          Can Berkol
     *
     * @use             $this->listSocialNetworks()
     *
     * @param           string          $by                 entity, id, url_key
     *
     * @return          mixed           $response
     */
    public function listGiggemAccounts($sortorder = null, $limit = null){
        $this->resetResponse();

        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => array('column' => $this->entity['social_network']['alias'].'.url', 'comparison' => 'like', 'value' => 'giggem.com'),
                )
            )
        );

        $response = $this->listSocialNetworks($filter, $sortorder, $limit);
        if($response['error']){
            return $response;
        }
        $collection = $response['result']['set'];
        /**
         * Prepare & Return Response
         */
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result'     => array(
                'set'           => $collection[0],
                'total_rows'    => 1,
                'last_insert_id'=> null,
            ),
            'error'      => false,
            'code'       => 'scc.db.entry.exist',
        );
        return $this->response;
    }
    /**
     * @name 			listInstagramAccounts()
     *  				Returns Instagram social network entries.
     *                  Runs a LIKE search in url field.
     *
     * @since			1.0.0
     * @version         1.0.0
     * @author          Can Berkol
     *
     * @use             $this->listSocialNetworks()
     *
     * @param           string          $by                 entity, id, url_key
     *
     * @return          mixed           $response
     */
    public function listInstagramAccounts($sortorder = null, $limit = null){
        $this->resetResponse();

        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => array('column' => $this->entity['social_network']['alias'].'.url', 'comparison' => 'like', 'value' => 'instagram.com'),
                )
            )
        );

        $response = $this->listSocialNetworks($filter, $sortorder, $limit);
        if($response['error']){
            return $response;
        }
        $collection = $response['result']['set'];
        /**
         * Prepare & Return Response
         */
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result'     => array(
                'set'           => $collection[0],
                'total_rows'    => 1,
                'last_insert_id'=> null,
            ),
            'error'      => false,
            'code'       => 'scc.db.entry.exist',
        );
        return $this->response;
    }
    /**
     * @name 			listLinkedinAccounts()
     *  				Returns linkedin social network entries.
     *                  Runs a LIKE search in url field.
     *
     * @since			1.0.0
     * @version         1.0.0
     * @author          Can Berkol
     *
     * @use             $this->listSocialNetworks()
     *
     * @param           string          $by                 entity, id, url_key
     *
     * @return          mixed           $response
     */
    public function listLinkedinAccounts($sortorder = null, $limit = null){
        $this->resetResponse();

        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => array('column' => $this->entity['social_network']['alias'].'.url', 'comparison' => 'like', 'value' => 'linkedin.com'),
                )
            )
        );

        $response = $this->listSocialNetworks($filter, $sortorder, $limit);
        if($response['error']){
            return $response;
        }
        $collection = $response['result']['set'];
        /**
         * Prepare & Return Response
         */
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result'     => array(
                'set'           => $collection[0],
                'total_rows'    => 1,
                'last_insert_id'=> null,
            ),
            'error'      => false,
            'code'       => 'scc.db.entry.exist',
        );
        return $this->response;
    }
    /**
     * @name 			listTwitterAccounts()
     *  				Returns Twitter social network entries.
     *                  Runs a LIKE search in url field.
     *
     * @since			1.0.0
     * @version         1.0.0
     * @author          Can Berkol
     *
     * @use             $this->listSocialNetworks()
     *
     * @param           string          $by                 entity, id, url_key
     *
     * @return          mixed           $response
     */
    public function listTwitterAccounts($sortorder = null, $limit = null){
        $this->resetResponse();

        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => array('column' => $this->entity['social_network']['alias'].'.url', 'comparison' => 'like', 'value' => 'twitter.com'),
                )
            )
        );

        $response = $this->listSocialNetworks($filter, $sortorder, $limit);
        if($response['error']){
            return $response;
        }
        $collection = $response['result']['set'];
        /**
         * Prepare & Return Response
         */
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result'     => array(
                'set'           => $collection[0],
                'total_rows'    => 1,
                'last_insert_id'=> null,
            ),
            'error'      => false,
            'code'       => 'scc.db.entry.exist',
        );
        return $this->response;
    }
    /**
     * @name 			listSocialNetworks()
     *  				List social networks from database based on a variety of conditions.
     *
     * @since			1.0.0
     * @version         1.0.0
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           array           $filter             Multi-dimensional array
     *
     *                                  Example:
     *                                  $filter[] = array(
     *                                              'glue' => 'and',
     *                                              'condition' => array(
     *                                                               array(
     *                                                                      'glue' => 'and',
     *                                                                      'condition' => array('column' => 'p.id', 'comparison' => 'in', 'value' => array(3,4,5,6)),
     *                                                                  )
     *                                                  )
     *                                              );
     *                                 $filter[] = array(
     *                                              'glue' => 'and',
     *                                              'condition' => array(
     *                                                              array(
     *                                                                      'glue' => 'or',
     *                                                                      'condition' => array('column' => 'p.status', 'comparison' => 'eq', 'value' => 'a'),
     *                                                              ),
     *                                                              array(
     *                                                                      'glue' => 'and',
     *                                                                      'condition' => array('column' => 'p.price', 'comparison' => '<', 'value' => 500),
     *                                                              ),
     *                                                             )
     *                                           );
     *
     *
     * @param           array           $sortorder              Array
     *                                      'column'            => 'asc|desc'
     * @param           array           $limit
     *                                      start
     *                                      count
     *
     * @param           string           $query_str             If a custom query string needs to be defined.
     *
     * @return          array           $response
     */
    private function listSocialNetworks($filter = null, $sortorder = null, $limit = null, $query_str = null){
        $this->resetResponse();
        if(!is_array($sortorder) && !is_null($sortorder)){
            return $this->createException('InvalidSortOrderException', '', 'err.invalid.parameter.sortorder');
        }
        /**
         * Add filter checks to below to set join_needed to true.
         */

        /** *************************************************** */
        $order_str = '';
        $where_str = '';
        $group_str = '';
        $filter_str = '';

        /**
         * Start creating the query.
         *
         * Note that if no custom select query is provided we will use the below query as a start.
         */
        $query_str = 'SELECT '.$this->entity['social_network']['alias']
            .' FROM '.$this->entity['social_network']['name'].' '.$this->entity['social_network']['alias'];
        /**
         * Prepare ORDER BY section of query.
         */
        if($sortorder != null){
            foreach($sortorder as $column => $direction){
                switch($column){
                    case 'id':
                    case 'name':
                    case 'url_key':
                    case 'url':
                        $column = $this->entity['social_network']['alias'].'.'.$column;
                        break;
                }
                $order_str .= ' '.$column.' '.strtoupper($direction).', ';
            }
            $order_str = rtrim($order_str, ', ');
            $order_str = ' ORDER BY '.$order_str.' ';
        }

        /**
         * Prepare WHERE section of query.
         */
        if($filter != null){
            $filter_str = $this->prepare_where($filter);
            $where_str .= ' WHERE '.$filter_str;
        }

        $query_str .= $where_str.$group_str.$order_str;

        $query = $this->em->createQuery($query_str);

        /**
         * Prepare LIMIT section of query
         */
        if($limit != null){
            if(isset($limit['start']) && isset($limit['count'])){
                /** If limit is set */
                $query->setFirstResult($limit['start']);
                $query->setMaxResults($limit['count']);
            }
            else{
                new CoreExceptions\InvalidLimitException($this->kernel, '');
            }
        }
        /**
         * Prepare & Return Response
         */
        $result = $query->getResult();

        $total_rows = count($result);

        if($total_rows < 1){
            $this->response['code'] = 'err.db.entry.notexist';
            return $this->response;
        }
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result'     => array(
                'set'           => $result,
                'total_rows'    => $total_rows,
                'last_insert_id'=> null,
            ),
            'error'      => false,
            'code'       => 'scc.db.entry.exist',
        );
        return $this->response;
    }
    /**
     * @name 			listSocialNetworksOfSite()
     *  				Lists all social networks registered for a given site.
     *
     * @since			1.0.0
     * @version         1.0.0
     * @author          Can Berkol
     *
     * @use             $this->listSocialNetworks()
     *
     * @param           mixed           $site              entity, id, url_key
     * @param           string          $by                 entity, id, url_key
     *
     * @return          mixed           $response
     */
    public function listSocialNetworksOfSite($site, $sortorder = null, $limit = null){
        $this->resetResponse();
        if($site instanceof SMEntity\Site){
            $site = $site->getId();
        }
        $filter[] = array(
            'glue' => 'and',
            'condition' => array(
                array(
                    'glue' => 'and',
                    'condition' => array('column' => $this->entity['social_network']['alias'].'.site', 'comparison' => '=', 'value' => $site),
                )
            )
        );

        $response = $this->listSocialNetworks($filter, $sortorder, $limit);
        if($response['error']){
            return $response;
        }
        $collection = $response['result']['set'];
        /**
         * Prepare & Return Response
         */
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result'     => array(
                'set'           => $collection[0],
                'total_rows'    => 1,
                'last_insert_id'=> null,
            ),
            'error'      => false,
            'code'       => 'scc.db.entry.exist',
        );
        return $this->response;
    }
    /**
     * @name 			updateSocialNetwork()
     *  				Updates single social network. The data must be either a post data (array) or an entity
     *
     * @since			1.0.0
     * @version         1.0.0
     * @author          Can Berkol
     *
     * @use             $this->updateSocialNetworks()
     *
     * @param           mixed           $data           entity or post data
     * @param           string          $by             entity, post
     *
     * @return          mixed           $response
     */
    public function updateSocialNetwork($data, $by = 'post'){
        return $this->updateSocialNetwork(Sarray($data), $by);
    }
    /**
     * @name 			updateSocialNetworks()
     *  				Updates one or more social networks in database.
     *
     * @since			1.0.0
     * @version         1.0.0
     * @author          Can Berkol
     *
     * @use             $this->doesSocialNetworkExist()
     * @use             $this->createException()
     *
     * @param           array           $collection      Collection of Product entities or array of entity details.
     * @param           array           $by              entity, post
     *
     * @return          array           $response
     */
    public function updateSocialNetworks($collection, $by = 'post'){
        $this->resetResponse();
        /** Parameter must be an array */
        if(!is_array($collection)){
            return $this->createException('InvalidParameterException', 'Array', 'err.invalid.parameter.collection');
        }
        $by_opts = array('entity', 'post');
        if(!in_array($by, $by_opts)){
            return $this->createException('InvalidParameterException', implode(',', $by_opts), 'err.invalid.parameter.by');
        }
        if($by == 'entity'){
            $sub_response = $this->update_entities($collection, 'BundleEntity\SocialNetwork');
            /**
             * If there are items that cannot be deleted in the collection then $sub_Response['process']
             * will be equal to continue and we need to continue process; otherwise we can return response.
             */
            if($sub_response['process'] == 'stop'){
                $this->response = array(
	    'rowCount' => $this->response['rowCount'],
                    'result'     => array(
                        'set'           => $sub_response['entries']['valid'],
                        'total_rows'    => $sub_response['item_count'],
                        'last_insert_id'=> null,
                    ),
                    'error'      => false,
                    'code'       => 'scc.db.delete.done',
                );
                return $this->response;
            }
            else{
                $collection = $sub_response['entries']['invalid'];
            }
        }
        /**
         * If by post
         */
        $to_update = array();
        $count = 0;
        $collection_by_id = array();
        foreach($collection as $item){
            if(!isset($item['id'])){
                unset($collection[$count]);
            }
            $to_update[] = $item['id'];
            $collection_by_id[$item['id']] = $item;
            $count++;
        }
        unset($collection);
        $filter = array(
            array(
                'glue' => 'and',
                'condition' => array(
                    array(
                        'glue' => 'and',
                        'condition' => array('column' => $this->entity['social_network']['alias'].'.id', 'comparison' => 'in', 'value' => $to_update),
                    )
                )
            )
        );
        $response = $this->listSocialNetworks($filter);
        if($response['error']){
            return $this->createException('InvalidParameterException', 'Array', 'err.invalid.parameter.collection');
        }
        $entities = $response['result']['set'];
        foreach($entities as $entity){
            $data = $collection_by_id[$entity->getId()];
            /** Prepare foreign key data for process */
            $site ='';
            if(isset($data['site'])){
                $site = $data['site'];
            }
            unset($data['site']);

            foreach($data as $column => $value){
                $method_set = 'set_'.$column;
                $method_get = 'get_'.$column;
                /**
                 * Set the value only if there is a corresponding value in collection and if that value is different
                 * from the one set in database
                 */
                if(isset($collection_by_id[$entity->getId()][$column]) && $collection_by_id[$entity->getId()][$column] != $entity->$method_get()){
                    $entity->$method_set($value);
                }

                /** HANDLE FOREIGN DATA :: SITE */
                if(is_numeric($site)){
                    $SMModel = new SMService\SiteManagementModel($this->kernel, $this->db_connection, $this->orm);
                    $response = $SMModel->getSite($site, 'id');
                    if($response['error']){
                        new CoreExceptions\InvalidSiteException($this->kernel, $value);
                        break;
                    }
                    $site_entity = $response['result']['set'];
                    $entity->$method_set($site_entity);
                    /** Free up some memory */
                    unset($site, $response, $SMModel);
                }
                $this->em->persist($entity);
            }
        }
        $this->em->flush();

        $total_rows = count($to_update);
        /**
         * Prepare & Return Response
         */
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result'     => array(
                'set'           => $to_update,
                'total_rows'    => $total_rows,
                'last_insert_id'=> null,
            ),
            'error'      => false,
            'code'       => 'scc.db.update.done',
        );
        return $this->response;
    }
}

/**
 * Change Log
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

