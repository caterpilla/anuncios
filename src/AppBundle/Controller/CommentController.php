<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\DependencyInjection\ContainerAwareHttpKernel;

class CommentController extends Controller{
    private $conn, $session;
    public function __construct(){
        $this -> session = new Session();
    }
    /**
     * @Route("/a/{guid}/comment/save", name="home")
     */
    public function commentSaveAction(Request $request, $guid){
        $this -> conn = $this -> get('database_connection');
        $this -> conn -> fetchAll(
            sprintf(
                "insert into comment (text, user_id, announce_id) values ('%s', %d, (select id from announce where guid = '%s'))",
                $request -> get('comment'),
                $this -> session -> get('id'),
                $guid
            )
        );   
    }
    /**
    * @Route("/a/{guid}/comment/{id}/delete", name="home")
    */
    public function commentDeleteAction(Request $request, $guid, $id){
        $this -> conn = $this -> get('database_connection');
        $this -> conn -> fetchAll(
            sprintf(
                "update comment set status = 3 where id = %d",
                $id
            )
        );      
    }
}