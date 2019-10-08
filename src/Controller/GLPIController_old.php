<?php

namespace App\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Carbon\Carbon;
use Symfony\Component\HttpClient\HttpClient;

use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\NativeHttpClient;

class GLPIController_old extends AbstractController
{

    /**
     * @var Client
     */
    private $client;
    private $url = 'http://localhost:81/apirest.php/';
    private $app_token = 'eQ58wLjO0V54ypXSIXYX2SqNeWAPCmWEgmDYsXFX';
    private $app_user = '123';
    private $app_pwd = '456789';

    function __construct()
    {
        $this->client = new Client(array(
                'base_uri' => $this->url
            )
        );
    }

    // Login
    public function login()
    {
        try {
            $response = $this->client->request('GET', 'initSession',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Basic base64('.$this->app_user.':'.$this->app_pwd.')',
                        'App-Token' => $this->app_token
                    ],
                    'query' => [
                        'login' => $this->app_user,
                        'password' => $this->app_pwd
                    ]
                ]);

            $body = $response->getBody()->read(1024);
            $body = json_decode($body);
            $_SESSION['token'] = $body->session_token;
        } catch (RequestException $e) {
            return $e->getMessage();
        }
    }

    // Session Valide ?
    public function sessionValid($token)
    {
        try {
            $response = $this->client->request('GET', 'getFullSession',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Basic base64('.$this->app_user.':'.$this->app_pwd.')',
                        'Session-Token' => $token,
                        'App-Token' => $this->app_token
                    ]
                ]);

            if($response->getStatusCode()=="200")
            {
                return true;
            }
            else{
                return false;
            }

        } catch (RequestException $e) {
            return $e->getMessage();
        }
    }

    // Connect
    public function connect()
    {
        if (isset($_SESSION['token'])) {
            $sessionValid = $this->sessionValid($_SESSION['token']);
        } else {
            $sessionValid = false;
        }

        if (!$sessionValid) {
            $login = $this->login();
            if (!$login) {
                return array(false, 'Erreur : ' . $login[1]);
                exit;
            }
        }
        return true;
    }

    // Headers
    function getHeaders($type)
    {
        if (isset($_SESSION['token'])) {
            return [
                'Content-Type' => $type,
                'Session-Token' => $_SESSION['token'],
                'App-Token' => $this->app_token,
                'debug' => TRUE,
                ];
        } else {
            echo 'Connexion à GLPI impossible';
            exit;
        }
    }

    // Item
    /**
     * @param $itemtype
     * @param null $id
     * @param string $sort
     * @param string $order
     * @return int|mixed
     * @throws GuzzleException
     */
    public function getItem($itemtype, $id = null, $sort = 'id', $order = 'ASC', $expand_dropdowns = false)
    {
        try {
            $this->connect();
            $response = $this->client->request('GET', $itemtype.'/'.$id,
                [
                    'headers' => $this->getHeaders('application/json'),
                    'query' => [
                        'sort' => $sort,
                        'order' => $order,
                        'expand_dropdowns' => $expand_dropdowns,
                        'with_notes' => true,
                        'range' => '0-250'
                    ]
                ]
            );
            $body = $response->getBody();
            $body = json_decode($body);

            if($itemtype=='Ticket')
            {
                if(is_array($body)) {
                    foreach ($body as $ticket) {
                        $ticket->status = $this->getStatusByID($ticket->status);
                    }
                }
                else{
                    $body->status = $this->getStatusByID($body->status);
                }
            }
            return $body;
        } catch (RequestException $e) {
//            dump($e); exit;
            return $e->getResponse()->getStatusCode();
        }
    }

    /**
     * TICKET USERS
     * 1: Demandeur
     * 2: Attribué à
     * 3: Observateur
     * @param $id
     * @return int|mixed|\Psr\Http\Message\ResponseInterface
     * @throws GuzzleException
     */
    public function getTicketUsers($id)
    {
        try {
            $this->connect();
            $response = $this->client->request('GET', 'Ticket/' . $id . '/Ticket_User',
                [
                    'headers' => $this->getHeaders('application/json'),
                    'query' => [
                        'get_hateoas' => false
                    ]
                ]
            );
            $body = $response->getBody();
            $users = json_decode($body);
            $users_order = array();
            if(is_array($users))
            {
                foreach($users as $user)
                {
                    $typeUser = $this->getTypeUser($user->type);
                    if(!isset($users_order[$typeUser]))
                    {
                        $users_order[$typeUser] = array();
                    }
                    $userGLPI = $this->getItem('User', $user->users_id);
                    $firstName = $userGLPI->firstname;
                    $lastName = $userGLPI->realname;
                    $users_order[$typeUser][] = $firstName.' '.$lastName;
                }
            }
            return json_decode(json_encode($users_order), true);
        } catch (RequestException $e) {
            return $e->getResponse()->getStatusCode();
        }
    }

    /**
     * @param $id
     * @return int|mixed|\Psr\Http\Message\StreamInterface
     * @throws GuzzleException
     */
    public function getTicketItems($id)
    {
        try {
            $this->connect();
            $response = $this->client->request('GET', 'Ticket/' . $id . '/Item_Ticket',
                [
                    'headers' => $this->getHeaders('application/json'),
                    'query' => [
                        'expand_dropdowns' => true
                    ]
                ]
            );
            $body = $response->getBody();
            $ticketitems = json_decode($body);
            $items_order = array();
            if(is_array($ticketitems))
            {
                foreach ($ticketitems as $items) {
                    if (!isset($items_order[$items->itemtype])) {
                        $items_order[$items->itemtype] = array();
                    }
                    $items_order[$items->itemtype][] = $items->items_id;
                }
            }
            return $items_order;
        } catch (RequestException $e) {
            return $e->getResponse()->getStatusCode();
        }
    }

    /**
     * Notes d'un ticket
     * @param $id
     * @return int|mixed|\Psr\Http\Message\StreamInterface
     * @throws GuzzleException
     */
    public function getTicketFollowup($id)
    {
        try {
            $this->connect();
            $response = $this->client->request('GET', 'Ticket/' . $id . '/ITILFollowup',
                [
                    'headers' => $this->getHeaders('application/json'),
                    'query' => [
                        'expand_dropdowns' => false
                    ]
                ]
            );
            $body = $response->getBody();
            $body = json_decode($body);
            return $body;
        } catch (RequestException $e) {
            return $e->getResponse()->getStatusCode();
        }
    }

    /**
     * Solutions d'un ticket
     * @param $id
     * @return int|mixed|\Psr\Http\Message\StreamInterface
     * @throws GuzzleException
     */
    public function getTicketSolution($id)
    {
        try {
            $this->connect();
            $response = $this->client->request('GET', 'Ticket/' . $id . '/ITILSolution',
                [
                    'headers' => $this->getHeaders('application/json'),
                    'query' => [
                        'expand_dropdowns' => false
                    ]
                ]
            );
            $body = $response->getBody();
            $body = json_decode($body);
            return $body;
        } catch (RequestException $e) {
            return $e->getResponse()->getStatusCode();
        }
    }

    /**
     * Historique des notes/solutions d'un ticket
     * @param $id
     * @return array
     * @throws GuzzleException
     */
    public function getTicketHisto($id)
    {
        $notes = $this->getTicketFollowup($id);
        $solutions = $this->getTicketSolution($id);

        $histo = array_merge($notes, $solutions);
        usort($histo, array($this, 'tri_tab'));

        foreach($histo as $user)
        {
            $username = $this->getItem('User', $user->users_id);
            $user->users_id = $username->firstname.' '.$username->realname;
        }
        return $histo;
    }

    // Tri par date
    function tri_tab($a, $b)
    {
        $date1 = Carbon::createFromFormat('Y-m-d H:i:s', $a->date_creation);
        $date2 = Carbon::createFromFormat('Y-m-d H:i:s', $b->date_creation);
        return $date1->greaterThan($date2);
    }

    /* Type user libellé */
    function getTypeUser($id)
    {
        switch($id){
            case 1: return 'Demandeur'; break;
            case 2: return 'Attribué à'; break;
            case 3: return 'Observateur'; break;
        }
    }

    /* Statut libellé */
    function getStatusByID($id)
    {
        switch($id){
            case 1: return 'Nouveau'; break;
            case 2: return 'En cours (attribué)'; break;
            case 3: return 'En cours (planifié)'; break;
            case 4: return 'En attente'; break;
            case 5: return 'Résolu'; break;
            case 6: return 'Clos'; break;
        }
    }

    function getStatusByIDColor($id)
    {
        switch($id){
            case 1: return '<span class="text-warning">Nouveau</span>'; break;
            case 2: return '<span class="text-warning">En cours (attribué)</span>'; break;
            case 3: return '<span class="text-warning">En cours (planifié)</span>'; break;
            case 4: return '<span class="text-warning">En attente</span>'; break;
            case 5: return '<span class="text-success">Résolu</span>'; break;
            case 6: return '<span class="text-info">Clos</span>'; break;
        }
    }

    /* Création ticket */
    public function addTicket($type, $category, $name, $content)
    {
//        $data = [
//            'type' => $type,
//            'itilcategories_id' => $category,
//            '_users_id_requester' => 180, //$_SESSION['idGLPI'],
//            'name' => $name,
//            'content' => $content
//        ];
//        try {
//            $this->connect();
//            $response = $this->client->request('POST', 'Ticket',
//                [
//                    'headers' => $this->getHeaders('application/x-www-form-urlencoded'),
//                    'form_params' => [
//                            'input' => [
//                                'name' => 'titre',
//                                'content' => 'contenu'
//                            ]
//                        ]
//                ]
//            );
//
//            $body = $response->getBody();
//            $body = json_decode($body);
//
////            dump($body);exit;
//            return $body;
//        } catch (RequestException $e) {
//            return $e->getRequest();
////            dump($e);exit;
//            return $e->getResponse();
//        }
    }
}